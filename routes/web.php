<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;

// Admin Controllers
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;

// Teacher Controllers
use App\Http\Controllers\Teacher\LoadController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\TeacherScheduleController;
use App\Http\Controllers\Teacher\NotificationController as TeacherNotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// -------------------------------------------------------------------------
// 1. PUBLIC ROUTES
// -------------------------------------------------------------------------

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

// Test route for debugging time
Route::get('/test-time', fn() => dd(now()));

// Face registration
Route::view('/face-register', 'facerecognition.face_register');

// -------------------------------------------------------------------------
// 2. AUTHENTICATED ROUTES (Shared)
// -------------------------------------------------------------------------

Route::middleware(['auth', 'verified'])->group(function () {
    // Admin Dashboard
    Route::get('/admin-dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('admin')
        ->name('dashboard.admin');

    // Teacher Dashboard
    Route::get('/teacher-dashboard', [DashboardController::class, 'index'])
        ->middleware('teacher')
        ->name('dashboard.teacher');

    // Profile management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

// -------------------------------------------------------------------------
// 3. ADMIN-ONLY ROUTES
// -------------------------------------------------------------------------

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
        Route::get('subjects/import', [SubjectController::class, 'import'])->name('subjects.import');
        Route::post('subjects/import', [SubjectController::class, 'processImport'])->name('subjects.processImport');
        Route::get('subjects/template', [SubjectController::class, 'downloadTemplate'])->name('subjects.downloadTemplate');

        // Rooms (exclude create/edit for modal usage)
        Route::resource('rooms', RoomController::class)->except(['create', 'edit']);

        // Schedules
        Route::resource('schedules', ScheduleController::class);

        // Notifications
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::patch('/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

        Route::post('/admin/schedules/import', [App\Http\Controllers\Admin\ScheduleController::class, 'importSchedules'])->name('schedules.import');

    });

// -------------------------------------------------------------------------
// 4. TEACHER-ONLY ROUTES
// -------------------------------------------------------------------------

Route::middleware(['auth', 'verified', 'teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {

        // Redirect legacy dashboard
        Route::get('/dashboard', fn() => redirect()->route('teacher.attendance.index'))->name('dashboard');

        // Load & Calendar
        Route::get('/schedule', [TeacherScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/load', [LoadController::class, 'index'])->name('load');
        Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');

        // Attendance
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/timeout', [AttendanceController::class, 'timeout'])->name('attendance.timeout');
        Route::post('/attendance/verify', [AttendanceController::class, 'verifyFace'])->name('attendance.verify');

        // Attendance history & export
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

// -------------------------------------------------------------------------
// 5. AUTH ROUTES
// -------------------------------------------------------------------------

require __DIR__.'/auth.php';
