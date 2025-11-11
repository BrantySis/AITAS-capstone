<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeacherNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();

        $notifications = TeacherNotification::where('user_id', $teacher->id)
            ->latest()
            ->take(50)
            ->get();

        return view('teacher.teacher-notifications', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = TeacherNotification::where('user_id', auth()->id())->find($id);
        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }
        return redirect()->back();
    }

    public function markAllAsRead()
    {
        TeacherNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back();
    }
}
