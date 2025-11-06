<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Imports\TeachersImport;
use Maatwebsite\Excel\Facades\Excel;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teacherRoleId = Role::where('name', 'teacher')->first()->id;
        $deanRoleId = Role::where('name', 'dean')->first()->id;

        $search = $request->input('search');

        $users = User::whereIn('role_id', [$teacherRoleId, $deanRoleId])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('faculty_number', 'like', "%{$search}%");
            })
            ->get();

        return view('admin.admin-teachers', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'faculty_number' => 'required|string|max:8|unique:users,faculty_number',
            'password' => 'required|string|min:6',
            'role' => 'required|in:teacher,dean',
        ]);

        $role = Role::where('name', $request->role)->first();

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'faculty_number' => $request->faculty_number,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', ucfirst($request->role) . ' created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'faculty_number' => 'required|string|max:8|unique:users,faculty_number,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'faculty_number' => $request->faculty_number,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.teachers.index')
            ->with('success', ucfirst($user->role->name) . ' updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', ucfirst($user->role->name) . ' deleted successfully.');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048'
        ]);

        try {
            Excel::import(new TeachersImport, $request->file('file'));

            return redirect()->route('admin.teachers.index')
                ->with('success', 'Users imported successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
