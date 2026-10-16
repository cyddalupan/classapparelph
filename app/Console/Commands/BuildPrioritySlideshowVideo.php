<?php

namespace App\Console\Commands;

use App\Services\PrioritySlideshowVideo;
use Illuminate\Console\Command;

/**
 * Background worker: i-render at i-encode ang Priority Mockup Slideshow MP4.
 * Sinisimulan ito ng web controller (detached) para hindi mag-hang ang request.
 *
 *   php artisan slideshow:build --user=1 --scope=prio --sec=6
 *
 * (Andrew 2026-10-16)
 */
class BuildPrioritySlideshowVideo extends Command
{
    protected $signature = 'slideshow:build
        {--user= : Acting user id (para sa class scoping)}
        {--scope=prio : prio | all}
        {--sec=6 : Seconds per slide}';

    protected $description = 'Render + encode ang Priority Mockup Slideshow bilang MP4 (background)';

    public function handle(): int
    {
        @set_time_limit(0);

        $userId = (int) $this->option('user');
        $scope  = $this->option('scope') === 'all' ? 'all' : 'prio';
        $sec    = (int) $this->option('sec');
        if ($sec < 2 || $sec > 30) { $sec = 6; }

        $user = \App\Models\User::find($userId);
        if (!$user) { $this->error('User not found: ' . $userId); return 1; }

        $controller = new \App\Http\Controllers\PrototypeSalesController();
        $slides = $controller->buildSlideshowSlides($user, $scope);
        if (empty($slides)) { $this->error('Walang slides para sa scope=' . $scope); return 1; }

        $key = PrioritySlideshowVideo::key($scope, PrioritySlideshowVideo::sig($slides));
        $this->info('Building ' . $key . ' (' . count($slides) . ' slides, ' . $sec . 's each)');

        $path = PrioritySlideshowVideo::build($slides, $key, $sec);

        if ($path) { $this->info('OK: ' . $path); return 0; }
        $this->error('FAILED: ' . $key);
        return 1;
    }
}
