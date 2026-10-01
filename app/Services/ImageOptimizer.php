<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ImageOptimizer
 *
 * Converts uploaded raster images to a "web-safe" size before they hit disk:
 *   - scales the longest side down to config('media.max_dimension') (default 1920px)
 *   - re-encodes to WebP (fallback JPEG) at config('media.quality') (default 82)
 *   - strips heavy metadata, keeps transparency (WebP / alpha-preserving)
 *
 * Design goals:
 *   - SAFE: if anything fails (no GD, unreadable file, non-raster input, disabled
 *     via config), it silently falls back to the original Laravel store behavior.
 *     A broken optimizer must never break an upload.
 *   - Drop-in: store()/storeAs() mirror Laravel's UploadedFile signatures so call
 *     sites only swap $file->store(...) -> ImageOptimizer::store($file, ...).
 */
class ImageOptimizer
{
    /** Extensions we can decode with GD and re-encode. */
    public const RASTER = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];

    public static function enabled(): bool
    {
        return (bool) config('media.optimize', true) && function_exists('imagecreatefromstring');
    }

    public static function maxDimension(): int
    {
        $max = (int) config('media.max_dimension', 1920);

        return $max > 0 ? $max : 1920;
    }

    public static function quality(): int
    {
        $q = (int) config('media.quality', 82);
        if ($q < 1) {
            $q = 1;
        }
        if ($q > 100) {
            $q = 100;
        }

        return $q;
    }

    public static function preferredFormat(): string
    {
        $f = strtolower((string) config('media.format', 'webp'));

        return in_array($f, ['webp', 'jpg', 'jpeg'], true) ? $f : 'webp';
    }

    /* -------------------------------------------------------------------------
     * Public API — drop-in replacements for UploadedFile::store()/storeAs()
     * ---------------------------------------------------------------------- */

    /**
     * Store an uploaded file. Raster images are optimized; everything else is
     * stored unchanged. Returns the stored relative path (as Laravel does).
     */
    public static function store(UploadedFile $file, string $folder, string $disk = 'public'): string
    {
        $optimized = self::tryOptimizeUpload($file);
        if ($optimized === null) {
            return $file->store($folder, $disk);
        }

        [$ext, $binary] = $optimized;
        $path = trim($folder, '/') . '/' . Str::random(40) . '.' . $ext;
        Storage::disk($disk)->put($path, $binary);

        return $path;
    }

    /**
     * Like storeAs(), but the stored file's extension may change to the
     * optimized format (e.g. .png -> .webp). Returns the stored relative path.
     */
    public static function storeAs(UploadedFile $file, string $folder, string $filename, string $disk = 'public'): string
    {
        $optimized = self::tryOptimizeUpload($file);
        if ($optimized === null) {
            return $file->storeAs($folder, $filename, $disk);
        }

        [$ext, $binary] = $optimized;
        $base = pathinfo($filename, PATHINFO_FILENAME);
        if ($base === '') {
            $base = Str::random(40);
        }
        $path = trim($folder, '/') . '/' . $base . '.' . $ext;
        Storage::disk($disk)->put($path, $binary);

        return $path;
    }

    /**
     * Optimize an in-memory binary image (e.g. decoded base64 mockups).
     *
     * @return array{0:string,1:string}|null  [extension, binary] or null on failure.
     */
    public static function optimizeBinary(string $raw): ?array
    {
        if (!self::enabled() || $raw === '') {
            return null;
        }

        try {
            $img = @imagecreatefromstring($raw);
            if ($img === false) {
                return null;
            }

            try {
                $img = self::resize($img);
                return self::encode($img);
            } finally {
                if ($img instanceof \GdImage || is_resource($img)) {
                    @imagedestroy($img);
                }
            }
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Convert a base64 image data URL to an optimized data URL.
     * Non-data-URLs and failures return the input unchanged.
     */
    public static function optimizeDataUrl(string $dataUrl, ?int $maxDim = null): string
    {
        if (!self::enabled() || !str_starts_with($dataUrl, 'data:image/')) {
            return $dataUrl;
        }
        if (!preg_match('#^data:image/([a-zA-Z0-9.+-]+);base64,(.*)$#s', $dataUrl, $m)) {
            return $dataUrl;
        }

        $binary = base64_decode($m[2], true);
        if ($binary === false || $binary === '') {
            return $dataUrl;
        }

        // Allow callers to force a stricter max dimension (e.g. PDF embedding).
        $prev = null;
        if ($maxDim !== null && $maxDim > 0 && $maxDim !== self::maxDimension()) {
            $prev = self::maxDimension();
            config(['media.max_dimension' => $maxDim]);
        }

        try {
            $optimized = self::optimizeBinary($binary);
        } finally {
            if ($prev !== null) {
                config(['media.max_dimension' => $prev]);
            }
        }

        if ($optimized === null) {
            return $dataUrl;
        }

        [$ext, $out] = $optimized;
        $mime = $ext === 'jpg' ? 'jpeg' : $ext;

        return 'data:image/' . $mime . ';base64,' . base64_encode($out);
    }

    /* -------------------------------------------------------------------------
     * Internals
     * ---------------------------------------------------------------------- */

    /**
     * @return array{0:string,1:string}|null  [extension, binary] or null if the
     *                                        file should be stored unchanged.
     */
    protected static function tryOptimizeUpload(UploadedFile $file): ?array
    {
        if (!self::enabled()) {
            return null;
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, self::RASTER, true)) {
            return null;
        }

        $path = $file->getRealPath();
        if ($path === false || !is_readable($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }

        $result = self::optimizeBinary($raw);
        if ($result === null) {
            return null;
        }

        // Best-effort EXIF auto-orient for JPEG uploads only.
        if (in_array($ext, ['jpg', 'jpeg'], true) && function_exists('exif_read_data')) {
            $oriented = self::autoOrient($path, $result[1]);
            if ($oriented !== null) {
                $result = $oriented;
            }
        }

        return $result;
    }

    /** Scale down (never up) the longest side to max_dimension. */
    protected static function resize($img)
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $max = self::maxDimension();

        if ($w <= $max && $h <= $max) {
            return $img;
        }

        $ratio = min($max / $w, $max / $h);
        $nw = max(1, (int) round($w * $ratio));
        $nh = max(1, (int) round($h * $ratio));

        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        @imagedestroy($img);

        return $dst;
    }

    /**
     * Encode a GD image to the configured web-safe format.
     *
     * @return array{0:string,1:string}|null
     */
    protected static function encode($img): ?array
    {
        $quality = self::quality();
        $format  = self::preferredFormat();

        // WebP first (smallest, supports alpha).
        if ($format === 'webp' && function_exists('imagewebp')) {
            $bin = self::capture(fn () => imagewebp($img, null, $quality));
            if ($bin !== null) {
                return ['webp', $bin];
            }
        }

        // JPEG: flatten transparency onto white.
        $w = imagesx($img);
        $h = imagesy($img);
        $flat = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($flat, 255, 255, 255);
        imagefilledrectangle($flat, 0, 0, $w, $h, $white);
        imagealphablending($flat, true);
        imagecopy($flat, $img, 0, 0, 0, 0, $w, $h);

        try {
            $bin = self::capture(fn () => imagejpeg($flat, null, $quality));
        } finally {
            @imagedestroy($flat);
        }

        return $bin !== null ? ['jpg', $bin] : null;
    }

    /** Run an encoder that writes to the output buffer; return string|null. */
    protected static function capture(callable $encoder): ?string
    {
        ob_start();
        try {
            $ok = $encoder();
        } catch (\Throwable $e) {
            ob_end_clean();

            return null;
        }
        $out = ob_get_clean();

        if ($ok === false || $out === false || $out === '') {
            return null;
        }

        return $out;
    }

    /**
     * Re-encode a JPEG with EXIF orientation applied.
     *
     * @return array{0:string,1:string}|null
     */
    protected static function autoOrient(string $path, string $binary): ?array
    {
        try {
            $exif = @exif_read_data($path);
            $orientation = (int) ($exif['Orientation'] ?? 0);
            if ($orientation < 2 || $orientation > 8) {
                return null;
            }

            $img = @imagecreatefromstring($binary);
            if ($img === false) {
                return null;
            }

            $angle = 0;
            $flip = false;
            switch ($orientation) {
                case 3: $angle = 180; break;
                case 6: $angle = -90; break;
                case 8: $angle = 90; break;
                case 2: $flip = true; break;
                case 4: $flip = true; $angle = 180; break;
                case 5: $flip = true; $angle = -90; break;
                case 7: $flip = true; $angle = 90; break;
            }

            if ($flip) {
                imageflip($img, IMG_FLIP_HORIZONTAL);
            }
            if ($angle !== 0) {
                $rotated = imagerotate($img, $angle, 0);
                if ($rotated !== false) {
                    @imagedestroy($img);
                    $img = $rotated;
                }
            }

            try {
                return self::encode($img);
            } finally {
                @imagedestroy($img);
            }
        } catch (\Throwable $e) {
            return null;
        }
    }
}
