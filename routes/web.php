<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;

// Middleware
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\TeacherMiddleware;
use App\Http\Middleware\DeanMiddleware;

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
        $role = optional(Auth::user()->role)->name;
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
Route::middleware(['auth', 'verified'])->group(function () {

    // Profile management (shared)
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
Route::middleware(['auth', 'verified', AdminMiddleware::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Teachers
        Route::resource('teachers', TeacherController::class);
        Route::get('teachers/import/form', [TeacherController::class, 'import'])->name('teachers.import');
        Route::post('teachers/import', [TeacherController::class, 'processImport'])->name('teachers.import.process');

        // Subjects
        Route::resource('subjects', SubjectController::class);
        Route::get('subjects/import', [SubjectController::class, 'import'])->name('subjects.import');
        Route::post('subjects/import', [SubjectController::class, 'processImport'])->name('subjects.processImport');
        Route::get('subjects/template', [SubjectController::class, 'downloadTemplate'])->name('subjects.downloadTemplate');

        // Rooms
        Route::resource('rooms', RoomController::class)->except(['create', 'edit']);

        // Schedules
        Route::resource('schedules', ScheduleController::class);
        Route::post('/schedules/import', [ScheduleController::class, 'importSchedules'])->name('schedules.import');

        // Notifications
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::patch('/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    });

/*
|--------------------------------------------------------------------------
| TEACHER-ONLY ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', TeacherMiddleware::class])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        // Dashboard redirect
         Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Load & Schedule
        Route::get('/schedule', [TeacherScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/load', [LoadController::class, 'index'])->name('load');
        Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');

        // Attendance
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/timeout', [AttendanceController::class, 'timeout'])->name('attendance.timeout');
        Route::post('/attendance/verify', [AttendanceController::class, 'verifyFace'])->name('attendance.verify');
        Route::get('/history', [AttendanceController::class, 'history'])->name('history');
        Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

        // Face Verification
        Route::get('/face-verification', [AttendanceController::class, 'faceVerification'])->name('face.verification');
        Route::post('/face-verification/process', [AttendanceController::class, 'processFaceVerification'])->name('face.process');

        // Location Verification
        Route::get('/location-verify', [AttendanceController::class, 'locationVerify'])->name('location.verify');
        Route::post('/location-verify/process', [AttendanceController::class, 'processLocationVerify'])->name('location.process');

        // Notifications
        Route::get('notifications', [TeacherNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/{id}/read', [TeacherNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::patch('notifications/read-all', [TeacherNotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    });

/*
|--------------------------------------------------------------------------
| DEAN-ONLY ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', DeanMiddleware::class])
    ->prefix('dean')
    ->name('dean.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [DeanDashboardController::class, 'index'])->name('dashboard');
        Route::get('/teachers-attending', [DeanTeacherLocationController::class, 'index'])->name('teachers');

        // Profile / Settings
        // Route::get('/settings', [ProfileController::class, 'edit'])->name('settings');
        // Route::patch('/settings', [ProfileController::class, 'update'])->name('settings.update');
        // Route::delete('/settings', [ProfileController::class, 'destroy'])->name('settings.destroy');
    });

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
