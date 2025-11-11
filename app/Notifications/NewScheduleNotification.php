<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewScheduleNotification extends Notification
{
   use Queueable;

    protected $schedule;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $schedule
     */
    public function __construct($schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // For now, only database. You can later add 'broadcast' or 'mail' if needed.
        return ['database'];
    }

    /**
     * Store the notification data in the database.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Schedule Added',
            'message' => 'A new schedule has been added for your subject: ' . ($this->schedule->subject_name ?? 'Unknown'),
            'schedule_id' => $this->schedule->id ?? null,
            'teacher_id' => $this->schedule->teacher_id ?? null,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Optional: Array form (if you need to return JSON or broadcast)
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Schedule Added',
            'message' => 'A new schedule has been added for your subject: ' . ($this->schedule->subject_name ?? 'Unknown'),
            'schedule_id' => $this->schedule->id ?? null,
        ];
    }
}
