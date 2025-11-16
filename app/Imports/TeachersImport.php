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
    protected int $rowCount = 0;

    /**
     * Cast any value to string and trim spaces
     */
    protected function asString($value)
    {
        if ($value === null) return null;
        return trim((string)$value);
    }

    public function model(array $row)
    {
        $name = $this->asString($row['name'] ?? null);
        $email = $this->asString($row['email'] ?? null);
        $facultyNumber = $this->asString($row['faculty_number'] ?? null);
        $password = $this->asString($row['password'] ?? 'password123'); // default password

        if (!$name || !$email || !$facultyNumber) {
            return null; // skip empty rows
        }

        $teacher = User::create([
            'name' => $name,
            'email' => $email,
            'faculty_number' => $facultyNumber,
            'password' => Hash::make($password),
        ]);

        $teacher->role()->associate(Role::where('name', 'teacher')->first());
        $teacher->save();

        $this->rowCount++;

        return $teacher;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'faculty_number' => 'required|string|max:10|unique:users,faculty_number',
            'password' => 'nullable', // <- allow empty and any type
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
    