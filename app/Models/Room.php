<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['room_code', 'building_name', 'latitude', 'longitude'];
}
