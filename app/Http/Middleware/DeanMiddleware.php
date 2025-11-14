<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DeanMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is logged in and has the "dean" role
        if (Auth::check() && Auth::user()->role_id === 3) {
            return $next($request);
        }

        // Redirect unauthorized users to the home page safely
        return redirect('/')->with('error', 'Unauthorized access');
    }
}
