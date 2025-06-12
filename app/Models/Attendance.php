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
        'time_in', // <-- Add this
        'time_out', // <-- Add if you're using it later
        // Add any other fields that need to be mass assigned
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
