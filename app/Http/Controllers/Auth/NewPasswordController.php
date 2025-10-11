<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return response('New password create - to be implemented');
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): Response
    {
        return response('New password store - to be implemented');
    }
}