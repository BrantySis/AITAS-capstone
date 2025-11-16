<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DeanTeacherLocationController extends Controller
{
    public function index(Request $request)
    {
        // Get search query from the request
        $search = $request->input('search');

        // Get all teachers whose latest attendance has status = "attending"
        $teachers = User::where('role_id', 2) // role_id=2: TEACHER
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->with(['latestAttendance' => function ($query) {
                $query->where('status', 'attending')
                      ->with(['room', 'subject']); // Eager load room and subject
            }])
            ->get()
            ->filter(fn($teacher) => $teacher->latestAttendance !== null);

        return view('dean.dean-teachers', compact('teachers', 'search'));
    }
}
