<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
     public function index()
    {
        // You can pass data to your Blade view if needed
        // For now, just return a simple view
        return view('teacher.teacher-dashboard');
    }
}
