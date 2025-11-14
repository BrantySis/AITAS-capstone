<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
 public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($this->redirectBasedOnRole($user) . '?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($this->redirectBasedOnRole($user) . '?verified=1');
    }

    private function redirectBasedOnRole($user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard', absolute: false);
        } elseif ($user->isTeacher()) {
            return route('teacher.dashboard', absolute: false);
        } elseif ($user->isDean()) {
            return route('dean.dashboard', absolute: false);
        }

        return route('home', absolute: false);
    }
}
