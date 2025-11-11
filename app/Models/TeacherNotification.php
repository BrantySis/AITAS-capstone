<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherNotification extends Model
{
     protected $fillable = [
        'user_id', // ← this must exist in the DB
        'type',
        'title',
        'message',
        'created_by',
        'read_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
