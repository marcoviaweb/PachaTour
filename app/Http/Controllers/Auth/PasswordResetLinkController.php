<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return response('Password reset link create - to be implemented');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): Response
    {
        return response('Password reset link store - to be implemented');
    }
}