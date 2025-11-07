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
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'day_of_week' => 'array',
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
}
