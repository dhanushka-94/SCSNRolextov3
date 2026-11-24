<?php

use App\Http\Middleware\EnforceMaintenanceMode;
use App\Http\Middleware\EnsureApiPlanter;
use App\Http\Middleware\EnsureApiStaff;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsDeveloper;
use App\Http\Middleware\PreventSearchIndexing;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
            PreventSearchIndexing::class,
            EnforceMaintenanceMode::class,
        ]);

        $middleware->api(append: [
            SecurityHeaders::class,
            EnforceMaintenanceMode::class,
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'admin' => EnsureUserIsAdmin::class,
            'developer' => EnsureUserIsDeveloper::class,
            'api.planter' => EnsureApiPlanter::class,
            'api.staff' => EnsureApiStaff::class,
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
        // Error responses (401, 403, 404, 500 ...) get the same security headers as normal responses.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            return SecurityHeaders::apply($response, $request);
        });

        // Every /api response is JSON, including errors.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        // Business-rule errors thrown by services become 422 responses in the API.
        $exceptions->render(function (InvalidArgumentException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return null;
        });
    })->create();
