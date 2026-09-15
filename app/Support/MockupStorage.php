<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * MockupStorage
 *
 * Central helper that keeps large base64 mockup images OUT of the database.
 *
 * Historically the app persisted mockups as `data:image/...;base64,....` blobs
 * inside `prototype_sales.services[].sublimationForm.mockup` and
 * `prototype_sales.mockup_images[].url`. A single row could carry ~4MB of
 * base64, and a month of Class sales carried ~74MB — enough to blow the 256MB
 * PHP memory_limit when hydrating calendars/kanban (json_encode peak ~286MB).
 *
 * This helper:
 *   1. Detects base64 image data URLs (externalizeArray / externalizeJson).
 *   2. Writes them once to `storage/app/public/uploads/mockups/{saleId}/...`
 *      and replaces the value with the public `/storage/...` URL.
 *   3. Is fully idempotent — already-stored URLs pass through untouched.
 *   4. Can inline a stored URL back to a data URI for DomPDF, because this
 *      install has `isRemoteEnabled = false` (relative URLs are not fetched).
 *
 * It is safe to call on arbitrary JSON: any nested string that is a base64
 * image data URL is externalized regardless of its key name.
 */
class MockupStorage
{
    /** Laravel filesystem disk that backs public/storage. */
    public const DISK = 'public';

    /** Folder (inside the disk) that holds externalized mockups. */
    public const PREFIX = 'uploads/mockups';

    /**
     * When true, storeDataUrl still decodes and computes the target path but
     * does NOT write any file. Used by the migrator's --dry-run mode.
     */
    public static bool $dryRun = false;

    /** True when the value is an inline base64 image data URL. */
    public static function isDataUrl($value): bool
    {
        return is_string($value)
            && str_starts_with($value, 'data:image/')
            && str_contains($value, ';base64,');
    }

    /**
     * Decode a base64 image data URL.
     *
     * @return array{0:string,1:string}|null  [extension, binary] or null if invalid.
     */
    public static function decodeDataUrl(string $dataUrl): ?array
    {
        if (!preg_match('#^data:image/([a-zA-Z0-9.+-]+);base64,(.*)$#s', $dataUrl, $m)) {
            return null;
        }

        $ext = strtolower($m[1]);
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;
        $ext = preg_replace('/[^a-z0-9]/', '', $ext);
        if ($ext === '') {
            $ext = 'png';
        }

        $bin = base64_decode($m[2], true);
        if ($bin === false || $bin === '') {
            return null;
        }

        return [$ext, $bin];
    }

    /**
     * Persist a base64 image data URL to the public disk.
     *
     * @return string|null  Public URL path (e.g. "/storage/uploads/mockups/90/mockup_ab12.png")
     *                      or null when the data URL is not decodable.
     */
    public static function storeDataUrl(string $dataUrl, ?int $saleId, string $prefix = 'mockup'): ?string
    {
        $decoded = self::decodeDataUrl($dataUrl);
        if ($decoded === null) {
            return null;
        }

        [$ext, $bin] = $decoded;

        // Content-addressed name → migrating the same blob twice reuses the file.
        $hash = substr(sha1($bin), 0, 16);
        $dir = self::PREFIX . '/' . ($saleId ?: 'unassigned');
        $rel = $dir . '/' . $prefix . '_' . $hash . '.' . $ext;

        $disk = Storage::disk(self::DISK);
        if (!self::$dryRun && !$disk->exists($rel)) {
            $disk->put($rel, $bin);
            @chmod($disk->path($rel), 0644);
        }

        return '/storage/' . $rel;
    }

    /**
     * Recursively externalize every base64 image data URL inside a value.
     *
     * @param mixed  $data
     * @param int|null $saleId
     * @param string $pathPrefix  Dotted path used only for the mapping log.
     * @param array  $map         Collected mapping entries (by reference).
     *
     * @return array{0:mixed}  Single-element array holding the externalized value.
     */
    public static function externalizeArray($data, ?int $saleId, string $pathPrefix = '', array &$map = []): array
    {
        if (is_array($data)) {
            $out = [];
            foreach ($data as $k => $v) {
                [$out[$k]] = self::externalizeArray($v, $saleId, $pathPrefix . '.' . $k, $map);
            }

            return [$out];
        }

        if (self::isDataUrl($data)) {
            $url = self::storeDataUrl($data, $saleId);
            $entry = [
                'path'  => ltrim($pathPrefix, '.'),
                'bytes' => strlen($data),
                'url'   => $url,
            ];
            if ($url === null) {
                // Undecodable blob: drop it so we never re-persist a broken 4MB string.
                $entry['dropped'] = true;
                $map[] = $entry;

                return [''];
            }
            $map[] = $entry;

            return [$url];
        }

        return [$data];
    }

    /**
     * Externalize a JSON-encoded column value.
     *
     * @return array{0:?string,1:bool,2:array}  [newJson, changed, mapping]
     */
    public static function externalizeJson(?string $json, ?int $saleId, string $prefix = ''): array
    {
        if (!is_string($json) || $json === '' || !str_contains($json, 'data:image')) {
            return [$json, false, []];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [$json, false, []];
        }

        $map = [];
        [$new] = self::externalizeArray($decoded, $saleId, $prefix, $map);

        if (empty($map)) {
            return [$json, false, []];
        }

        $newJson = json_encode($new, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return [$newJson, $newJson !== $json, $map];
    }

    /**
     * Convert a stored mockup URL/path into an inline data URI for DomPDF.
     *
     * DomPDF here runs with isRemoteEnabled = false, so a `/storage/...` URL
     * would render as a broken image. Data URIs still render offline.
     * Returns the input unchanged when it is already inline or unresolvable.
     */
    public static function toPdfSrc(?string $value): ?string
    {
        if (!is_string($value) || $value === '' || self::isDataUrl($value)) {
            return $value;
        }

        $local = self::resolveLocalPath($value);
        if ($local === null || !is_file($local) || !is_readable($local)) {
            return $value;
        }

        $bin = @file_get_contents($local);
        if ($bin === false || $bin === '') {
            return $value;
        }

        $mime = 'image/png';
        if (function_exists('finfo_open')) {
            $fi = @finfo_open(FILEINFO_MIME_TYPE);
            if ($fi) {
                $detected = @finfo_file($fi, $local);
                @finfo_close($fi);
                if (is_string($detected) && str_starts_with($detected, 'image/')) {
                    $mime = $detected;
                }
            }
        }
        if ($mime === 'image/png') {
            $ext = strtolower(pathinfo($local, PATHINFO_EXTENSION));
            $map = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp'];
            if (isset($map[$ext])) {
                $mime = $map[$ext];
            }
        }

        return 'data:' . $mime . ';base64,' . base64_encode($bin);
    }

    /** Resolve a public mockup URL/path to a local filesystem path, if possible. */
    private static function resolveLocalPath(string $value): ?string
    {
        // /storage/... → public disk
        foreach (['/storage/', 'storage/'] as $prefix) {
            if (str_starts_with($value, $prefix)) {
                $rel = substr($value, strlen($prefix));

                return Storage::disk(self::DISK)->path($rel);
            }
        }

        // Absolute same-host URL → use the path portion.
        $path = parse_url($value, PHP_URL_PATH);
        if (is_string($path) && $path !== '') {
            $host = parse_url($value, PHP_URL_HOST);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if (($host === null || $host === $appHost) && str_starts_with($path, '/storage/')) {
                return Storage::disk(self::DISK)->path(substr($path, strlen('/storage/')));
            }
            if ($host !== null && $host !== $appHost) {
                return null; // external host — leave as-is
            }
            $value = $path;
        }

        // Legacy public/* path (e.g. /uploads/mockups/...) or absolute filesystem path.
        if (str_starts_with($value, '/')) {
            $candidate = public_path(ltrim($value, '/'));
            if (is_file($candidate)) {
                return $candidate;
            }
            if (is_file($value)) {
                return $value;
            }
        }

        return null;
    }
}
