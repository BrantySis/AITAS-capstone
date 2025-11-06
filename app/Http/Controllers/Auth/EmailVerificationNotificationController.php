<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // ✅ Check if email already verified
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($this->getDashboardRoute($user));
        }

        // ✅ Send verification link
        $user->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent');
    }

    /**
     * Determine the dashboard route based on user role.
     */
    private function getDashboardRoute($user): string
    {
        if ($user->role === 'admin') {
            return route('dashboard.admin', absolute: false);
        }

        if ($user->role === 'teacher') {
            return route('dashboard.teacher', absolute: false);
        }

        // Default fallback (in case role is missing)
        return route('login', absolute: false);
    }
}
