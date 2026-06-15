<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (Auth::guest()) {
            abort(401, 'Unauthorized');
        }

        $user = Auth::user();

        if (!$user->role || !in_array($user->role, $roles)) {
            abort(403, 'Forbidden: kamu tidak punya akses');
        }

        return $next($request);
    }
}