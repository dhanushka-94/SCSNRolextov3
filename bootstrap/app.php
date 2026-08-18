<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\PreventSearchIndexing;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            PreventSearchIndexing::class,
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'admin' => EnsureUserIsAdmin::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            return $request->is('admin') || $request->is('admin/*')
                ? route('admin.login')
                : route('planter.login');
        });

        $middleware->redirectUsersTo(function () {
            if (Auth::guard('web')->check()) {
                return route('admin.dashboard');
            }

            if (Auth::guard('planter')->check()) {
                return route('planter.dashboard');
            }

            return route('planter.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
