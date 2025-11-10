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
    /**
     * Display a listing of Teachers and Deans.
     */
    public function index(Request $request)
    {
        $teacherRoleId = Role::where('name', 'teacher')->value('id');
        $deanRoleId = Role::where('name', 'dean')->value('id');

        $search = $request->input('search');

        $users = User::whereIn('role_id', [$teacherRoleId, $deanRoleId])
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%")
                             ->orWhere('faculty_number', 'like', "%{$search}%");
                });
            })
            ->with('role')
            ->orderBy('name')
            ->get();

        return view('admin.admin-teachers', compact('users'));
    }

    /**
     * Store a newly created Teacher or Dean.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'faculty_number'  => 'required|string|max:8|unique:users,faculty_number',
            'password'        => 'required|string|min:6',
            'role'            => 'required|in:teacher,dean',
        ]);

        $role = Role::where('name', $request->role)->firstOrFail();

        User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'faculty_number'  => $request->faculty_number,
            'password'        => Hash::make($request->password),
            'role_id'         => $role->id,
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', ucfirst($request->role) . ' created successfully.');
    }

    /**
     * Update an existing Teacher or Dean.
     */
    public function update(Request $request, User $teacher)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $teacher->id,
            'faculty_number'  => 'required|string|max:8|unique:users,faculty_number,' . $teacher->id,
            'password'        => 'nullable|string|min:6',
        ]);

        $updateData = [
            'name'           => $request->name,
            'email'          => $request->email,
            'faculty_number' => $request->faculty_number,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $teacher->update($updateData);

        return redirect()->route('admin.teachers.index')
            ->with('success', ucfirst($teacher->role->name) . ' updated successfully.');
    }

    /**
     * Remove the specified Teacher or Dean.
     */
    public function destroy(User $teacher)
    {
        $roleName = ucfirst($teacher->role->name);
        $teacher->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', $roleName . ' deleted successfully.');
    }

    /**
     * Import Teachers from an Excel file.
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048',
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
