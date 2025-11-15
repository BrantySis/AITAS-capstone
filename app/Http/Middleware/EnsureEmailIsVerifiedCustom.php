<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerifiedCustom
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Redirect guests to login
        if (!$user) {
            return redirect()->route('login')->with('status', 'Please log in first.');
        }

        // If user is not verified, redirect to custom email verification page
        if (!$user->email_verified_at) {
            // Allow access to verification page, resend page, and token route
            if (
                !$request->routeIs('email.verify') &&
                !$request->routeIs('email.resend') &&
                !$request->routeIs('email.verify.token')
            ) {
                return redirect()->route('email.verify')
                    ->with('status', 'Please verify your email before accessing this page.');
            }
        }

        // Verified users can access any page freely
        return $next($request);
    }
}
