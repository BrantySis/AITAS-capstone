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
        $user = $request->user();

        // If email is already verified, redirect to the appropriate dashboard
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($this->getDashboardRoute($user));
        }

        // Otherwise, show the email verification notice view
        return view('auth.verify-email', [
            'userName' => $user->name, // optional, for personalized notice
        ]);
    }

    /**
     * Determine the dashboard route based on user role.
     */
    private function getDashboardRoute($user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard', absolute: false),
            'teacher' => route('teacher.dashboard', absolute: false),
            'dean' => route('dean.dashboard', absolute: false),
            default => route('login', absolute: false),
        };
    }
}
