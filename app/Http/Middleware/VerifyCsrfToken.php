<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'planificar-visita',
        'api/user/dashboard/*',
        'api/user/bookings/*',
        'api/user/reviews',
        'api/user/favorites',
        'api/user/favorites/*'
    ];
}