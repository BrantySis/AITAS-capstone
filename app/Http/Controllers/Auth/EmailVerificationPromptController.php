<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->getDashboardRoute($request->user()));
        }

        return view('auth.verify-email');
    }

    /**
     * Determine the dashboard route based on user role.
     */
    private function getDashboardRoute($user): string
    {
        if ($user->role === 'admin') {
            return route('admin.dashboard', absolute: false);
        }

        if ($user->role === 'teacher') {
            return route('teacher.dashboard', absolute: false);
        }

        if ($user->role === 'dean') {
            return route('dean.dashboard', absolute: false);
        }

        // Default fallback (in case role is missing)
        return route('login', absolute: false);
    }
}
