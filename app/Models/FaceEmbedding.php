<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'embeddings'
    ];

    protected $casts = [
        'embeddings' => 'array'
    ];
}
