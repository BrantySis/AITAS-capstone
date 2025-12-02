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
use Illuminate\Support\Facades\Log;

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
     * Generate date range (shared by Excel & PDF)
     */
    private function getDateRange($request)
    {
        $now = Carbon::now('Asia/Manila');

        if ($request->timeframe === 'custom' && $request->start_date && $request->end_date) {
            return [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ];
        }

        return match ($request->timeframe) {
            'daily' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'weekly' => [
                $now->copy()->startOfWeek()->startOfDay(),
                $now->copy()->endOfWeek()->endOfDay(),
            ],
            'monthly' => [
                $now->copy()->startOfMonth()->startOfDay(),
                $now->copy()->endOfMonth()->endOfDay(),
            ],
            default => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * Bulk export (Excel)
     */
    public function bulkExport(Request $request)
    {
        $request->validate([
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:users,id',
            'status' => 'nullable|array',
            'status.*' => 'in:attended,missed,late,undertime,upcoming',
            'timeframe' => 'required|in:daily,weekly,monthly,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'department' => 'required|string',
        ]);

        $teacherIds = $request->teacher_ids ??
            User::where('role_id', 2)
                ->where('department', $request->department)
                ->pluck('id')
                ->toArray();

        if (empty($teacherIds)) {
            return back()->with('error', 'No teachers found for the selected department.');
        }

        [$from, $to] = $this->getDateRange($request);

        Log::info('Excel Export Range', [
            'from' => $from,
            'to' => $to,
            'teacher_ids' => $teacherIds
        ]);

        $filters = [
            'department' => $request->department,
            'teacher_ids' => $teacherIds,
            'status' => $request->status ?? ['attended','missed','late','undertime','upcoming'],
            'start_date' => $from->toDateTimeString(),
            'end_date' => $to->toDateTimeString(),
        ];

        return Excel::download(
            new TeacherAttendanceExport($filters),
            'attendance_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    /**
     * AJAX: Load teachers based on department
     */
    public function getTeachersByDepartment(Request $request)
    {
        $teachers = User::where('role_id', 2)
                        ->where('department', $request->query('department'))
                        ->get(['id', 'name']);

        return response()->json($teachers);
    }

    /**
     * Export PDF
     */
    public function exportPDF(Request $request)
    {
        $request->validate([
            'department' => 'required|string',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:users,id',
            'status' => 'nullable|array',
            'status.*' => 'in:attended,missed,late,undertime,upcoming',
            'timeframe' => 'required|in:daily,weekly,monthly,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $teacherIds = $request->teacher_ids ??
            User::where('role_id', 2)
                ->where('department', $request->department)
                ->pluck('id')
                ->toArray();

        if (empty($teacherIds)) {
            return back()->with('error', 'No teachers found for this department.');
        }

        [$from, $to] = $this->getDateRange($request);

        Log::info('PDF Export Range', [
            'from' => $from,
            'to'   => $to,
            'teacher_ids' => $teacherIds
        ]);

        $statuses = $request->status ?? ['attended','missed','late','undertime','upcoming'];

        // 🔥 FINAL FIX: use created_at for date filtering
        $attendances = Attendance::with(['schedule.teacher', 'schedule.subject', 'schedule.room'])
            ->whereIn('user_id', $teacherIds)
            ->whereIn('status', $statuses)
            ->whereBetween('created_at', [$from, $to])   // <-- FIXED
            ->orderBy('created_at')
            ->get();

        if ($attendances->isEmpty()) {
            Log::warning('PDF Export EMPTY', [
                'teachers' => $teacherIds,
                'from' => $from,
                'to' => $to,
                'status' => $statuses
            ]);

            return back()->with('error', 'No attendance records found for this date range.');
        }

        $pdf = Pdf::loadView('admin.pdf', [
            'attendances' => $attendances,
            'department'  => $request->department,
            'from'        => $from,
            'to'          => $to,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('Teacher_Attendance_Report.pdf');
    }
}
