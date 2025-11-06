<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterTeacherController;
use App\Http\Controllers\FaceController;
use App\Models\FaceEmbedding;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This file defines the API routes that connect your Laravel backend
| with the FastAPI face recognition service and your frontend.
|
*/

// --------------------------------------------------
// AUTH TEST
// --------------------------------------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --------------------------------------------------
// TEACHER REGISTRATION FLOW
// --------------------------------------------------

// STEP 1: Create temporary user entry (before face scan)
Route::post('/teacher/register-temp', [RegisterTeacherController::class, 'storeTemp']);

// STEP 2: Final registration — receives avg_embedding from FastAPI & saves user + face data
Route::post('/teacher/register', [RegisterTeacherController::class, 'store']);

// STEP 3: Cleanup temp user if something fails
Route::post('/teacher/cleanup-temp/{user}', [RegisterTeacherController::class, 'cleanupTemp']);

Route::post('/teacher/check-email', function (Request $request) {
    $exists = \App\Models\User::where('email', $request->email)->exists();
    return response()->json(['exists' => $exists]);
});

// --------------------------------------------------
// FACE RECOGNITION (FastAPI uses this to match faces)
// --------------------------------------------------

// Laravel provides stored embeddings to FastAPI
Route::get('/get-embeddings', function () {
    $faces = FaceEmbedding::select('user_id', 'embedding')->get();
    return response()->json(['embeddings' => $faces]);
});

// (Optional) FaceController routes — if you want extra API control
Route::post('/recognize-embedding', [FaceController::class, 'recognize']);
