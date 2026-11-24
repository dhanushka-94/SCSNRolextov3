<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiStaff
{
    /**
     * Usage: "api.staff" for any active staff account, "api.staff:admin" for administrators (and the Developer),
     * "api.staff:developer" for the Super Admin (Developer) only.
     */
    public function handle(Request $request, Closure $next, ?string $scope = null): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json(['message' => 'This endpoint is for staff accounts only.'], 403);
        }

        if (! $user->isActive()) {
            return response()->json(['message' => 'This account is inactive.'], 403);
        }

        if ($scope === 'admin' && ! $user->isAdmin()) {
            return response()->json(['message' => 'Only administrators can access this.'], 403);
        }

        if ($scope === 'developer' && ! $user->isDeveloper()) {
            return response()->json(['message' => 'Only the Super Admin (Developer) can access this.'], 403);
        }

        return $next($request);
    }
}
