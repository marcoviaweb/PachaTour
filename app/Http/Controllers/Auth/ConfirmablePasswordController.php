<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): Response
    {
        return response('Confirm password - to be implemented');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): Response
    {
        return response('Confirm password store - to be implemented');
    }
}