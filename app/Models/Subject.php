<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'subject_code',
        'subject_name', 
        'units',
        'description',
        'department',
        'course_year',
        'semester',
        'school_year',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}