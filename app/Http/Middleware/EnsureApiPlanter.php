<?php

namespace App\Http\Middleware;

use App\Models\Planter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiPlanter
{
    public function handle(Request $request, Closure $next): Response
    {
        $planter = $request->user();

        if (! $planter instanceof Planter) {
            return response()->json(['message' => 'This endpoint is for planter accounts only.'], 403);
        }

        if (! $planter->isApproved()) {
            return response()->json(['message' => 'Your registration is not active.'], 403);
        }

        return $next($request);
    }
}
