<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * While maintenance mode is on, only active administrators can use the system.
 * The admin sign-in page and the staff sign-in endpoint stay reachable so an
 * administrator can get in and switch it off.
 */
class EnforceMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! SystemSetting::maintenanceEnabled()) {
            return $next($request);
        }

        if ($request->is('api/*')) {
            return $this->handleApi($request, $next);
        }

        if ($request->routeIs(['admin.login', 'admin.login.store', 'admin.logout'])) {
            return $next($request);
        }

        $user = $request->user('web');

        if ($user && $user->isAdmin() && $user->isActive()) {
            return $next($request);
        }

        return response()->view('maintenance', [
            'message' => SystemSetting::maintenanceMessage(),
        ], 503, ['Retry-After' => 3600]);
    }

    private function handleApi(Request $request, Closure $next): Response
    {
        // Public metadata (so the app can show the maintenance message) and staff sign-in
        // (which only admins pass; the controller enforces that) stay reachable.
        if ($request->is('api/v1/meta', 'api/v1/auth/staff/login')) {
            return $next($request);
        }

        $user = Auth::guard('sanctum')->user();

        if ($user instanceof User && $user->isAdmin() && $user->isActive()) {
            return $next($request);
        }

        return response()->json([
            'message' => SystemSetting::maintenanceMessage()
                ?? 'The system is under maintenance. Please try again later.',
            'maintenance' => true,
        ], 503, ['Retry-After' => 3600]);
    }
}
