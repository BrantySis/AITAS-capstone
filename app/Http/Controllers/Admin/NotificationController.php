<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminNotification as Notification; // ✅ Correct alias

class NotificationController extends Controller
{
    /**
     * Display the list of notifications.
     */
    public function index()
    {
        // Fetch latest 50 notifications
        $notifications = Notification::with('user')
            ->latest()
            ->take(50)
            ->get();

        return view('admin.admin-notifications', compact('notifications'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return redirect()->back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->back();
    }
}
