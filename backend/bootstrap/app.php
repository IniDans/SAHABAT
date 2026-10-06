<?php

use App\Http\Middleware\HeaderKeamanan;
use App\Http\Middleware\PastikanAkunAktif;
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

            // Panel admin didaftarkan sebelum website agar "/" di subdomain admin menuju dashboard.
            $admin = Route::middleware('web');

            if ($domain = config('app.admin_domain')) {
                $admin->domain($domain);
            }

            $admin->group(base_path('routes/admin.php'));

            Route::middleware('web')->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(HeaderKeamanan::class);
        $middleware->alias(['aktif' => PastikanAkunAktif::class]);

        // Tamu yang membuka panel admin mendapat 404 agar alamat /login tidak terungkap.
        $middleware->redirectGuestsTo(function (Request $request) {
            abort_if($request->routeIs('admin.*'), 404);

            return route('login');
        });

        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
                || $request->getHost() === config('app.api_domain')
                || $request->expectsJson(),
        );
    })->create();
