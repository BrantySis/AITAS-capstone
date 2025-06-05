<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && optional(Auth::user()->role)->name === 'teacher') {
            return $next($request);
        }
        
        return redirect('/dashboard')->with('error', 'Unauthorized access');
    }
}