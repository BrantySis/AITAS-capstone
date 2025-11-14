<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;

class DeanTeacherLocationController extends Controller
{
    public function index()
    {
        // Get all teachers whose latest attendance has status = "attending"
        $teachers = User::where('role_id', 2) // role_id=2: TEACHER
            ->with(['latestAttendance' => function ($query) {
                $query->where('status', 'attending');
            }])
            ->get()
            ->filter(function ($teacher) {
                return $teacher->latestAttendance !== null;
            });

        return view('dean.dean-teachers', compact('teachers'));
    }
}
