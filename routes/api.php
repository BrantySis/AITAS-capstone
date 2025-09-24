<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterTeacherController;
use App\Http\Controllers\FaceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/teacher/register', [RegisterTeacherController::class, 'store']);
    Route::post('/store-embedding', [FaceController::class, 'store']);
    Route::post('/recognize-embedding', [FaceController::class, 'recognize']);

