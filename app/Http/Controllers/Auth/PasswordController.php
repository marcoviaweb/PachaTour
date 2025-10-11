<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): Response
    {
        return response('Password update - to be implemented');
    }
}