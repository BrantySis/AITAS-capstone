<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Subject</th>
            <th>Room</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($attendances as $a)
        <tr>
            <td>{{ \Carbon\Carbon::parse($a->time_in)->format('M d, Y') }}</td>
            <td>{{ $a->schedule->subject->subject_name ?? 'N/A' }}</td>
            <td>{{ $a->schedule->room->room_name ?? 'N/A' }}</td>
            <td>{{ $a->time_in ? \Carbon\Carbon::parse($a->time_in)->format('h:i A') : '-' }}</td>
            <td>{{ $a->time_out ? \Carbon\Carbon::parse($a->time_out)->format('h:i A') : '-' }}</td>
            <td>{{ $a->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
