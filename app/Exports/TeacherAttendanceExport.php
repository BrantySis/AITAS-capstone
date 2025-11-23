<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class TeacherAttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $teacherId = $this->filters['teacher_id'] ?? Auth::id();

        $query = Attendance::where('user_id', $teacherId)
            ->whereIn('status', ['Attended', 'Missed', 'Late', 'Undertime'])
            ->with(['schedule.teacher', 'schedule.subject', 'schedule.room']);

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('schedule.subject', fn($sub) => $sub->where('subject_code', 'like', "%$search%"))
                  ->orWhereHas('schedule.room', fn($room) => $room->where('room_code', 'like', "%$search%"))
                  ->orWhereHas('schedule.teacher', fn($t) => $t->where('name', 'like', "%$search%"));
            });
        }

        // Filter by status
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Filter by date range
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->filters['start_date'])->startOfDay(),
                Carbon::parse($this->filters['end_date'])->endOfDay(),
            ]);
        }

        // Filter by subject code
        if (!empty($this->filters['subject'])) {
            $query->whereHas('schedule.subject', fn($q) => $q->where('subject_code', $this->filters['subject']));
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Instructor',
            'EDP Code',
            'Subject Code',
            'Room',
            'Type',
            'Day',
            'Time',
            'Time In',
            'Time Out',
            'Status',
        ];
    }

    public function map($attendance): array
    {
        $schedule = $attendance->schedule;

        $timeIn  = $attendance->time_in ? Carbon::parse($attendance->time_in)->format('g:i A') : '-';
        $timeOut = $attendance->time_out ? Carbon::parse($attendance->time_out)->format('g:i A') : '-';

        return [
            $attendance->created_at ? $attendance->created_at->format('Y-m-d') : '-',
            $schedule->teacher->name ?? 'N/A',
            $schedule->edp_code ?? '—',
            $schedule->subject->subject_code ?? 'N/A',
            $schedule->room->room_code ?? 'N/A',
            ucfirst($schedule->type ?? '—'),
            strtoupper($schedule->day_of_week ?? '—'),
            optional($schedule->starts_at)->format('g:i A') . ' - ' . optional($schedule->ends_at)->format('g:i A'),
            $timeIn,
            $timeOut,
            ucfirst($attendance->status ?? '—'),
        ];
    }
}
