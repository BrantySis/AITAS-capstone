<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        switch ($user->role_id) {
            case 1:
                return view('admin.admin-settings', compact('user'));
            case 2:
                return view('teacher.teacher-settings', compact('user'));
            case 3:
                return view('dean.dean-settings', compact('user'));
            default:
                return view('profile.edit', compact('user'));
        }
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // Redirect based on role
        switch ($request->user()->role_id) {
            case 1: // Admin
                return Redirect::route('profile.edit')->with('status', 'profile-updated');
            case 2: // Teacher
                return Redirect::route('profile.edit')->with('status', 'profile-updated');
            case 3: // Dean
                return Redirect::route('profile.edit')->with('status', 'profile-updated');
            default:
                return Redirect::route('profile.edit')->with('status', 'profile-updated');
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
