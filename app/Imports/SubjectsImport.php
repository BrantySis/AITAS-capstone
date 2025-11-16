<?php

namespace App\Imports;

use App\Models\Subject;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class SubjectsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable;

    private $failures = [];
    private $errors = [];

    public function model(array $row)
    {
        // Skip completely empty rows
        if (empty($row['subject_code']) && empty($row['subject_name'])) {
            return null;
        }

        return new Subject([
            'subject_code' => isset($row['subject_code']) ? (string) trim($row['subject_code']) : null,
            'subject_name' => isset($row['subject_name']) ? (string) trim($row['subject_name']) : null,
            'department' => isset($row['department']) ? (string) trim($row['department']) : null,
            'course_year' => isset($row['course_year']) ? (string) trim($row['course_year']) : null,
            'semester' => isset($row['semester']) ? (string) trim($row['semester']) : null,
            'school_year' => isset($row['school_year']) ? (string) trim($row['school_year']) : null,
            'units' => isset($row['units']) ? (int) $row['units'] : 3,
            'description' => isset($row['description']) ? (string) trim($row['description']) : null,
            'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : 1,
        ]);
    }

    public function rules(): array
    {
        return [
            // Relax string requirement and remove strict string validation
            'subject_code' => 'required|max:255|unique:subjects,subject_code',
            'subject_name' => 'required|max:255',
            'department' => 'nullable|max:255',
            'course_year' => 'nullable|max:255',
            'semester' => 'nullable|max:255',
            'school_year' => 'nullable|max:255',
            'units' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|max:1000',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'subject_code.required' => 'Subject code is required',
            'subject_code.unique' => 'Subject code already exists',
            'subject_name.required' => 'Subject name is required',
            'units.integer' => 'Units must be a number',
            'units.min' => 'Units must be at least 1',
            'is_active.boolean' => 'is_active must be 0 or 1',
        ];
    }

    public function onError(Throwable $error)
    {
        $this->errors[] = $error->getMessage();
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = $failures;
    }

    public function getFailures()
    {
        return $this->failures;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
