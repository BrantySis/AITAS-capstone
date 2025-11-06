<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{

    protected $fillable = [
        'user_id',
        'room_id',
        'subject_id',
        'edp_code',
        'units',
        'type',
         'starts_at',
    'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
    

    
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
                    return $this->belongsTo(Subject::class);
                }

            public function attendances()
                {
                    return $this->hasMany(Attendance::class, 'schedule_id', 'id');
                }
}

