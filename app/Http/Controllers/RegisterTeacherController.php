<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterTeacherController extends Controller
{
    public function create()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
    
        return view('admin.register-teacher');
    }

    public function store(Request $request)
    {

        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'teacher', // assign role here
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('dashboard')->with('success', 'Teacher registered successfully!');
    }
}

