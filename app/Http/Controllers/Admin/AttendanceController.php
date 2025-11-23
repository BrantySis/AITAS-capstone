<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherAttendanceExport;

class AttendanceController extends Controller
{
    /**
     * Show teachers page with export modal
     */
    public function index()
    {
        // Fetch only teachers (role_id = 2)
        $teachers = User::where('role_id', 2)->get();

        // Fetch all users for cards on the page (optional: can also filter teachers here)
        $users = User::all();

        return view('admin.admin-teachers', compact('users', 'teachers'));
    }

    /**
     * Export attendance for selected teacher
     */
    public function export(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'timeframe'  => 'required|in:daily,weekly,monthly',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);

        $teacher = User::where('role_id', 2)->findOrFail($request->teacher_id);

        $filters = [];

        // Optional: custom date range overrides timeframe
        if ($request->start_date && $request->end_date) {
            $filters['start_date'] = $request->start_date;
            $filters['end_date']   = $request->end_date;
        } else {
            $now = Carbon::now('Asia/Manila');
            switch ($request->timeframe) {
                case 'daily':
                    $filters['start_date'] = $now->startOfDay()->toDateString();
                    $filters['end_date']   = $now->endOfDay()->toDateString();
                    break;
                case 'weekly':
                    $filters['start_date'] = $now->startOfWeek()->toDateString();
                    $filters['end_date']   = $now->endOfWeek()->toDateString();
                    break;
                case 'monthly':
                    $filters['start_date'] = $now->startOfMonth()->toDateString();
                    $filters['end_date']   = $now->endOfMonth()->toDateString();
                    break;
            }
        }

        $filters['teacher_id'] = $teacher->id;

        $filename = 'attendance_' . str_replace(' ', '_', $teacher->name) . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new TeacherAttendanceExport($filters), $filename);
    }
}
