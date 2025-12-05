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

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $from = Carbon::parse($this->filters['start_date'], 'Asia/Manila')->startOfDay();
            $to   = Carbon::parse($this->filters['end_date'], 'Asia/Manila')->endOfDay();

            $query->whereDate('created_at', '>=', $from->toDateString())
                  ->whereDate('created_at', '<=', $to->toDateString());
        }

        $attendances = $query->get()
            ->sortBy(fn($attendance) => $attendance->schedule->teacher->name ?? 'ZZZ')
            ->values();

        Log::info('TeacherAttendanceExport: Attendance records count', [
            'count' => $attendances->count(),
            'filters' => $this->filters,
        ]);

        return $attendances;
    }

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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->mergeCells('A1:L1');

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

    public function map($attendance): array
    {
        $schedule = $attendance->schedule;

        // Time In / Out
        $timeIn  = $attendance->time_in
            ? Carbon::parse($attendance->time_in, 'Asia/Manila')->format('g:i A')
            : '-';
        $timeOut = $attendance->time_out
            ? Carbon::parse($attendance->time_out, 'Asia/Manila')->format('g:i A')
            : '-';

        // Schedule Time
        $scheduleTime =
            ($schedule->starts_at ? Carbon::parse($schedule->starts_at)->format('g:i A') : '-') . ' - ' .
            ($schedule->ends_at ? Carbon::parse($schedule->ends_at)->format('g:i A') : '-');

        // ---- TYPE NORMALIZATION ----
        $type = strtolower($schedule->type ?? '');
        if (in_array($type, ['lecture','lec','le'])) $type = 'LEC';
        elseif (in_array($type, ['laboratory','lab','l'])) $type = 'LAB';
        else $type = strtoupper($type ?: '—');

        // ---- DAY NORMALIZATION ----
        $dayOfWeek = $schedule->day_of_week ?? '';
        if (is_array($dayOfWeek)) $dayOfWeek = implode(',', $dayOfWeek);
        $dayOfWeek = strtoupper($dayOfWeek);

        $fullToShort = [
            'MONDAY'    => 'M',
            'TUESDAY'   => 'T',
            'WEDNESDAY' => 'W',
            'THURSDAY'  => 'TH',
            'FRIDAY'    => 'F',
            'SATURDAY'  => 'SAT',
            'SUNDAY'    => 'SUN',
        ];

        $daysArray = preg_split('/[,\s]+/', $dayOfWeek);
        $dayShortArray = [];
        foreach ($daysArray as $d) {
            if (isset($fullToShort[$d])) $dayShortArray[] = $fullToShort[$d];
        }

        $dayOfWeekShorthand = implode('', $dayShortArray);
        if ($dayOfWeekShorthand === 'MWF') $dayOfWeekShorthand = 'MWF';
        elseif ($dayOfWeekShorthand === 'TTH') $dayOfWeekShorthand = 'TTH';
        elseif (in_array($dayOfWeekShorthand, ['SAT','SUN'])) $dayOfWeekShorthand = $dayOfWeekShorthand;
        else $dayOfWeekShorthand = implode(', ', $dayShortArray);

        return [
            $attendance->created_at
                ? Carbon::parse($attendance->created_at, 'Asia/Manila')->format('M d, Y')
                : '-',
            $schedule->teacher->name ?? 'N/A',
            $schedule->teacher->department ?? 'N/A',
            $schedule->edp_code ?? '—',
            $schedule->subject->subject_code ?? 'N/A',
            $schedule->room->room_code ?? 'N/A',
            $type,
            $dayOfWeekShorthand,
            $scheduleTime,
            $timeIn,
            $timeOut,
            ucfirst($attendance->status ?? '—'),
        ];
    }
}
