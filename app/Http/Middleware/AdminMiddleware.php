<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && optional(Auth::user()->role)->name === 'admin') {
            return $next($request);
        }
        
        return redirect('/dashboard')->with('error', 'Unauthorized access');
    }
}