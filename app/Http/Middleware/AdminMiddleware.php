<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Debe iniciar sesión para acceder al área de administración.');
        }

        // Check if user has admin role or is_admin flag
        $user = auth()->user();
        
        // Check admin permissions (email contains 'admin' or user id is 1 or role is 'admin')
        $isAdmin = $user->email && str_contains($user->email, 'admin') || 
                   $user->id === 1 || 
                   (isset($user->role) && $user->role === 'admin');
        
        if (!$isAdmin) {
            return redirect('/')->with('error', 'Acceso denegado. Se requieren permisos de administrador.');
        }

        return $next($request);
    }
}
