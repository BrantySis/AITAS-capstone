<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Schedule;
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

        $teacherIds = $request->teacher_ids ?? User::where('role_id', 2)
            ->where('department', $request->department)
            ->pluck('id')
            ->toArray();

        if (empty($teacherIds)) {
            return back()->with('error', 'No teachers found for the selected department.');
        }

        // Determine date range
        $now = Carbon::now('Asia/Manila');
        
        if ($request->timeframe === 'custom' && $request->start_date && $request->end_date) {
            $from = Carbon::parse($request->start_date, 'Asia/Manila')->startOfDay();
            $to   = Carbon::parse($request->end_date, 'Asia/Manila')->endOfDay();
        } else {
            switch ($request->timeframe) {
                case 'daily':
                    $from = $now->copy()->startOfDay();
                    $to   = $now->copy()->endOfDay();
                    break;
                case 'weekly':
                    $from = $now->copy()->startOfWeek()->startOfDay();
                    $to   = $now->copy()->endOfWeek()->endOfDay();
                    break;
                case 'monthly':
                    $from = $now->copy()->startOfMonth()->startOfDay();
                    $to   = $now->copy()->endOfMonth()->endOfDay();
                    break;
                default:
                    $from = $now->copy()->startOfDay();
                    $to   = $now->copy()->endOfDay();
            }
        }

        Log::info('Excel Export Date Range', [
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'timeframe' => $request->timeframe
        ]);

        $filters = [
            'department'  => $request->department,
            'teacher_ids' => $teacherIds,
            'status'      => $request->status ?? ['attended','missed','late','undertime'],
            'start_date'  => $from->toDateTimeString(),
            'end_date'    => $to->toDateTimeString(),
        ];

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
     * Export attendance to PDF
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

        $teacherIds = $request->teacher_ids ?? User::where('role_id', 2)
            ->where('department', $request->department)
            ->pluck('id')
            ->toArray();

        if (empty($teacherIds)) {
            return back()->with('error', 'No teachers found for the selected department.');
        }

        // Determine date range
        $now = Carbon::now('Asia/Manila');
        
        if ($request->timeframe === 'custom' && $request->start_date && $request->end_date) {
            $from = Carbon::parse($request->start_date, 'Asia/Manila')->toDateString();
            $to   = Carbon::parse($request->end_date, 'Asia/Manila')->toDateString();
        } else {
            switch ($request->timeframe) {
                case 'daily':
                    $from = $now->copy()->toDateString();
                    $to   = $now->copy()->toDateString();
                    break;
                case 'weekly':
                    $from = $now->copy()->startOfWeek()->toDateString();
                    $to   = $now->copy()->endOfWeek()->toDateString();
                    break;
                case 'monthly':
                    $from = $now->copy()->startOfMonth()->toDateString();
                    $to   = $now->copy()->endOfMonth()->toDateString();
                    break;
                default:
                    $from = $now->copy()->toDateString();
                    $to   = $now->copy()->toDateString();
            }
        }

        Log::info('PDF Export Date Range', [
            'from' => $from,
            'to' => $to,
            'timeframe' => $request->timeframe
        ]);

        $status = $request->status ?? ['attended','missed','late','undertime','upcoming'];

        $attendances = Attendance::with(['schedule.teacher', 'schedule.subject', 'schedule.room'])
            ->whereIn('user_id', $teacherIds)
            ->whereIn('status', $status)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->get()
            ->sortBy(function($attendance) {
                return $attendance->schedule->teacher->name ?? 'ZZZ';
            })
            ->values();

        if ($attendances->isEmpty()) {
            Log::warning("No attendance records found for PDF export.", [
                'department' => $request->department,
                'teacher_ids' => $teacherIds,
                'status' => $status,
                'from' => $from,
                'to' => $to,
            ]);
            return back()->with('error', 'No attendance records found for this date range.');
        }

        Log::info('PDF Export Attendance Count', [
            'count' => $attendances->count(),
            'first_date' => $attendances->first()->schedule->starts_at ?? 'N/A',
            'last_date' => $attendances->last()->schedule->starts_at ?? 'N/A'
        ]);

        $pdf = Pdf::loadView('admin.pdf', [
            'attendances' => $attendances,
            'from'        => $from,
            'to'          => $to,
            'department'  => $request->department,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('Teacher_Attendance_Report.pdf');
    }
}