<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;

// Admin Controllers
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\AdminDashboardController; // Corrected usage

// Teacher Controllers
use App\Http\Controllers\Teacher\LoadController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\DashboardController; // Used for teacher dashboard and calendar

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Routes are grouped logically by access level and functionality.
*/

// =========================================================================
// 1. PUBLIC/MISC ROUTES
// =========================================================================

// Redirect root URL based on role
Route::get('/', function () {
    if (Auth::check()) {
        $role = optional(Auth::user()->role)->name;

        // Use the existing route names for redirection
        return match ($role) {
            'admin' => redirect()->route('dashboard.admin'),
            'teacher' => redirect()->route('dashboard.teacher'),
            default => redirect('/profile'),
        };
    }

    return view('welcome');
});

// Test time route
Route::get('/test-time', function () {
    dd(now());
});

// Face registration route
Route::get('/face-register', function () {
    return view('facerecognition.face_register');
});

// =========================================================================
// 2. AUTHENTICATED ROUTES (Shared)
// =========================================================================

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard routes (Corrected URI and Middleware placement)
    Route::get('/admin-dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('admin')
        ->name('dashboard.admin'); // Name maintained: dashboard.admin

    Route::get('/teacher-dashboard', [DashboardController::class, 'index'])
        ->middleware('teacher')
        ->name('dashboard.teacher'); // Name maintained: dashboard.teacher
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =========================================================================
// 3. ADMIN-ONLY ROUTES
// =========================================================================

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        // Teachers Resource & Imports
        Route::resource('teachers', TeacherController::class);
        Route::get('teachers/import/form', [TeacherController::class, 'import'])->name('teachers.import');
        Route::post('teachers/import', [TeacherController::class, 'processImport'])->name('teachers.import.process');
        
        // Subjects Resource & Imports/Template
        Route::resource('subjects', SubjectController::class);
        Route::get('subjects/import', [SubjectController::class, 'import'])->name('subjects.import');
        Route::post('subjects/import', [SubjectController::class, 'processImport'])->name('subjects.processImport');
        Route::get('subjects/template', [SubjectController::class, 'downloadTemplate'])->name('subjects.downloadTemplate');
        
        // Rooms Resource (EXCLUDE create and edit as requested for modal implementation)
        Route::resource('rooms', RoomController::class)->except([
            'create', 
            'edit'
        ]);
        
        // Schedules Resource
        Route::resource('schedules', ScheduleController::class);
    });

// =========================================================================
// 4. TEACHER-ONLY ROUTES
// =========================================================================

Route::middleware(['auth', 'verified', 'teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        
        // Static View Helpers
        // NOTE: dashboard is handled in Section 2, but needs a redirect/view if accessed directly via /teacher/dashboard
        Route::get('/dashboard', function () {
            // Redirects to the main dashboard route defined above
            return redirect()->route('dashboard.teacher');
        })->name('dashboard');

        // Static View Routes (Using Route::view for simpler ones)
        Route::view('/notifications', 'teacher.notifications')->name('notifications');
        Route::view('/forms', 'teacher.forms')->name('forms');
        Route::view('/grades', 'teacher.grades')->name('grades');
        Route::view('/evaluation', 'teacher.evaluation')->name('evaluation');
        
        // Load & Calendar
        Route::get('/load', [LoadController::class, 'index'])->name('load');
        Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');

        // History
        Route::get('/history', [AttendanceController::class, 'history'])->name('history');
        Route::get('/teacher/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export'); // Name maintained

        // Attendance Actions
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/timeout', [AttendanceController::class, 'timeout'])->name('attendance.timeout');
        Route::post('/attendance/verify', [AttendanceController::class, 'verifyFace'])->name('attendance.verify');

        // Face Verification Pages
        Route::get('/face-verification', [AttendanceController::class, 'faceVerification'])->name('teacher.face.verification'); // Name maintained
        Route::post('/face-verification/process', [AttendanceController::class, 'processFaceVerification'])->name('teacher.face.process'); // Name maintained

        // Location Verification Pages
        Route::get('/location-verify', [AttendanceController::class, 'locationVerify'])->name('teacher.location.verify'); // Name maintained
        Route::post('/location-verify/process', [AttendanceController::class, 'processLocationVerify'])->name('teacher.location.process'); // Name maintained
    });

// =========================================================================
// 5. LARAVEL AUTH ROUTES
// =========================================================================

require __DIR__.'/auth.php';