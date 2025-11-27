<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Attendance Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 15px;
            margin-bottom: 20px;
            padding-top: 10px;
        }

        .header img {
            width: 80px;
            margin-bottom: 8px;
        }

        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin-top: 8px;
            color: #1a1a1a;
        }

        .sub-heading {
            margin-top: 5px;
            font-size: 14px;
            color: #555;
            font-weight: 600;
        }

        .department-badge {
            background: #0066cc;
            color: white;
            padding: 5px 16px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 8px;
            border: 2px solid #0052a3;
        }

        .details {
            margin-top: 20px;
            margin-bottom: 15px;
            background: #f0f0f0;
            padding: 12px;
            border-left: 4px solid #0066cc;
        }

        .details table {
            width: 100%;
            font-size: 11px;
        }

        .details td {
            padding: 3px 0;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.report-table th {
            background: #4a5568;
            color: white;
            border: 1px solid #2d3748;
            padding: 10px 6px;
            font-size: 11px;
            text-align: center;
            font-weight: 600;
        }

        table.report-table td {
            border: 1px solid #cbd5e0;
            padding: 8px 6px;
            font-size: 10px;
            text-align: center;
        }

        table.report-table tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        .status-attended {
            color: #22543d;
            font-weight: 600;
        }

        .status-late {
            color: #c05621;
            font-weight: 600;
        }

        .status-missed {
            color: #c53030;
            font-weight: 600;
        }

        .status-undertime {
            color: #744210;
            font-weight: 600;
        }

        .summary-box {
            margin-top: 25px;
            border: 2px solid #cccccc;
            padding: 15px;
            background: #f9f9f9;
        }

        .summary-title {
            font-size: 13px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 10px;
            border-bottom: 2px solid #cccccc;
            padding-bottom: 5px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px;
            text-align: center;
            border-right: 1px solid #cccccc;
            font-size: 11px;
        }

        .summary-table td:last-child {
            border-right: none;
        }

        .summary-label {
            display: block;
            font-size: 10px;
            color: #666666;
            margin-bottom: 4px;
        }

        .summary-value {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
        }

        .footer-text {
            margin-top: 40px;
            font-size: 11px;
            color: #4a5568;
        }

        .signature-line {
            display: inline-block;
            width: 200px;
            border-bottom: 1px solid #333;
            margin-left: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 10px;
            color: #856404;
        }
    </style>
</head>
<body>

@php
    $logoPath = public_path('images/UClogo.png');
    $logoData = base64_encode(file_get_contents($logoPath));
@endphp

<div class="header">
    <img src="data:image/png;base64,{{ $logoData }}" alt="UCLM Logo">
    <div class="report-title">UNIVERSITY OF CEBU LAPU-LAPU & MANDAUE</div>
    <div class="sub-heading">TEACHER ATTENDANCE REPORT</div>
    <div class="department-badge">{{ strtoupper($department) }} DEPARTMENT</div>
</div>

<!-- DATE RANGE -->
<div class="details">
    <table>
        <tr>
            <td><span class="label">Period Covered:</span> {{ \Carbon\Carbon::parse($from)->format('F d, Y') }} — {{ \Carbon\Carbon::parse($to)->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td><span class="label">Department:</span> {{ strtoupper($department) }}</td>
        </tr>
        <tr>
            <td><span class="label">Generated On:</span> {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y h:i A') }}</td>
        </tr>
    </table>
</div>

<!-- ATTENDANCE TABLE -->
<table class="report-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Teacher</th>
            <th>Subject</th>
            <th>EDP Code</th>
            <th>Type</th>
            <th>Room</th>
            <th>Status</th>
            <th>Time-in</th>
            <th>Time-out</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($attendances as $a)
            @php
                $schedule = $a->schedule;
                
                // Use attendance created_at as the actual attendance date
                $attendanceDate = \Carbon\Carbon::parse($a->created_at, 'Asia/Manila');
                $timeIn = $a->time_in ? \Carbon\Carbon::parse($a->time_in, 'Asia/Manila')->format('g:i A') : '--';
                $timeOut = $a->time_out ? \Carbon\Carbon::parse($a->time_out, 'Asia/Manila')->format('g:i A') : '--';
                
                // Status class for styling
                $statusClass = 'status-' . strtolower($a->status ?? 'unknown');
            @endphp
            <tr>
                <td>{{ $attendanceDate->format('M d, Y') }}</td>
                <td>{{ $schedule->teacher->name ?? 'N/A' }}</td>
                <td>{{ $schedule->subject->subject_code ?? 'N/A' }}</td>
                <td>{{ $schedule->edp_code ?? 'N/A' }}</td>
                <td>{{ $schedule->type ?? 'N/A' }}</td>
                <td>{{ $schedule->room->room_code ?? 'N/A' }}</td>
                <td class="{{ $statusClass }}">{{ strtoupper($a->status ?? '--') }}</td>
                <td>{{ $timeIn }}</td>
                <td>{{ $timeOut }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center; padding:10px;">No attendance records found for this date range.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- SUMMARY -->
@if($attendances->isNotEmpty())
@php
    $totalAttended = $attendances->filter(function($a) { return strtolower($a->status) === 'attended'; })->count();
    $totalLate = $attendances->filter(function($a) { return strtolower($a->status) === 'late'; })->count();
    $totalMissed = $attendances->filter(function($a) { return strtolower($a->status) === 'missed'; })->count();
    $totalUndertime = $attendances->filter(function($a) { return strtolower($a->status) === 'undertime'; })->count();
    $totalRecords = $attendances->count();
@endphp
<div class="summary-box">
    <div class="summary-title">ATTENDANCE SUMMARY</div>
    <table class="summary-table">
        <tr>
            <td>
                <span class="summary-label">Total Records</span>
                <span class="summary-value">{{ $totalRecords }}</span>
            </td>
            <td>
                <span class="summary-label">Attended</span>
                <span class="summary-value" style="color: #22543d;">{{ $totalAttended }}</span>
            </td>
            <td>
                <span class="summary-label">Late</span>
                <span class="summary-value" style="color: #c05621;">{{ $totalLate }}</span>
            </td>
            <td>
                <span class="summary-label">Missed</span>
                <span class="summary-value" style="color: #c53030;">{{ $totalMissed }}</span>
            </td>
            <td>
                <span class="summary-label">Undertime</span>
                <span class="summary-value" style="color: #744210;">{{ $totalUndertime }}</span>
            </td>
        </tr>
    </table>
</div>
@endif

<!-- FOOTER -->
<div class="footer-text">
    <strong>Prepared By:</strong> <span class="signature-line"></span> 
    <strong style="margin-left: 40px;">Date:</strong> <span class="signature-line"></span>
    <br><br>
    <strong>Verified By:</strong> <span class="signature-line"></span> 
    <strong style="margin-left: 40px;">Date:</strong> <span class="signature-line"></span>
</div>

</body>
</html>