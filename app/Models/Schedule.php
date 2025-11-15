<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'room_id',
        'subject_id',
        'edp_code',
        'units',
        'type',
        'starts_at',
        'ends_at',
        'day_of_week',
        'school_year',
        'semester',
        'start_date',
        'end_date',
        'room_lat',
        'room_lng', // Added
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'day_of_week' => 'array',
        'room_lat' => 'float',
        'room_lng' => 'float',
    ];

    /**
     * =====================
     * 🔗 RELATIONSHIPS
     * =====================
     */

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'schedule_id', 'id');
    }

    /**
     * =====================
     * 📅 CHECK IF SCHEDULE IS FOR TODAY
     * =====================
     */
    public function isToday()
    {
        $today = now()->format('D'); // Mon, Tue, Wed...

        $map = [
            'MWF' => ['Mon', 'Wed', 'Fri'],
            'TTH' => ['Tue', 'Thu'],
            'Sat' => ['Sat'],
            'Sun' => ['Sun'],
        ];

        // If the format is matched (string only, not array)
        if (isset($map[$this->day_of_week])) {
            return in_array($today, $map[$this->day_of_week]);
        }

        return false;
    }

    /**
     * =====================
     * 🧭 AUTO SET ROOM COORDINATES
     * =====================
     */
    protected static function booted()
    {
        static::saving(function ($schedule) {
            if ($schedule->room_id) {
                $room = $schedule->room()->first();
                if ($room) {
                    $schedule->room_lat = $room->latitude ?? 0.0;
                    $schedule->room_lng = $room->longitude ?? 0.0;
                }
            }
        });
    }
}
