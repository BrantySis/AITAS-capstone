<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterTeacherController extends Controller
{
    // Show the registration page
    public function create()
    {
        return view('register', [
            'fastapiUrl'   => env('FASTAPI_URL', 'http://127.0.0.1:8001'),
            'laravelApiUrl'=> env('LARAVEL_URL', 'http://127.0.0.1:8000/api'),
        ]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:8|confirmed',
            'face_registered'       => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'face_registered' => $request->face_registered ?? 0,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user_id' => $user->id,
        ]);
    }
}
