<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if (method_exists($user, 'hasPermission') && $user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}

