<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Teacher\LoadController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root URL based on role or show welcome
Route::get('/', function () {
    if (Auth::check()) {
        $role = optional(Auth::user()->role)->name;

        return match ($role) {
            'admin' => redirect()->route('dashboard.admin'),
            'teacher' => redirect()->route('dashboard.teacher'),
            default => redirect('/profile'),
        };
    }

    return view('welcome');
});
Route::get('/test-time', function () {
    dd(now()); // or dd(Carbon::now());
});

// Dashboard routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/admin', function () {
        return view('dashboard.admin');
    })->middleware('admin')->name('dashboard.admin');

    Route::get('/dashboard/teacher', [DashboardController::class, 'index'])
    ->middleware('teacher')
    ->name('dashboard.teacher');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin-only routes
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Teachers
        Route::resource('teachers', TeacherController::class);
        Route::get('teachers/import/form', [TeacherController::class, 'import'])->name('teachers.import');
        Route::post('teachers/import', [TeacherController::class, 'processImport'])->name('teachers.import.process');
        
        // Subjects
        Route::resource('subjects', SubjectController::class);
        Route::get('subjects/import/form', [SubjectController::class, 'import'])->name('subjects.import');
        Route::post('subjects/import', [SubjectController::class, 'processImport'])->name('subjects.import.process');
        Route::get('subjects/template', [SubjectController::class, 'downloadTemplate'])->name('subjects.downloadTemplate');
        
        // Rooms
        Route::resource('rooms', RoomController::class);
        
        // Schedules
        Route::resource('schedules', ScheduleController::class);
    });

// Teacher-only routes
Route::middleware(['auth', 'verified', 'teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/load', [LoadController::class, 'index'])->name('load');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');
        Route::post('/attendance/timeout', [AttendanceController::class, 'timeout'])->name('attendance.timeout'); 
    });

require __DIR__.'/auth.php';



