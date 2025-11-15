<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'room_code',
        'building_name',
        'latitude',
        'longitude',
        'is_active',
    ];

    // Ensure latitude/longitude are always floats
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * A room can have many schedules.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
