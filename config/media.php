<?php

/*
|--------------------------------------------------------------------------
| Media / Image Optimization
|--------------------------------------------------------------------------
| Central settings for the web-safe image conversion applied to every
| upload entry point (see App\Services\ImageOptimizer).
|
| - optimize:       master switch. Set MEDIA_OPTIMIZE=false to disable and
|                   fall back to the raw Laravel store()/storeAs() behavior.
| - max_dimension:  longest side in pixels; larger images are scaled down.
| - quality:        encoder quality for WebP/JPEG (1-100).
| - format:         preferred output format for raster images: 'webp' or 'jpg'.
*/

return [
    'optimize'      => env('MEDIA_OPTIMIZE', true),
    'max_dimension' => (int) env('MEDIA_MAX_DIMENSION', 1920),
    'quality'       => (int) env('MEDIA_QUALITY', 82),
    'format'        => env('MEDIA_FORMAT', 'webp'),
];
