<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
        {
            $events = Schedule::with('subject')->where('user_id', auth()->id())->get()->map(function ($schedule) {
                return [
                    'title' => $schedule->subject->subject_name ?? 'Unknown Subject',
                    'start' => Carbon::parse($schedule->starts_at)->toIso8601String(),
                    'end' => Carbon::parse($schedule->ends_at)->toIso8601String(),
                ];
            });

            return view('dashboard.teacher', compact('events'));
        }

        public function calendar()
            {
                $events = Schedule::with(['subject', 'room'])->where('user_id', auth()->id())->get()->map(function ($schedule) {
                    // Generate different colors based on schedule type
                    $backgroundColor = $schedule->type === 'lab' ? '#10B981' : '#3B82F6';

                    return [
                        'title' => ($schedule->subject->subject_name ?? 'Unknown Subject') . ' (' . ($schedule->room->room_code ?? 'TBA') . ')',
                        'start' => Carbon::parse($schedule->starts_at)->toIso8601String(),
                        'end' => Carbon::parse($schedule->ends_at)->toIso8601String(),
                        'backgroundColor' => $backgroundColor,
                        'borderColor' => $backgroundColor,
                        'extendedProps' => [
                            'edp_code' => $schedule->edp_code,
                            'type' => $schedule->type,
                            'room' => $schedule->room->room_code ?? 'TBA',
                            'units' => $schedule->units
                        ]
                    ];
                });

                return view('teacher.calendar', compact('events'));
            }
}
