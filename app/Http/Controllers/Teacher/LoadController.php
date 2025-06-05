<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class LoadController extends Controller
{
    public function index()
    {
        // Get the currently logged-in teacher's schedules
        $schedules = Schedule::with('room')
            ->where('user_id', Auth::id())
            ->get();

        return view('teacher.load', compact('schedules'));
    }
}
