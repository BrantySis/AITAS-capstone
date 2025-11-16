<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeacherNotification;

class NotificationController extends Controller
{
    public function index()
    {
        // Safety: ensure only teachers access this
        if (auth()->user()->role->name !== 'teacher') {
            abort(403, 'Access denied.');
        }

        $notifications = TeacherNotification::where('user_id', auth()->id())
            ->latest()
            ->take(50)
            ->get();

        return view('teacher.teacher-notifications', compact('notifications'));
    }

    public function markAsRead($id)
    {
        if (auth()->user()->role->name !== 'teacher') {
            abort(403, 'Access denied.');
        }

        $notification = TeacherNotification::where('user_id', auth()->id())->find($id);

        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return redirect()->back();
    }

    public function markAllAsRead()
    {
        if (auth()->user()->role->name !== 'teacher') {
            abort(403, 'Access denied.');
        }

        TeacherNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back();
    }
}
