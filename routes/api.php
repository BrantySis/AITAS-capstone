<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterTeacherController;
use App\Http\Controllers\FaceController;
use App\Models\FaceEmbedding;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/teacher/register', [RegisterTeacherController::class, 'store']);
    Route::post('/store-embedding', [FaceController::class, 'store']);
    Route::post('/recognize-embedding', [FaceController::class, 'recognize']);

    Route::get('/get-embeddings', function () {
    $faces = FaceEmbedding::all(['user_id', 'embedding']);
    return response()->json([
        'embeddings' => $faces
    ]);
});

