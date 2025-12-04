<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OwnAttendanceExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithEvents,
    WithStyles
{
    protected $filters;
    protected $attendances;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        if ($this->attendances) {
            return $this->attendances;
        }

        $teacher = Auth::user();

        // Ensure status is always an array
        $status = array_map(
            fn($s) => strtolower($s),
            (array) ($this->filters['status'] ?? ['attended','missed','late','undertime','upcoming'])
        );

        $query = Attendance::query()
            ->where('user_id', $teacher->id)
            ->whereIn('status', $status)
            ->with(['schedule.teacher', 'schedule.subject', 'schedule.room']);

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $from = Carbon::parse($this->filters['start_date'], 'Asia/Manila')->startOfDay();
            $to   = Carbon::parse($this->filters['end_date'], 'Asia/Manila')->endOfDay();
            $query->whereDate('created_at', '>=', $from->toDateString())
                  ->whereDate('created_at', '<=', $to->toDateString());
        }

        $this->attendances = $query->orderBy('created_at', 'asc')->get();

        Log::info('OwnAttendanceExport: Attendance count', [
            'count' => $this->attendances->count(),
            'teacher_id' => $teacher->id,
            'filters' => $this->filters
        ]);

        return $this->attendances;
    }

    public function headings(): array
    {
        $teacher = Auth::user();

        $period = 'All Dates';
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $period = Carbon::parse($this->filters['start_date'])->format('M d, Y') . 
                      " - " . 
                      Carbon::parse($this->filters['end_date'])->format('M d, Y');
        }

        // Cast to array before array_map to avoid errors when single string
        $statusFilter = !empty($this->filters['status'])
            ? implode(', ', array_map('ucfirst', (array)$this->filters['status']))
            : 'All Statuses';

        return [
            ["UCLM - My Attendance Report"],
            ["Teacher: " . $teacher->name],
            ["Department: " . strtoupper($teacher->department ?? 'N/A')],
            ["Period Covered: " . $period],
            ["Status Filter: " . $statusFilter],
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
            8 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 
                  'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 
                             'startColor' => ['rgb' => '0070C0']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $sheet->mergeCells('A1:L1');

                foreach (range('A','L') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $attendances = $this->attendances ?: $this->collection();
                $lastRow = $sheet->getHighestDataRow();
                $summaryRow = $lastRow + 2;

                $totalRecords = $attendances->count();
                $totalAttended = $attendances->filter(fn($a) => strtolower($a->status) === 'attended')->count();
                $totalLate = $attendances->filter(fn($a) => strtolower($a->status) === 'late')->count();
                $totalMissed = $attendances->filter(fn($a) => strtolower($a->status) === 'missed')->count();
                $totalUndertime = $attendances->filter(fn($a) => strtolower($a->status) === 'undertime')->count();

                $sheet->setCellValue('A'.$summaryRow, 'ATTENDANCE SUMMARY');
                $sheet->mergeCells('A'.$summaryRow.':L'.$summaryRow);
                $sheet->getStyle('A'.$summaryRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType'=> \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor'=>['rgb'=>'DDDDDD']],
                ]);

                $dataRow = $summaryRow + 1;
                $sheet->setCellValue('A'.$dataRow, 'Total Records')->setCellValue('B'.$dataRow, $totalRecords);
                $sheet->setCellValue('D'.$dataRow, 'Attended')->setCellValue('E'.$dataRow, $totalAttended);
                $sheet->setCellValue('G'.$dataRow, 'Late')->setCellValue('H'.$dataRow, $totalLate);
                $sheet->setCellValue('J'.$dataRow, 'Missed')->setCellValue('K'.$dataRow, $totalMissed);

                $dataRow2 = $summaryRow + 2;
                $sheet->setCellValue('D'.$dataRow2, 'Undertime')->setCellValue('E'.$dataRow2, $totalUndertime);

                foreach ([$dataRow, $dataRow2] as $row) {
                    $sheet->getStyle('A'.$row.':L'.$row)->applyFromArray([
                        'font' => ['bold'=>true],
                        'fill' => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor'=>['rgb'=>'F9F9F9']],
                    ]);
                }

                $sheet->getStyle('A'.$summaryRow.':L'.$dataRow2)->applyFromArray([
                    'borders' => ['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,'color'=>['rgb'=>'000000']]],
                ]);
            }
        ];
    }

    public function map($attendance): array
    {
        $schedule = $attendance->schedule;

        $timeIn  = $attendance->time_in ? Carbon::parse($attendance->time_in, 'Asia/Manila')->format('g:i A') : '-';
        $timeOut = $attendance->time_out ? Carbon::parse($attendance->time_out, 'Asia/Manila')->format('g:i A') : '-';
        $scheduleTime = 
            ($schedule->starts_at ? Carbon::parse($schedule->starts_at, 'Asia/Manila')->format('g:i A') : '-') 
            . ' - ' . 
            ($schedule->ends_at ? Carbon::parse($schedule->ends_at, 'Asia/Manila')->format('g:i A') : '-');

        $dayOfWeek = $schedule->day_of_week ?? '—';
        if (is_array($dayOfWeek)) {
            $dayOfWeek = implode(',', $dayOfWeek);
        }
        $dayOfWeek = strtoupper($dayOfWeek);

        $fullToShort = [
            'MONDAY'=>'M','TUESDAY'=>'T','WEDNESDAY'=>'W','THURSDAY'=>'TH',
            'FRIDAY'=>'F','SATURDAY'=>'SAT','SUNDAY'=>'SUN'
        ];
        $daysArray = array_map('trim', explode(',', $dayOfWeek));
        $dayOfWeekShorthand = implode('', array_map(fn($d)=>$fullToShort[$d] ?? $d, $daysArray));

        return [
            $attendance->created_at ? Carbon::parse($attendance->created_at, 'Asia/Manila')->format('M d, Y') : '-',
            $schedule->teacher->name ?? 'N/A',
            $schedule->teacher->department ?? 'N/A',
            $schedule->edp_code ?? '—',
            $schedule->subject->subject_code ?? 'N/A',
            $schedule->room->room_code ?? 'N/A',
            ucfirst($schedule->type ?? '—'),
            $dayOfWeekShorthand,
            $scheduleTime,
            $timeIn,
            $timeOut,
            ucfirst($attendance->status ?? '—'),
        ];
    }
}
