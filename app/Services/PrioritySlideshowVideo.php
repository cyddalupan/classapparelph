<?php

namespace App\Services;

/**
 * Renderer + encoder ng Priority Mockup Slideshow bilang MP4 (1080p H.264).
 *
 * Ang mabigat na gawain (ImageMagick frame rendering + ffmpeg encode) ay
 * tumatakbo sa background artisan command (`slideshow:build`) — hindi sa web
 * request — para hindi mag-hang ang tab habang nag-e-encode. Ang Progressive
 * progress ay ipinapadala sa `.json` status file na binabasa ng progress page.
 *
 * (Andrew 2026-10-16)
 */
class PrioritySlideshowVideo
{
    public const FRAME_FPS = 12;      // output fps (static slides — 12 ay sapat; mas mabilis mag-encode)
    public const MAX_AGE   = 43200;   // 12h — itapon ang lumang cached videos/status

    public static function dir(): string
    {
        $d = storage_path('app/pslides');
        if (!is_dir($d)) { @mkdir($d, 0775, true); }
        return $d;
    }

    /** Signature ng deck (dapat pareho sa controller at command). */
    public static function sig(array $slides): string
    {
        return md5(collect($slides)->map(function ($s) {
            return ($s['sales_number'] ?? '') . ':' . ($s['priority'] ?? '-') . ':' . (empty($s['img']) ? '0' : '1');
        })->implode('|'));
    }

    public static function key(string $scope, string $sig): string
    {
        return preg_replace('/[^a-z0-9]/i', '', $scope) . '_' . $sig;
    }

    public static function path(string $key): string       { return self::dir() . '/' . $key . '.mp4'; }
    public static function statusFile(string $key): string { return self::dir() . '/' . $key . '.json'; }
    public static function lockFile(string $key): string   { return self::dir() . '/' . $key . '.lock'; }

    public static function isValidKey(string $key): bool
    {
        return (bool) preg_match('/^[a-z0-9]{1,12}_[a-f0-9]{32}$/i', $key);
    }

    public static function status(string $key): array
    {
        $f = self::statusFile($key);
        if (is_file($f)) {
            $j = json_decode((string) @file_get_contents($f), true);
            if (is_array($j)) { return $j; }
        }
        return ['state' => 'idle', 'done' => 0, 'total' => 0];
    }

    public static function isReady(string $key): bool
    {
        return is_file(self::path($key)) && (self::status($key)['state'] ?? '') === 'done';
    }

    public static function isBuilding(string $key): bool
    {
        $l = self::lockFile($key);
        if (is_file($l) && (time() - filemtime($l)) < 900) { return true; }
        return (self::status($key)['state'] ?? '') === 'working';
    }

    private static function writeStatus(string $key, array $data): void
    {
        $data['ts'] = date('c');
        @file_put_contents(self::statusFile($key), json_encode($data));
    }

    /** Burahin ang lumang cached files (> MAX_AGE). */
    public static function gc(): void
    {
        foreach (glob(self::dir() . '/*') ?: [] as $f) {
            if (is_file($f) && filemtime($f) < time() - self::MAX_AGE) { @unlink($f); }
        }
    }

    /**
     * Render + encode. Ibinabalik ang mp4 path kapag tagumpay, null kapag bigo.
     * $slides = output ng PrototypeSalesController@buildSlideshowSlides.
     */
    public static function build(array $slides, string $key, int $sec = 6): ?string
    {
        if (!function_exists('proc_open')) { return null; }
        if (!self::run(['which', 'ffmpeg'])) { return null; }

        self::gc();

        $work = self::dir() . '/work_' . $key . '_' . substr(md5(uniqid('', true)), 0, 6);
        if (!@mkdir($work, 0775, true)) { return null; }

        @file_put_contents(self::lockFile($key), (string) getmypid());
        $total = count($slides);
        self::writeStatus($key, ['state' => 'working', 'done' => 0, 'total' => $total]);

        $fontB = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $fontR = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';

        $n = 0;
        try {
            foreach ($slides as $s) {
                $out  = sprintf('%s/frame_%04d.jpg', $work, $n);
                $args = self::frameArgs($s, $out, $fontB, $fontR);
                if (!self::run($args)) {
                    throw new \RuntimeException('ImageMagick frame render failed sa slide ' . ($n + 1));
                }
                $n++;
                if ($n % 4 === 0 || $n === $total) {
                    self::writeStatus($key, ['state' => 'working', 'done' => $n, 'total' => $total]);
                }
            }
            if ($n === 0) { throw new \RuntimeException('Walang frame na na-render.'); }

            $mp4Tmp = $work . '/slideshow.mp4';
            $ok = self::run([
                'ffmpeg', '-y', '-framerate', '1/' . $sec, '-i', $work . '/frame_%04d.jpg',
                '-vf', 'scale=1920:1080,format=yuv420p', '-r', (string) self::FRAME_FPS,
                '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '20',
                '-pix_fmt', 'yuv420p', '-movflags', '+faststart', $mp4Tmp,
            ]);
            if (!$ok || !is_file($mp4Tmp)) { throw new \RuntimeException('ffmpeg encode failed.'); }

            // Atomic publish para hindi mahatiran ng partial file.
            $dest = self::path($key);
            @rename($mp4Tmp, $dest);
            if (!is_file($dest)) { throw new \RuntimeException('Hindi na-publish ang video.'); }

            self::writeStatus($key, ['state' => 'done', 'done' => $total, 'total' => $total, 'size' => filesize($dest)]);
        } catch (\Throwable $e) {
            self::writeStatus($key, ['state' => 'error', 'done' => $n, 'total' => $total, 'error' => $e->getMessage()]);
        } finally {
            foreach (glob($work . '/*') ?: [] as $f) { @unlink($f); }
            @rmdir($work);
            @unlink(self::lockFile($key));
        }

        return self::isReady($key) ? self::path($key) : null;
    }

    /** Buuin ang ImageMagick argv para sa isang slide frame. */
    private static function frameArgs(array $s, string $out, string $fontB, string $fontR): array
    {
        // Hanapin ang mockup file sa disk.
        $imgPath = null;
        if (!empty($s['img'])) {
            $rel = ltrim(preg_replace('#^/storage/#', '', $s['img']), '/');
            foreach ([base_path('public/storage/' . $rel), storage_path('app/public/' . $rel)] as $cand) {
                if (is_file($cand)) { $imgPath = $cand; break; }
            }
        }

        $args = ['convert', '-size', '1920x1080', 'xc:#0b0f14', '-gravity', 'NorthWest'];

        if ($imgPath) {
            $args = array_merge($args, [
                '(', $imgPath, '-resize', '1200x1000>', '-background', '#0b0f14',
                '-gravity', 'center', '-extent', '1240x1080', ')',
                '-gravity', 'NorthWest', '-geometry', '+40+0', '-composite',
            ]);
        } else {
            $args = array_merge($args, [
                '-font', $fontR, '-pointsize', '34', '-fill', '#64748b',
                '-annotate', '+120+520', 'Walang mockup na naka-upload',
            ]);
        }

        $x = 1330;
        $y = 150;

        // Priority chip.
        if (!empty($s['priority'])) {
            $ptxt  = 'PRIORITY #' . $s['priority'];
            $chipW = 60 + (int) (strlen($ptxt) * 27);
            $args = array_merge($args, ['-fill', '#6f42c1', '-draw', 'roundrectangle ' . $x . ',' . $y . ' ' . ($x + $chipW) . ',' . ($y + 68) . ' 18,18']);
            $args = array_merge($args, ['-font', $fontB, '-pointsize', '40', '-fill', '#ffffff', '-annotate', '+' . ($x + 30) . '+' . ($y + 16), self::imEsc($ptxt)]);
            $y += 104;
        } else {
            $args = array_merge($args, ['-font', $fontB, '-pointsize', '34', '-fill', '#64748b', '-annotate', '+' . $x . '+' . $y, 'Walang priority']);
            $y += 84;
        }

        // Sale number.
        $args = array_merge($args, ['-font', $fontR, '-pointsize', '24', '-fill', '#94a3b8', '-annotate', '+' . $x . '+' . $y, self::imEsc((string) $s['sales_number'])]);
        $y += 62;

        // Customer (max 2 lines).
        foreach (self::wrapText(trim((string) ($s['customer'] ?: '—')), 20, 2) as $ln) {
            $args = array_merge($args, ['-font', $fontB, '-pointsize', '58', '-fill', '#ffffff', '-annotate', '+' . $x . '+' . $y, self::imEsc($ln)]);
            $y += 74;
        }
        $y += 22;

        // Info rows.
        $rows = [
            ['Dami (Qty)', (string) ($s['qty'] ?: '—')],
            ['Department', (string) $s['dept']],
            ['Stage', (string) ($s['stage'] ?: '—')],
            ['Due date', (string) ($s['due'] ?: '—')],
        ];
        foreach ($rows as $r) {
            $args = array_merge($args, [
                '-fill', '#141a22', '-draw', 'roundrectangle ' . $x . ',' . $y . ' 1870,' . ($y + 58) . ' 12,12',
                '-font', $fontR, '-pointsize', '28', '-fill', '#94a3b8', '-annotate', '+' . ($x + 20) . '+' . ($y + 15), self::imEsc($r[0]),
                '-font', $fontB, '-pointsize', '28', '-fill', '#e2e8f0', '-gravity', 'NorthEast', '-annotate', '+50+' . ($y + 15), self::imEsc($r[1]),
                '-gravity', 'NorthWest',
            ]);
            $y += 74;
        }

        // Footer.
        $args = array_merge($args, [
            '-font', $fontR, '-pointsize', '20', '-fill', '#475569',
            '-gravity', 'SouthWest', '-annotate', '+30+24',
            'CLASS Apparel PH — ' . \Carbon\Carbon::now('Asia/Manila')->format('M d, Y g:i A'),
            '-gravity', 'NorthWest',
        ]);

        return array_merge($args, ['-quality', '90', $out]);
    }

    private static function run(array $args): bool
    {
        $desc = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $proc = proc_open($args, $desc, $pipes);
        if (!is_resource($proc)) { return false; }
        stream_get_contents($pipes[1]); fclose($pipes[1]);
        stream_get_contents($pipes[2]); fclose($pipes[2]);
        return proc_close($proc) === 0;
    }

    private static function imEsc(string $t): string
    {
        $t = str_replace('\\', '\\\\', $t);
        $t = str_replace('%', '%%', $t);
        if (isset($t[0]) && $t[0] === '@') { $t = '\\' . $t; }
        return $t;
    }

    private static function wrapText(string $t, int $perLine, int $maxLines): array
    {
        $t = trim(preg_replace('/\s+/', ' ', $t));
        if ($t === '') { return ['—']; }
        if (mb_strlen($t) <= $perLine) { return [$t]; }

        $out = [];
        $cur = '';
        foreach (explode(' ', $t) as $w) {
            $try = $cur === '' ? $w : $cur . ' ' . $w;
            if (mb_strlen($try) <= $perLine) {
                $cur = $try;
            } else {
                if ($cur !== '') { $out[] = $cur; }
                $cur = $w;
                if (count($out) >= $maxLines) { break; }
            }
        }
        if ($cur !== '' && count($out) < $maxLines) { $out[] = $cur; }
        $out = array_slice($out, 0, $maxLines);

        if (mb_strlen(implode(' ', $out)) < mb_strlen($t)) {
            $last = array_pop($out);
            $out[] = rtrim(mb_substr($last, 0, max(1, $perLine - 1))) . '…';
        }
        return $out ?: ['—'];
    }
}
