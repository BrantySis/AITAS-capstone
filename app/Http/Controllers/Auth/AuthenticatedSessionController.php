<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            // Redirect to a notice page or same login page with status
            return redirect()->route('verification.notice')
                ->with('status', 'Please verify your email before accessing the dashboard.');
        }

        // Redirect based on role
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'dean' => redirect()->route('dean.dashboard'),
            default => redirect()->route('login')
        };
    }

    /**
     * Handle logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
