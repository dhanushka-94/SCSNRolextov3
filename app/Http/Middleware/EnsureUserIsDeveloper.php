<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsDeveloper
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user('web')?->isDeveloper()) {
            abort(403, 'Only the Super Admin (Developer) can access this area.');
        }

        return $next($request);
    }
}
