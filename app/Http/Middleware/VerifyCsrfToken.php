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
        
        'api/*', // Exclude all API routes from CSRF verification
        'esim/create', // Exclude specific route for creating eSIM
        'esim/confirm', // Exclude specific route for confirming eSIM
    ];
}
