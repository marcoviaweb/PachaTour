<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt view.
     */
    public function __invoke(Request $request): Response
    {
        return response('Email verification prompt - to be implemented');
    }
}