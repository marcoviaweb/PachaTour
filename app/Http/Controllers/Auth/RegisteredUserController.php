<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return response('Register create - to be implemented');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): Response
    {
        return response('Register store - to be implemented');
    }
}