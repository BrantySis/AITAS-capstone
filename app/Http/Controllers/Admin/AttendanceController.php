<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherAttendanceExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceController extends Controller
{
    /**
     * Show teachers page with export modal
     */
    public function index()
    {
        $departments = User::where('role_id', 2)
                           ->select('department')
                           ->distinct()
                           ->pluck('department');

        $teachers = User::where('role_id', 2)->get();

        return view('admin.admin-teachers', compact('teachers', 'departments'));
    }

    /**
     * Bulk export attendance for selected teachers (Excel)
     */
    public function bulkExport(Request $request)
    {
        $request->validate([
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:users,id',
            'status'     => 'nullable|array',
            'status.*'   => 'in:attended,missed,late,undertime',
            'timeframe'  => 'required|in:daily,weekly,monthly,custom',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'department' => 'required|string',
        ]);

        // If specific teachers selected, use them; otherwise get all teachers in the department
        $teacherIds = $request->teacher_ids ?? User::where('role_id', 2)
                                        ->where('department', $request->department)
                                        ->pluck('id')
                                        ->toArray();

        if (empty($teacherIds)) {
            return back()->with('error', 'No teachers found for the selected department.');
        }

        // Add department to filters
        $filters = [
            'department'  => $request->department,
            'teacher_ids' => $teacherIds,
            'status'      => $request->status ?? ['attended','missed','late','undertime'],
        ];

        // Determine date range
        $now = Carbon::now('Asia/Manila');

        if ($request->timeframe == 'custom' && $request->start_date && $request->end_date) {

            $filters['start_date'] = $request->start_date;
            $filters['end_date']   = $request->end_date;

        } else {
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

        $filename = 'attendance_bulk_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new TeacherAttendanceExport($filters), $filename);
    }

    /**
     * AJAX endpoint to get teachers by department
     */
    public function getTeachersByDepartment(Request $request)
    {
        try {
            $department = $request->query('department');

            $teachers = User::where('role_id', 2)
                            ->where('department', $department)
                            ->get(['id', 'name']);

            return response()->json($teachers);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⭐ Export PDF for attendance (ANY DATE RANGE)
     */
    public function exportPDF(Request $request)
{
    $from = Carbon::parse($request->from)->startOfDay();
    $to   = Carbon::parse($request->to)->endOfDay();

    $attendances = Attendance::with(['schedule.teacher', 'schedule.subject', 'schedule.room'])
        ->whereBetween('created_at', [$from, $to])
        ->orderBy('created_at', 'asc')
        ->get();

    if ($attendances->isEmpty()) {
        return back()->with('error', 'No attendance records found for this date range.');
    }

    $pdf = Pdf::loadView('admin.pdf', [
        'attendances' => $attendances,
        'from'        => $from,
        'to'          => $to
    ])->setPaper('A4', 'portrait');

    return $pdf->download('Teacher_Attendance_Report.pdf');
}
}
