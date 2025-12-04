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
use Illuminate\Support\Facades\Log;

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
        $status = array_map(fn($s) => strtolower($s), $this->filters['status'] ?? [
            'attended','missed','late','undertime','upcoming'
        ]);

        $query = Attendance::query()
            ->whereIn('status', $status)
            ->with(['schedule.teacher', 'schedule.subject', 'schedule.room']);

        if (!empty($this->filters['teacher_ids'])) {
            $query->whereIn('user_id', $this->filters['teacher_ids']);
        }

        // Filter by created_at date instead of schedule starts_at
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $from = Carbon::parse($this->filters['start_date'], 'Asia/Manila')->startOfDay();
            $to   = Carbon::parse($this->filters['end_date'], 'Asia/Manila')->endOfDay();

            $query->whereDate('created_at', '>=', $from->toDateString())
                  ->whereDate('created_at', '<=', $to->toDateString());
        }

        $attendances = $query->get()
            ->sortBy(function($attendance) {
                return $attendance->schedule->teacher->name ?? 'ZZZ';
            })
            ->values();

        Log::info('TeacherAttendanceExport: Attendance records count', [
            'count' => $attendances->count(),
            'filters' => $this->filters,
        ]);

        return $attendances;
    }

    /**
     * HEADER ROWS
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
            ? implode(', ', array_map(fn($s) => ucfirst(is_array($s) ? implode(', ', $s) : $s), $this->filters['status']))
            : 'All Statuses';

        return [
            ["UCLM - Teacher Attendance Report"],
            ["Department: " . strtoupper(is_array($department) ? implode(', ', $department) : $department)],
            ["Period Covered: " . $period],
            ["Status Filter: " . $status],
            ["Date Generated: " . Carbon::now('Asia/Manila')->format('M d, Y g:i A')],
            [],
            [
                'Date',
                'Instructor',
                'Department',
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
            7 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Auto-merge title row and add summary at the bottom
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->mergeCells('A1:L1');
                
                // Auto-size columns
                foreach (range('A', 'L') as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                $lastRow = $event->sheet->getHighestRow();
                $summaryRow = $lastRow + 2;
                $attendances = $this->collection();

                $totalRecords = $attendances->count();
                $totalAttended = $attendances->filter(fn($a) => strtolower($a->status) === 'attended')->count();
                $totalLate = $attendances->filter(fn($a) => strtolower($a->status) === 'late')->count();
                $totalMissed = $attendances->filter(fn($a) => strtolower($a->status) === 'missed')->count();
                $totalUndertime = $attendances->filter(fn($a) => strtolower($a->status) === 'undertime')->count();

                $event->sheet->setCellValue('A' . $summaryRow, 'ATTENDANCE SUMMARY');
                $event->sheet->mergeCells('A' . $summaryRow . ':L' . $summaryRow);
                $event->sheet->getStyle('A' . $summaryRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => 'center'],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'CCCCCC']],
                ]);

                $dataRow = $summaryRow + 1;
                $event->sheet->setCellValue('A' . $dataRow, 'Total Records');
                $event->sheet->setCellValue('B' . $dataRow, $totalRecords);
                $event->sheet->setCellValue('D' . $dataRow, 'Attended');
                $event->sheet->setCellValue('E' . $dataRow, $totalAttended);
                $event->sheet->setCellValue('G' . $dataRow, 'Late');
                $event->sheet->setCellValue('H' . $dataRow, $totalLate);
                $event->sheet->setCellValue('J' . $dataRow, 'Missed');
                $event->sheet->setCellValue('K' . $dataRow, $totalMissed);

                $dataRow2 = $summaryRow + 2;
                $event->sheet->setCellValue('D' . $dataRow2, 'Undertime');
                $event->sheet->setCellValue('E' . $dataRow2, $totalUndertime);

                foreach ([$dataRow, $dataRow2] as $row) {
                    $event->sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F9F9F9']],
                    ]);
                }

                $event->sheet->getStyle('A' . $summaryRow . ':L' . $dataRow2)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            }
        ];
    }

    /**
     * TABLE ROWS
     */
    public function map($attendance): array
{
    $schedule = $attendance->schedule;

    $timeIn  = $attendance->time_in ? Carbon::parse($attendance->time_in, 'Asia/Manila')->format('g:i A') : '-';
    $timeOut = $attendance->time_out ? Carbon::parse($attendance->time_out, 'Asia/Manila')->format('g:i A') : '-';

    $scheduleTime = 
        ($schedule->starts_at ? Carbon::parse($schedule->starts_at, 'Asia/Manila')->format('g:i A') : '-') 
        . ' - ' . 
        ($schedule->ends_at ? Carbon::parse($schedule->ends_at, 'Asia/Manila')->format('g:i A') : '-');

    // --- Convert day_of_week to shorthand without commas ---
    $dayOfWeek = $schedule->day_of_week;

    // Ensure it's a string
    if (is_array($dayOfWeek)) {
        $dayOfWeek = implode(',', $dayOfWeek);
    }

    // Normalize to uppercase
    $dayOfWeek = strtoupper($dayOfWeek);

    // Mapping full day names to shorthand
    $fullToShort = [
        'MONDAY' => 'M',
        'TUESDAY' => 'T',
        'WEDNESDAY' => 'W',
        'THURSDAY' => 'TH',
        'FRIDAY' => 'F',
        'SATURDAY' => 'SAT',
        'SUNDAY' => 'SUN',
    ];

    // Split by comma, map to shorthand, then join without separator
    $daysArray = array_map('trim', explode(',', $dayOfWeek));
    $dayOfWeekShorthand = implode('', array_map(fn($d) => $fullToShort[$d] ?? $d, $daysArray));

    return [
        $attendance->created_at ? Carbon::parse($attendance->created_at, 'Asia/Manila')->format('M d, Y') : '-',
        $schedule->teacher->name ?? 'N/A',
        $schedule->teacher->department ?? 'N/A',
        $schedule->edp_code ?? '—',
        $schedule->subject->subject_code ?? 'N/A',
        $schedule->room->room_code ?? 'N/A',
        ucfirst($schedule->type ?? '—'),
        $dayOfWeekShorthand, // Outputs MWF, TTH, SAT, SUN
        $scheduleTime,
        $timeIn,
        $timeOut,
        ucfirst($attendance->status ?? '—'),
    ];
}
}
