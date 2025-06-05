<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterTeacherController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\TeacherController;

Route::get('/', function () {
    return view('welcome');
});

// Updated dashboard route to load role-specific view
Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->isAdmin()) {
        return view('dashboard.admin');
    } elseif ($user->isTeacher()) {
        return view('dashboard.teacher');
    } else {
        abort(403); // Or redirect to a generic view
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Grouped routes that require authentication
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-only: Register new teacher routes
    Route::get('/admin/register-teacher', [RegisterTeacherController::class, 'create'])->name('register.teacher');
    Route::post('/admin/register-teacher', [RegisterTeacherController::class, 'store'])->name('register.teacher.store');
});

//  Admin routes for Teacher CRUD
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('teachers', App\Http\Controllers\Admin\TeacherController::class);
});
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('schedules', ScheduleController::class);
});
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('rooms', RoomController::class);
});

//teacher load
Route::middleware(['auth'])->group(function () {
    Route::get('/teacher/load', [App\Http\Controllers\Teacher\LoadController::class, 'index'])->name('teacher.load');
});


require __DIR__.'/auth.php';
