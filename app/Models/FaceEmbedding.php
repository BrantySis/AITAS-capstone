<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceEmbedding extends Model
{
    use HasFactory;

    protected $table = 'face_embeddings';

    protected $fillable = [
        'user_id',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    // Relationship back to user (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
