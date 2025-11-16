<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'schedule_id',
        'latitude',
        'longitude',
        'time_in',
        'time_out',
        'status',
        'room_id',    // Added for room relationship
        'subject_id', // Added for subject relationship
    ];

    // Attendance belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Attendance belongs to a Schedule
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    // Attendance belongs to a Room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Attendance belongs to a Subject
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
