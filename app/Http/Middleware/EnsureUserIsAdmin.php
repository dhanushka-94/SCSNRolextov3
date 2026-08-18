<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user('web')?->isAdmin()) {
            abort(403, 'Only administrators can manage system users.');
        }

        return $next($request);
    }
}
