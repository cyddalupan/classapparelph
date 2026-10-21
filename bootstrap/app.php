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
    ->withCommands([
        \App\Console\Commands\BuildPrioritySlideshowVideo::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'staff' => \App\Http\Middleware\StaffMiddleware::class,
            'coo.access' => \App\Http\Middleware\CheckCooAccess::class,
            'cpo.access' => \App\Http\Middleware\CheckCpoAccess::class,
            'cmo.access' => \App\Http\Middleware\CheckCmoAccess::class,
            'prodmanager.access' => \App\Http\Middleware\CheckProdManagerClassAccess::class,
            'qa.access' => \App\Http\Middleware\CheckQaAccess::class,
            'trimmerqa.access' => \App\Http\Middleware\CheckTrimmerQaAccess::class,
            'agent.cleared' => \App\Http\Middleware\EnsureAgentActionsCleared::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\CheckTrimmerQaAccess::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/api/production/*',
            'sales/prototype/*/verify-payment',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // CSRF / session-token mismatch ("419 Page Expired"). Sporadic in production
        // when a form is submitted from a page that was open before the token rotated
        // (login / switch-account / password change) or was restored from mobile bfcache.
        // Instead of the raw 419 error screen, send the user back with a clear message
        // so they can simply retry. (Toybits 2026-10-16)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page and try again.',
                ], 419);
            }

            return redirect()->back()->withInput()->with(
                'error',
                'Session expired (security token). I-refresh ang page at subukan muli.'
            );
        });
    })->create();
