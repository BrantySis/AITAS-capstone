<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Attendance Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 25px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header img {
            width: 70px;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }

        .sub-heading {
            margin-top: 3px;
            font-size: 13px;
        }

        .details {
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .details table {
            width: 100%;
            font-size: 12px;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.report-table th {
            background: #f1f1f1;
            border: 1px solid #666;
            padding: 6px;
            font-size: 12px;
            text-align: center;
        }

        table.report-table td {
            border: 1px solid #777;
            padding: 6px;
            font-size: 11px;
        }

        .footer-text {
            margin-top: 30px;
            font-size: 11px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <img src="{{ public_path('images/uclm-logo.png') }}" alt="UCLM Logo">

        <div class="report-title">UNIVERSITY OF CEBU LAPU-LAPU & MANDAUE</div>
        <div class="sub-heading">TEACHER ATTENDANCE REPORT</div>
    </div>

    <!-- DATE RANGE -->
    <div class="details">
        <table>
            <tr>
                <td><strong>Period Covered:</strong> {{ \Carbon\Carbon::parse($from)->format('F d, Y') }} — {{ \Carbon\Carbon::parse($to)->format('F d, Y') }}</td>
                <td style="text-align: right;"><strong>Generated On:</strong> {{ now()->format('F d, Y h:i A') }}</td>
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
                <th>Room</th>
                <th>Status</th>
                <th>Time-in</th>
                <th>Time-out</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($attendances as $a)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($a->created_at)->format('M d, Y') }}</td>
                    <td>{{ $a->schedule->teacher->name ?? 'N/A' }}</td>
                    <td>{{ $a->schedule->subject->subject_code ?? 'N/A' }}</td>
                    <td>{{ $a->schedule->room->room_code ?? 'N/A' }}</td>

                    <td style="text-transform: uppercase;">
                        {{ $a->status }}
                    </td>

                    <td>{{ $a->time_in ?? '--' }}</td>
                    <td>{{ $a->time_out ?? '--' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:10px;">
                        No attendance records found for this date range.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer-text">
        <strong>Prepared By:</strong> __________________________ <br><br>
        <strong>Verified By:</strong> __________________________
    </div>

</body>
</html>
