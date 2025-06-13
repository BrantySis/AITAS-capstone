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
            $events = Schedule::where('user_id', auth()->id())->get()->map(function ($schedule) {
                return [
                    'title' => $schedule->subject,
                    'start' => Carbon::parse($schedule->starts_at)->toIso8601String(),
                    'end' => Carbon::parse($schedule->ends_at)->toIso8601String(),
                ];
            });

            return view('dashboard.teacher', compact('events'));
        }

        public function calendar()
            {
                $events = Schedule::where('user_id', auth()->id())->get()->map(function ($schedule) {
                    return [
                        'title' => $schedule->subject,
                        'start' => Carbon::parse($schedule->starts_at)->toIso8601String(),
                        'end' => Carbon::parse($schedule->ends_at)->toIso8601String(),
                    ];
                });

                return view('teacher.calendar', compact('events'));
            }
}
