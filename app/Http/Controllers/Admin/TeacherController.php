<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Imports\TeachersImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AdminNotification;

class TeacherController extends Controller
{
    /**
     * Display a listing of Teachers and Deans.
     */
    public function index(Request $request)
    {
        $teacherRoleId = Role::where('name', 'teacher')->value('id');
        $deanRoleId    = Role::where('name', 'dean')->value('id');

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
            'department'      => 'required|string|max:255',
            'role'            => 'required|in:teacher,dean',
        ]);

        $role = Role::where('name', $request->role)->firstOrFail();

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'faculty_number'  => $request->faculty_number,
            'password'        => Hash::make($request->password),
            'department'      => $request->department,
            'role_id'         => $role->id,
        ]);

        try {
            AdminNotification::create([
                'type'       => 'teacher',
                'title'      => ucfirst($request->role) . ' Added',
                'message'    => ucfirst($request->role) . " {$user->name} has been added.",
                'created_by' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminNotification failed: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', ucfirst($request->role) . ' created successfully.');
    }

    /**
     * Update an existing Teacher or Dean.
     */
    public function update(Request $request, User $teacher)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $teacher->id,
            'faculty_number' => 'required|string|max:8|unique:users,faculty_number,' . $teacher->id,
            'department'     => 'required|string|max:255',
            'password'       => 'nullable|string|min:6',
        ]);

        $updateData = [
            'name'           => $request->name,
            'email'          => $request->email,
            'faculty_number' => $request->faculty_number,
            'department'     => $request->department,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $teacher->update($updateData);

        AdminNotification::create([
            'type'       => 'teacher',
            'title'      => ucfirst($teacher->role->name) . ' Updated',
            'message'    => ucfirst($teacher->role->name) . " {$teacher->name} has been updated.",
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', ucfirst($teacher->role->name) . ' updated successfully.');
    }

    /**
     * Remove the specified Teacher or Dean.
     */
    public function destroy(User $teacher)
    {
        $roleName = ucfirst($teacher->role->name);
        $teacherName = $teacher->name;

        $teacher->delete();

        AdminNotification::create([
            'type'       => 'teacher',
            'title'      => $roleName . ' Deleted',
            'message'    => "$roleName {$teacherName} has been deleted.",
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', $roleName . ' deleted successfully.');
    }

    /**
     * Import Teachers from a file.
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048',
        ]);

        try {
            $import = new TeachersImport;
            Excel::import($import, $request->file('file'));

            if ($import->getRowCount() === 0) {
                return redirect()->route('admin.teachers.index')
                    ->with('error', 'The file contains no data rows to import.');
            }

            return redirect()->route('admin.teachers.index')
                ->with('success', 'Users imported successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
