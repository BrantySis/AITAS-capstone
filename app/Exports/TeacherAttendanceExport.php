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

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $teacherId = Auth::id();
        $query = Attendance::where('user_id', $teacherId)
            ->whereIn('status', ['Attended', 'Late', 'Missed'])
            ->with(['schedule.subject', 'schedule.room']);

        // Apply filters
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('schedule.subject', fn($sub) => $sub->where('subject_name', 'like', "%$search%"))
                  ->orWhereHas('schedule.room', fn($room) => $room->where('room_code', 'like', "%$search%"));
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->filters['start_date'])->startOfDay(),
                Carbon::parse($this->filters['end_date'])->endOfDay(),
            ]);
        }

        if (!empty($this->filters['subject'])) {
            $query->whereHas('schedule.subject', fn($q) => $q->where('subject_name', $this->filters['subject']));
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['Date', 'Subject', 'Room', 'Start Time', 'End Time', 'Status'];
    }

    public function map($attendance): array
    {
        return [
            $attendance->created_at->format('Y-m-d'),
            $attendance->schedule->subject->subject_name ?? 'N/A',
            $attendance->schedule->room->room_code ?? 'N/A',
            optional($attendance->schedule->starts_at)->format('g:i A') ?? '-',
            optional($attendance->schedule->ends_at)->format('g:i A') ?? '-',
            $attendance->status,
        ];
    }
}
