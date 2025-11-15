<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Handle the email verification request.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        // If already verified, redirect to role-based dashboard
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($this->redirectBasedOnRole($user) . '?verified=1');
        }

        // Mark email as verified and fire the Verified event
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($this->redirectBasedOnRole($user) . '?verified=1');
    }

    /**
     * Determine redirect route based on user role.
     */
    private function redirectBasedOnRole($user): string
    {
        return match (true) {
            $user->isAdmin() => route('admin.dashboard', absolute: false),
            $user->isTeacher() => route('teacher.dashboard', absolute: false),
            $user->isDean() => route('dean.dashboard', absolute: false),
            default => route('login', absolute: false),
        };
    }
}
