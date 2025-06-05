<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->get();
        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'faculty_number' => 'required|string|max:8|unique:users,faculty_number',
            'password' => 'required|string|min:6',
        ]);

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'faculty_number' => $request->faculty_number,
            'password' => Hash::make($request->password),
        ]);

        $teacher->role()->associate(Role::where('name', 'teacher')->first());
        $teacher->save();

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher created.');
    }

    public function edit(User $teacher)
    {
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $teacher->id,
            'faculty_number' => 'required|string|max:7|unique:users,faculty_number,' . $teacher->id,
        ]);

        $teacher->update([
            'name' => $request->name,
            'email' => $request->email,
            'faculty_number' => $request->faculty_number,
        ]);

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher updated.');
    }

    public function destroy(User $teacher)
    {
        $teacher->delete();
        return redirect()->route('admin.teachers.index')->with('success', 'Teacher deleted.');
    }
}
