<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Fetch the data needed for the dashboard metrics
        $totalTeachers = User::where('role_id', 2)->count();
        $activeSubjects = Subject::where('is_active', true)->count(); 
        $totalRooms = Room::where('is_active', true)->count();

        // 2. Return the view, passing the data
        return view('admin.admin-dashboard', [
            'totalTeachers' => $totalTeachers,
            'activeSubjects' => $activeSubjects,
            'totalRooms' => $totalRooms,
        ]);
    }
}
