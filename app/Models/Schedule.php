<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{

    protected $fillable = [
        'user_id',
        'room_id',
        'edp_code',
        'subject',
        'units',
        'type',
        'day',
        'time_start',
        'time_end',
    ];
    

    
    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function room()
{
    return $this->belongsTo(Room::class);
}
}
