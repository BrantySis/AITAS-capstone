<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class TeacherAttendanceExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithEvents,
    WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Fetch attendance data based on filters
     */
    public function collection()
    {
        $status = array_map(fn($s) => ucfirst(strtolower($s)), $this->filters['status'] ?? [
            'Attended','Missed','Late','Undertime'
        ]);

        $query = Attendance::query()
            ->whereIn('status', $status)
            ->with(['schedule.teacher', 'schedule.subject', 'schedule.room']);

        if (!empty($this->filters['teacher_ids'])) {
            $query->whereIn('user_id', $this->filters['teacher_ids']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->filters['start_date'])->startOfDay(),
                Carbon::parse($this->filters['end_date'])->endOfDay(),
            ]);
        }

        return $query->get();
    }

    /**
     * HEADER ROWS (Professional SAP-style)
     */
    public function headings(): array
    {
        $department = $this->filters['department'] ?? 'All Departments';

        $period = 'All Dates';
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $period = Carbon::parse($this->filters['start_date'])->format('M d, Y') .
                      " - " .
                      Carbon::parse($this->filters['end_date'])->format('M d, Y');
        }

        $status = !empty($this->filters['status'])
            ? implode(', ', $this->filters['status'])
            : 'All Statuses';

        return [
            ["UCLM - Teacher Attendance Report"],  
            ["Department: " . $department],
            ["Period Covered: " . $period],
            ["Status Filter: " . $status],
            ["Date Generated: " . now()->format('M d, Y g:i A')],
            [], 
            [
                'Date',
                'Instructor',
                'Department',     // 🔥 NEW COLUMN ADDED
                'EDP Code',
                'Subject Code',
                'Room',
                'Type',
                'Day',
                'Time',
                'Time In',
                'Time Out',
                'Status',
            ]
        ];
    }

    /**
     * Style formatting for header
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true]], // Table header row
        ];
    }

    /**
     * Auto-merge title row
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // Now 12 columns (A to L)
                $event->sheet->mergeCells('A1:L1');  
            }
        ];
    }

    /**
     * TABLE ROWS
     */
    public function map($attendance): array
    {
        $schedule = $attendance->schedule;

        $timeIn  = $attendance->time_in ? Carbon::parse($attendance->time_in)->format('g:i A') : '-';
        $timeOut = $attendance->time_out ? Carbon::parse($attendance->time_out)->format('g:i A') : '-';

        $scheduleTime = 
            ($schedule->starts_at ? Carbon::parse($schedule->starts_at)->format('g:i A') : '-') 
            . ' - ' . 
            ($schedule->ends_at ? Carbon::parse($schedule->ends_at)->format('g:i A') : '-');

        return [
            $attendance->created_at ? $attendance->created_at->format('Y-m-d') : '-',
            $schedule->teacher->name ?? 'N/A',
            $schedule->teacher->department ?? 'N/A',        // 🔥 NEW COLUMN DATA
            $schedule->edp_code ?? '—',
            $schedule->subject->subject_code ?? 'N/A',
            $schedule->room->room_code ?? 'N/A',
            ucfirst($schedule->type ?? '—'),
            strtoupper($schedule->day_of_week ?? '—'),
            $scheduleTime,
            $timeIn,
            $timeOut,
            ucfirst($attendance->status ?? '—'),
        ];
    }
}
