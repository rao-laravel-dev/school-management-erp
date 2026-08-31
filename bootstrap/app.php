<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',   // ye naya line add karein
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        // =========================================================================
        // YAHAN NAYI FILE REGISTER HO GI
        // =========================================================================
        then: function () {
            Route::middleware(['web', 'auth']) // Taake login check aur sessions active rahein
                ->prefix('')       // Browser me URL: domain.com/front-office/... banega
                // ->name('front_office.')       // Blade views me route('front_office.xxx') chalega
                ->group(base_path('routes/front_office.php'));
        },

    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Spatie Middleware Aliases yahan add honge
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
