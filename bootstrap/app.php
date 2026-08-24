<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'staff' => \App\Http\Middleware\StaffMiddleware::class,
            'coo.access' => \App\Http\Middleware\CheckCooAccess::class,
            'cpo.access' => \App\Http\Middleware\CheckCpoAccess::class,
            'cmo.access' => \App\Http\Middleware\CheckCmoAccess::class,
            'prodmanager.access' => \App\Http\Middleware\CheckProdManagerClassAccess::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/api/production/*',
            'sales/prototype/*/verify-payment',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
