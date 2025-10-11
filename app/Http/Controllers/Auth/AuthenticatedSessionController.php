<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return response('Login page - to be implemented');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): Response
    {
        return response('Login store - to be implemented');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        return response('Logout - to be implemented');
    }
}