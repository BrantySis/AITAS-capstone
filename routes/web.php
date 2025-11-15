<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;

// Admin Controllers
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;

// Teacher Controllers
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\TeacherScheduleController;
use App\Http\Controllers\Teacher\LoadController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\NotificationController as TeacherNotificationController;

// Dean Controllers
use App\Http\Controllers\Dean\DeanDashboardController;
use App\Http\Controllers\Dean\DeanTeacherLocationController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        // If email not verified, send to /verify-email
        if (!$user->email_verified_at) {
            return redirect()->route('email.verify');
        }

        // Redirect based on role
        $role = optional($user->role)->name;
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'dean' => redirect()->route('dean.dashboard'),
            default => redirect('/profile'),
        };
    }

    return view('auth.login');
});

// Test route
Route::get('/test-time', fn() => dd(now()));

// Face registration
Route::view('/face-register', 'facerecognition.face_register');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES (SHARED)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified.custom'])->group(function () {
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN-ONLY ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified.custom', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        // ... other admin routes
    });

/*
|--------------------------------------------------------------------------
| TEACHER-ONLY ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified.custom', 'teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // ... other teacher routes
    });

/*
|--------------------------------------------------------------------------
| DEAN-ONLY ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified.custom', 'dean'])
    ->prefix('dean')
    ->name('dean.')
    ->group(function () {
        Route::get('/dashboard', [DeanDashboardController::class, 'index'])->name('dashboard');
        // ... other dean routes
    });

/*
|--------------------------------------------------------------------------
| UNVERIFIED EMAIL ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Show verify email page
    Route::get('verify-email', function () {
        return view('auth.verify-email');
    })->name('email.verify');

    // Resend verification email
    Route::post('email/resend', [RegisteredUserController::class, 'resendVerificationEmail'])
        ->name('email.resend');

    // Verify email via token
    Route::get('verify-email/{token}', [RegisteredUserController::class, 'verifyEmail'])
        ->name('email.verify.token');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
