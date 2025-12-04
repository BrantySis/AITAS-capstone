<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;
use App\Models\Subject;
use App\Models\AdminNotification;
use App\Models\TeacherNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewScheduleNotification;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleController extends Controller
{
    private array $dayPatternMap = [
        'MWF' => ['Monday', 'Wednesday', 'Friday'],
        'TTH' => ['Tuesday', 'Thursday'],
        'Sat' => ['Saturday'],
        'Sun' => ['Sunday'],
    ];

    public function index(Request $request)
    {
        $query = Schedule::with(['teacher', 'room', 'subject']);

        if ($search = $request->input('search')) {
            $query->whereHas('teacher', fn($q) => $q->where('name', 'like', "%$search%"))
                  ->orWhereHas('subject', fn($q) => $q->where('subject_name', 'like', "%$search%"))
                  ->orWhereHas('room', fn($q) => $q->where('room_code', 'like', "%$search%"));
        }

        $nowDate = now()->format('Y-m-d');
        $nowTime = now()->format('H:i:s');

        if ($filter = $request->input('filter')) {
            if ($filter === 'in-progress') {
                $query->where('start_date', '<=', $nowDate)
                      ->where('end_date', '>=', $nowDate)
                      ->where('starts_at', '<=', $nowTime)
                      ->where('ends_at', '>=', $nowTime);
            } elseif ($filter === 'upcoming') {
                $query->where(function($q) use ($nowDate, $nowTime) {
                    $q->where('start_date', '>', $nowDate)
                      ->orWhere(function($q2) use ($nowDate, $nowTime) {
                          $q2->where('start_date', '=', $nowDate)
                             ->where('starts_at', '>', $nowTime);
                      });
                });
            } elseif ($filter === 'missed') {
                $query->where(function($q) use ($nowDate, $nowTime) {
                    $q->where('end_date', '<', $nowDate)
                      ->orWhere(function($q2) use ($nowDate, $nowTime) {
                          $q2->where('end_date', '=', $nowDate)
                             ->where('ends_at', '<', $nowTime);
                      });
                });
            }
        }

        $query->orderBy('start_date', 'asc')->orderBy('starts_at', 'asc');

        $schedules = $query->paginate(10);

        $teachers = User::where('role_id', 2)->get();
        $rooms = Room::all();
        $subjects = Subject::all();

        return view('admin.admin-schedules', compact('schedules', 'teachers', 'rooms', 'subjects'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'user_id'     => 'required|exists:users,id',
        'room_id'     => 'required|exists:rooms,id',
        'subject_id'  => 'required|exists:subjects,id',
        'edp_code'    => 'required|string|max:255',
        'units'       => 'required|integer|min:0',
        'type'        => 'required|in:lecture,lab',
        'day_of_week' => 'required|string',
        'start_date'  => 'required|date',
        'end_date'    => 'required|date|after_or_equal:start_date',
        'semester'    => 'required|string',
        'school_year' => 'required|string',
        'starts_at'   => 'required|date_format:H:i',
        'ends_at'     => 'required|date_format:H:i|after:starts_at',
    ]);

    try {
        // Normalize day pattern
        $expandedDays = $this->expandDayPattern($validated['day_of_week']);
        if (empty($expandedDays)) {
            return back()->withErrors(['day_of_week' => 'Invalid day pattern.'])->withInput();
        }
        $dayOfWeekToStore = implode(',', $expandedDays);

        // Normalize times
        $startsAt = Carbon::parse($validated['starts_at'])->format('H:i:s');
        $endsAt   = Carbon::parse($validated['ends_at'])->format('H:i:s');

        // Normalize dates
        $startDate = Carbon::parse($validated['start_date'])->format('Y-m-d');
        $endDate   = Carbon::parse($validated['end_date'])->format('Y-m-d');

        // Check for conflicts/duplicates
        $conflict = $this->checkScheduleConflict(
            $validated['user_id'],
            $validated['room_id'],
            $dayOfWeekToStore,
            $startsAt,
            $endsAt,
            $startDate,
            $endDate,
            null,
            $validated['edp_code']
        );

        if ($conflict === 'edp_conflict') {
            return back()->withErrors(['edp_code' => 'EDP Code already exists.'])->withInput();
        }
        if ($conflict === 'time_conflict') {
            return back()->withErrors(['conflict' => 'Schedule conflict detected for teacher or room.'])->withInput();
        }

        // Create schedule
        $schedule = Schedule::create([
            'user_id'     => $validated['user_id'],
            'room_id'     => $validated['room_id'],
            'subject_id'  => $validated['subject_id'],
            'edp_code'    => $validated['edp_code'],
            'units'       => $validated['units'],
            'type'        => $validated['type'],
            'day_of_week' => $dayOfWeekToStore,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'semester'    => $validated['semester'],
            'school_year' => $validated['school_year'],
            'starts_at'   => $startsAt,
            'ends_at'     => $endsAt,
        ]);

        $this->sendNotifications($schedule, 'New Schedule Added');

        return redirect()->route('admin.schedules.index')
            ->with('success', '✅ Schedule created and notifications sent.');

    } catch (\Exception $e) {
        Log::error('Schedule creation failed: ' . $e->getMessage());
        return back()->withErrors(['error' => '❌ Failed to create schedule.']);
    }
}

public function update(Request $request, Schedule $schedule)
{
    $validated = $request->validate([
        'user_id'     => 'required|exists:users,id',
        'room_id'     => 'required|exists:rooms,id',
        'subject_id'  => 'required|exists:subjects,id',
        'edp_code'    => 'required|string|max:255',
        'units'       => 'required|integer|min:0',
        'type'        => 'required|in:lecture,lab',
        'day_of_week' => 'required|string',
        'start_date'  => 'required|date',
        'end_date'    => 'required|date|after_or_equal:start_date',
        'semester'    => 'required|string',
        'school_year' => 'required|string',
        'starts_at'   => 'required|date_format:H:i',
        'ends_at'     => 'required|date_format:H:i|after:starts_at',
    ]);

    try {
        // Normalize day pattern
        $expandedDays = $this->expandDayPattern($validated['day_of_week']);
        if (empty($expandedDays)) {
            return back()->withErrors(['day_of_week' => 'Invalid day pattern.'])->withInput();
        }
        $dayOfWeekToStore = implode(',', $expandedDays);

        // Normalize times
        $startsAt = Carbon::parse($validated['starts_at'])->format('H:i:s');
        $endsAt   = Carbon::parse($validated['ends_at'])->format('H:i:s');

        // Normalize dates
        $startDate = Carbon::parse($validated['start_date'])->format('Y-m-d');
        $endDate   = Carbon::parse($validated['end_date'])->format('Y-m-d');

        // Check for conflicts/duplicates, excluding current schedule
        $conflict = $this->checkScheduleConflict(
            $validated['user_id'],
            $validated['room_id'],
            $dayOfWeekToStore,
            $startsAt,
            $endsAt,
            $startDate,
            $endDate,
            $schedule->id,
            $validated['edp_code']
        );

        if ($conflict === 'edp_conflict') {
            return back()->withErrors(['edp_code' => 'EDP Code already exists.'])->withInput();
        }
        if ($conflict === 'time_conflict') {
            return back()->withErrors(['conflict' => 'Schedule conflict detected for teacher or room.'])->withInput();
        }

        // Update schedule
        $schedule->update([
            'user_id'     => $validated['user_id'],
            'room_id'     => $validated['room_id'],
            'subject_id'  => $validated['subject_id'],
            'edp_code'    => $validated['edp_code'],
            'units'       => $validated['units'],
            'type'        => $validated['type'],
            'day_of_week' => $dayOfWeekToStore,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'semester'    => $validated['semester'],
            'school_year' => $validated['school_year'],
            'starts_at'   => $startsAt,
            'ends_at'     => $endsAt,
        ]);

        $this->sendNotifications($schedule, 'Schedule Updated');

        return redirect()->route('admin.schedules.index')
            ->with('success', '✅ Schedule updated and notification sent.');

    } catch (\Exception $e) {
        Log::error('Schedule update failed: ' . $e->getMessage());
        return back()->withErrors(['error' => '❌ Failed to update schedule.']);
    }
}

public function destroy(Schedule $schedule)
    {
        if ($schedule->attendances()->exists()) {
            return redirect()->route('admin.schedules.index')
                ->with('error', '❌ Cannot delete a schedule with recorded attendance.');
        }

        $subjectName = $schedule->subject->subject_name;
        $teacherName = $schedule->teacher->name;

        $schedule->delete();

        AdminNotification::create([
            'type' => 'schedule',
            'title' => 'Schedule Deleted',
            'message' => "Schedule for {$subjectName} assigned to {$teacherName} has been deleted.",
            'created_by' => auth()->id(),
        ]);

        TeacherNotification::create([
            'user_id' => $schedule->user_id,
            'type' => 'schedule',
            'title' => 'Schedule Deleted',
            'message' => "Your schedule for {$subjectName} has been deleted.",
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', '🗑️ Schedule deleted and notification sent.');
    }

    /**
     * Check for conflicts by pattern string (Option A)
     */
private function checkScheduleConflict(
    $userId,
    $roomId,
    string $dayPattern,
    string $startTime, // 'H:i:s' or 'H:i'
    string $endTime,
    string $startDate, // 'Y-m-d'
    string $endDate,
    $excludeId = null,
    $edpCode = null
) {
    Log::info("Checking conflicts for user {$userId}, room {$roomId}, dayPattern {$dayPattern}, start {$startTime}, end {$endTime}, startDate {$startDate}, endDate {$endDate}, exclude {$excludeId}, edp {$edpCode}");

    // 1) EDP conflict
    if ($edpCode) {
        $edpQuery = Schedule::where('edp_code', $edpCode);
        if ($excludeId) $edpQuery->where('id', '!=', $excludeId);
        if ($edpQuery->exists()) {
            Log::info("Conflict: edp_code {$edpCode} exists");
            return 'edp_conflict';
        }
    }

    // Expand day pattern for the incoming schedule
    $newDays = $this->expandDayPattern($dayPattern);
    if (empty($newDays)) {
        // nothing to compare — safe
        Log::info("No days expanded from pattern '{$dayPattern}'");
        return false;
    }

    // Normalize times to Carbon time objects for reliable comparison
    $newStart = Carbon::parse($startTime);
    $newEnd   = Carbon::parse($endTime);

    // fetch candidate schedules that overlap date range and either same user OR same room
    $candidates = Schedule::where(function($q) use ($userId, $roomId) {
            $q->where('user_id', $userId)
              ->orWhere('room_id', $roomId);
        })
        ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
        ->where('start_date', '<=', $endDate)
        ->where('end_date', '>=', $startDate)
        ->get();

    foreach ($candidates as $existing) {
        // expand existing's day list using helper (handles inconsistent DB formats)
        $existingDays = $this->expandDayPattern($existing->day_of_week);

        // if no shared day, skip
        if (count(array_intersect($newDays, $existingDays)) === 0) {
            continue;
        }

        // time overlap check: newStart < existingEnd AND newEnd > existingStart
        try {
            $existingStart = Carbon::parse($existing->starts_at);
            $existingEnd   = Carbon::parse($existing->ends_at);
        } catch (\Exception $e) {
            // If parse fails, skip this row but log it
            Log::warning("Failed to parse existing schedule times for id {$existing->id}");
            continue;
        }

        if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
            // conflict — either same user or same room
            Log::info("Time conflict with schedule id {$existing->id} (user {$existing->user_id}, room {$existing->room_id}) for days: " . implode(',', array_intersect($newDays, $existingDays)));
            return 'time_conflict';
        }
    }

    Log::info("No conflict detected");
    return false;
}

private function expandDayPattern(string|array $value): array
{
    if (is_array($value)) {
        // Normalize to proper case
        $result = array_values(array_filter(array_map(function($day) {
            return ucfirst(strtolower(trim($day)));
        }, $value)));
        Log::debug("expandDayPattern: Input was array, returned: [" . implode(', ', $result) . "]");
        return $result;
    }

    $originalValue = $value;
    
    // Remove all quotes and trim
    $value = trim(str_replace(['"', "'"], '', $value));
    
    Log::debug("expandDayPattern: Input='{$originalValue}', After cleaning='{$value}'");

    // Known short patterns (check uppercase)
    $valueUpper = strtoupper($value);
    if (isset($this->dayPatternMap[$valueUpper])) {
        $result = $this->dayPatternMap[$valueUpper];
        Log::debug("expandDayPattern: Matched pattern '{$valueUpper}' -> [" . implode(', ', $result) . "]");
        return $result;
    }

    // JSON array?
    if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            // Normalize to proper case
            $result = array_values(array_filter(array_map(function($day) {
                return ucfirst(strtolower(trim($day)));
            }, $decoded)));
            Log::debug("expandDayPattern: Decoded JSON -> [" . implode(', ', $result) . "]");
            return $result;
        }
    }

    // Remove brackets & split by comma
    $clean = str_replace(['[', ']'], '', $value);
    $parts = array_filter(array_map('trim', explode(',', $clean)));
    
    // Normalize each day to proper case (Monday, Tuesday, etc.)
    $result = array_values(array_map(function($day) {
        return ucfirst(strtolower($day));
    }, $parts));
    
    Log::debug("expandDayPattern: Split by comma and normalized -> [" . implode(', ', $result) . "]");
    
    return $result;
}

public function importSchedules(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:5120',
    ]);

    $imported = [];
    $failed = [];
    $duplicates = [];

    try {
        $rows = Excel::toArray([], $request->file('file'))[0];
        
        Log::info('Import started. Total rows: ' . count($rows));

        foreach ($rows as $index => $row) {
            // skip header row if it looks like header
            if ($index === 0 && isset($row[0]) && strtolower(trim((string)$row[0])) === 'teacher_name') {
                Log::info('Skipping header row');
                continue;
            }

            // normalize row values (guard for missing columns)
            $row = array_map(fn($v) => is_array($v) ? '' : trim((string)$v), $row);
            $row = array_pad($row, 13, null); // Ensure we have at least 13 columns

            // Destructure with all 13 columns
            [$teacherName, $roomCode, $subjectCode, $edpCode, $units, $type, $dayPatternRaw, $startDateRaw, $endDateRaw, $semester, $schoolYear, $startsAtRaw, $endsAtRaw] = $row;

            $rowNumber = $index + 1;
            
            Log::info("Processing row {$rowNumber}: Teacher={$teacherName}, Room={$roomCode}, Subject={$subjectCode}, EDP={$edpCode}");

            // required basic fields
            if (!$teacherName || !$roomCode || !$subjectCode || !$edpCode) {
                $failed[] = ['row' => $rowNumber, 'reason' => 'Missing required fields (teacher/room/subject/edp).'];
                continue;
            }

            $teacher = User::where('name', $teacherName)->where('role_id', 2)->first();
            $room    = Room::where('room_code', $roomCode)->first();
            $subject = Subject::where('subject_code', $subjectCode)->first();

            if (!$teacher) { $failed[] = ['row' => $rowNumber, 'reason' => "Teacher '{$teacherName}' not found."]; continue; }
            if (!$room) { $failed[] = ['row' => $rowNumber, 'reason' => "Room '{$roomCode}' not found."]; continue; }
            if (!$subject) { $failed[] = ['row' => $rowNumber, 'reason' => "Subject '{$subjectCode}' not found."]; continue; }

            // units (required)
            $unitsVal = is_numeric($units) ? (int)$units : ($subject->units ?? null);
            if ($unitsVal === null) {
                $failed[] = ['row' => $rowNumber, 'reason' => "Units missing and subject has no units."];
                continue;
            }

            // type
            $typeVal = strtolower((string)$type);
            if (!in_array($typeVal, ['lecture','lab'])) {
                $failed[] = ['row' => $rowNumber, 'reason' => "Invalid type '{$type}'. Use 'lecture' or 'lab'."];
                continue;
            }

            // day pattern -> expand
            $dayPattern = strtoupper((string)($dayPatternRaw ?? ''));
            $expandedDays = $this->expandDayPattern($dayPatternRaw ?: $dayPattern);
            if (empty($expandedDays)) {
                $failed[] = ['row' => $rowNumber, 'reason' => "Invalid day pattern '{$dayPatternRaw}'."];
                continue;
            }
            $dayOfWeekString = implode(',', $expandedDays);

            // parse dates & times
            try {
                $startDate = Carbon::parse($startDateRaw)->format('Y-m-d');
                $endDate   = Carbon::parse($endDateRaw)->format('Y-m-d');
                
                // Handle time formats - could be H:i or H:i:s
                $startsAt  = Carbon::createFromFormat('H:i', $startsAtRaw)->format('H:i:s');
                $endsAt    = Carbon::createFromFormat('H:i', $endsAtRaw)->format('H:i:s');
            } catch (\Exception $e) {
                Log::error("Row {$rowNumber}: Date/time parse error - " . $e->getMessage());
                $failed[] = ['row' => $rowNumber, 'reason' => "Invalid date/time format. Error: " . $e->getMessage()];
                continue;
            }

            if (Carbon::parse($endsAt)->lte(Carbon::parse($startsAt))) {
                $failed[] = ['row' => $rowNumber, 'reason' => "End time must be after start time."];
                continue;
            }
            if (Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
                $failed[] = ['row' => $rowNumber, 'reason' => "End date must be after or equal to start date."];
                continue;
            }

            // Check for exact duplicate schedule
            $exactDuplicate = $this->checkExactDuplicate(
                $teacher->id,
                $room->id,
                $subject->id,
                $dayOfWeekString,
                $startsAt,
                $endsAt,
                $startDate,
                $endDate,
                $edpCode
            );

            if ($exactDuplicate) {
                $duplicates[] = [
                    'row' => $rowNumber, 
                    'reason' => "⚠️ Schedule duplicate detected: {$teacherName} - {$subjectCode} ({$edpCode}) on {$dayPatternRaw} from {$startsAtRaw} to {$endsAtRaw} already exists.",
                    'existing_id' => $exactDuplicate->id
                ];
                continue;
            }

            // conflict check (different schedules but overlapping time/room)
            $conflict = $this->checkScheduleConflict(
                $teacher->id,
                $room->id,
                $dayOfWeekString,
                $startsAt,
                $endsAt,
                $startDate,
                $endDate,
                null,
                $edpCode
            );

            if ($conflict === 'edp_conflict') {
                $failed[] = ['row' => $rowNumber, 'reason' => "EDP Code '{$edpCode}' already exists."];
                continue;
            }
            if ($conflict === 'time_conflict') {
                $failed[] = ['row' => $rowNumber, 'reason' => "Schedule conflict detected for teacher or room on {$dayPatternRaw}."];
                continue;
            }

            // create schedule
            $schedule = Schedule::create([
                'user_id'     => $teacher->id,
                'room_id'     => $room->id,
                'subject_id'  => $subject->id,
                'edp_code'    => $edpCode,
                'units'       => $unitsVal,
                'type'        => $typeVal,
                'day_of_week' => $dayOfWeekString,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'semester'    => $semester,
                'school_year' => $schoolYear,
                'starts_at'   => $startsAt,
                'ends_at'     => $endsAt,
            ]);

            $imported[] = ['row' => $rowNumber, 'schedule_id' => $schedule->id];

            // create notifications in your custom tables
            TeacherNotification::create([
                'user_id' => $teacher->id,
                'type' => 'schedule',
                'title' => 'New Schedule Added',
                'message' => "A new schedule for {$subject->subject_name} has been assigned to you.",
                'created_by' => auth()->id(),
            ]);

            AdminNotification::create([
                'type' => 'schedule',
                'title' => 'Schedule Imported',
                'message' => "Schedule for {$subject->subject_name} assigned to {$teacher->name} has been imported successfully.",
                'created_by' => auth()->id(),
            ]);
        }

        $successCount = count($imported);
        $failureCount = count($failed);
        $duplicateCount = count($duplicates);

        $message = "✅ Import completed. Successfully imported: {$successCount}. Failed: {$failureCount}. Duplicates skipped: {$duplicateCount}.";

        return back()->with([
            'success' => $message,
            'imported' => $imported,
            'failed' => $failed,
            'duplicates' => $duplicates,
        ]);
    } catch (\Exception $e) {
        Log::error('Schedule import failed: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return back()->withErrors(['error' => '❌ Failed to import schedules. Error: ' . $e->getMessage()]);
    }
}

/**
 * Check if exact duplicate schedule exists
 */
private function checkExactDuplicate(
    $userId,
    $roomId,
    $subjectId,
    string $dayOfWeekString,
    string $startsAt,
    string $endsAt,
    string $startDate,
    string $endDate,
    string $edpCode
): ?Schedule
{
    // Expand the incoming day pattern for comparison
    $newDays = $this->expandDayPattern($dayOfWeekString);
    
    // Find schedules with same basic attributes
    $existingSchedules = Schedule::where('user_id', $userId)
        ->where('room_id', $roomId)
        ->where('subject_id', $subjectId)
        ->where('start_date', $startDate)
        ->where('end_date', $endDate)
        ->where('starts_at', $startsAt)
        ->where('ends_at', $endsAt)
        ->get();

    foreach ($existingSchedules as $schedule) {
        $existingDays = $this->expandDayPattern($schedule->day_of_week);
        
        // Sort both arrays for comparison
        sort($newDays);
        sort($existingDays);
        
        // If days match exactly, it's a duplicate
        if ($newDays === $existingDays) {
            Log::info("Exact duplicate found: Schedule ID {$schedule->id}");
            return $schedule;
        }
    }

    return null;
}

private function sendNotifications(Schedule $schedule, string $title = 'Schedule Notification')
{
    try {
        // Teacher notification
        TeacherNotification::create([
            'user_id' => $schedule->user_id,
            'type' => 'schedule',
            'title' => $title,
            'message' => "A new schedule for {$schedule->subject->subject_name} ({$schedule->edp_code}) has been assigned to you.",
            'created_by' => auth()->id(),
        ]);

        // Admin notification
        AdminNotification::create([
            'type' => 'schedule',
            'title' => $title,
            'message' => "Schedule for {$schedule->subject->subject_name} ({$schedule->edp_code}) assigned to {$schedule->teacher->name} has been created/updated.",
            'created_by' => auth()->id(),
        ]);

        // Optionally send Laravel Notifications as well
        // Notification::send($schedule->teacher, new NewScheduleNotification($schedule));

        Log::info("Notifications sent for Schedule ID {$schedule->id}");
    } catch (\Exception $e) {
        Log::error("Failed to send notifications for Schedule ID {$schedule->id}: " . $e->getMessage());
    }
}

}
