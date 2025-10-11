<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Token de autenticación requerido',
                'error' => 'Unauthenticated'
            ], 401);
        }

        // Check if user is active
        if (!$request->user()->is_active) {
            return response()->json([
                'message' => 'Cuenta desactivada',
                'error' => 'Account disabled'
            ], 403);
        }

        return $next($request);
    }
}