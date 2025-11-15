<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes (NOT logged in)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    // Registration page (optional: non-API)
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Forgot / Reset Password
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (LOGGED IN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Email verification page (for unverified users)
    Route::get('verify-email', function () {
        return view('auth.verify-email');
    })->name('email.verify');

    // Resend verification email
    Route::post('email/resend', [RegisteredUserController::class, 'resendVerificationEmail'])
        ->name('email.resend');

    // Verify email via token (Brevo)
    Route::get('verify-email/{token}', [RegisteredUserController::class, 'verifyEmail'])
        ->name('email.verify.token');

    // Password confirmation
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Update password
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Verified Users Only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified.custom'])->group(function () {

    // Teacher dashboard
    Route::prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
        // ... add other teacher routes here ...
    });

    // Admin dashboard
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
        // ... add other admin routes here ...
    });

    // Dean dashboard
    Route::prefix('dean')->name('dean.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Dean\DeanDashboardController::class, 'index'])->name('dashboard');
        // ... add other dean routes here ...
    });
});
