<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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
        'room_lng',
    ];

    protected $casts = [
        'starts_at'   => 'string', // TIME column stored as H:i:s
        'ends_at'     => 'string', 
        'start_date'  => 'date',
        'end_date'    => 'date',
        'day_of_week' => 'array',  // store as array
        'room_lat'    => 'float',
        'room_lng'    => 'float',
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

    public function getDayOfWeekAttribute($value)
    {
        // Always return as array
        if (is_string($value)) {
            $clean = trim(str_replace(['[',']','"'], '', $value));
            return array_filter(array_map('trim', explode(',', $clean)));
        }

        return $value ?? [];
    }

    /**
     * =====================
     * 📅 CHECK IF SCHEDULE IS FOR TODAY
     * =====================
     */
    public function isToday(): bool
{
    $today = now()->format('l'); // e.g., "Monday"

    // If stored value is comma-separated days, explode into array
    $days = is_string($this->day_of_week) ? explode(',', $this->day_of_week) : (array)$this->day_of_week;

    // Remove extra spaces
    $days = array_map('trim', $days);

    return in_array($today, $days);
}

    /**
     * =====================
     * 🕒 ACCESSORS FOR TIME FORMATTING
     * =====================
     */
    public function getStartsAtAttribute($value)
    {
        return Carbon::createFromFormat('H:i:s', $value)->format('H:i'); 
    }

    public function getEndsAtAttribute($value)
    {
        return Carbon::createFromFormat('H:i:s', $value)->format('H:i'); 
    }

    /**
     * =====================
     * SHORT DAY STRING (e.g., MWF)
     * =====================
     */
   public function getDayShortAttribute(): string
{
    // Mapping of full day names to standard short forms
    $map = [
        'MONDAY'    => 'M',
        'TUESDAY'   => 'T',
        'WEDNESDAY' => 'W',
        'THURSDAY'  => 'TH',
        'FRIDAY'    => 'F',
        'SATURDAY'  => 'SAT',
        'SUNDAY'    => 'SUN',
    ];

    // Get raw input
    $raw = $this->day_of_week ?? [];

    // Convert string to array if needed
    if (!is_array($raw)) {
        $raw = explode(',', str_replace(['[',']','"'], '', $raw));
    }

    $output = [];
    foreach ($raw as $d) {
        $key = strtoupper(trim($d));

        if (isset($map[$key])) {
            $output[] = $map[$key];
        } else {
            // Handle short names like Mon, Tue
            $short = strtoupper(substr($key, 0, 3));
            $alias = [
                'MON' => 'M',
                'TUE' => 'T',
                'WED' => 'W',
                'THU' => 'TH',
                'FRI' => 'F',
                'SAT' => 'SAT',
                'SUN' => 'SUN',
            ];
            if (isset($alias[$short])) {
                $output[] = $alias[$short];
            }
        }
    }

    $dayString = implode('', $output);

    // Normalize common sequences
    if ($dayString === 'MTW') $dayString = 'MWF';
    if ($dayString === 'TTH') $dayString = 'TTH';

    return $dayString ?: '—';
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
