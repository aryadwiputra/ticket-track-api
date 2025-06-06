<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomSpatieForbiddenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission, $guard = null)
    {
        if (auth($guard)->guest()) {
            return response()->json([
                'timestamp' => now()->toIso8601String(),
                'code' => 403,
                'message' => 'AUTHENTICATION_REQUIRED',
                'errors' => ['User must be authenticated to access this resource.'],
            ], 403);
        }

        if (!auth($guard)->user()->can($permission)) {
            return response()->json([
                'timestamp' => now()->toIso8601String(),
                'code' => 403,
                'message' => 'FORBIDDEN',
                'errors' => ["You don't have permission: {$permission}"],
            ], 403);
        }

        return $next($request);
    }
}
