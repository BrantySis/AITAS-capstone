<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Teacher\LoadController;
use App\Http\Controllers\Teacher\AttendanceController;

// Public welcome page
Route::get('/', function () {
    return view('welcome');
});

// Dashboard redirect based on role
Route::get('/dashboard', function () {
    $user = Auth::user();
    
    return match(optional($user->role)->name) {
        'admin' => view('dashboard.admin'),
        'teacher' => view('dashboard.teacher'),
        default => redirect()->route('profile.edit')
              ->with('error', 'Please complete your profile registration'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes (accessible to all authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin-only routes
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('teachers', TeacherController::class);
        Route::resource('schedules', ScheduleController::class);
        Route::resource('rooms', RoomController::class);
    });

// Teacher-only routes
Route::middleware(['auth', 'verified', 'teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/load', [LoadController::class, 'index'])->name('load');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [App\Http\Controllers\Teacher\AttendanceController::class, 'store'])->name('attendance.store');
    });

// Authentication routes
require __DIR__.'/auth.php';