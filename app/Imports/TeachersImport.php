<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TeachersImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $teacher = User::create([
            'name' => $row['name'],
            'email' => $row['email'],
            'faculty_number' => $row['faculty_number'],
            'password' => Hash::make($row['password'] ?? 'password123'),
        ]);

        $teacher->role()->associate(Role::where('name', 'teacher')->first());
        $teacher->save();

        return $teacher;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'faculty_number' => 'required|string|max:10|unique:users,faculty_number',
        ];
    }
}