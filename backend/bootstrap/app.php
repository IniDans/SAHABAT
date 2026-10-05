<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // API didaftarkan sebelum web agar subdomain API tidak tertangkap route web.
            $api = Route::middleware('api');

            if ($domain = config('app.api_domain')) {
                $api->domain($domain);
            } else {
                $api->prefix('api');
            }

            $api->group(base_path('routes/api.php'));

            Route::middleware('web')->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
                || $request->getHost() === config('app.api_domain')
                || $request->expectsJson(),
        );
    })->create();
