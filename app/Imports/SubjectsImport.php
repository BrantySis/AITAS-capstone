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
        // Skip empty rows
        if (empty($row['subject_code']) && empty($row['subject_name'])) {
            return null;
        }

        return new Subject([
            'subject_code' => trim($row['subject_code']),
            'subject_name' => trim($row['subject_name']),
            'units' => (int) $row['units'],
            'description' => isset($row['description']) ? trim($row['description']) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'subject_code' => 'required|string|max:20|unique:subjects,subject_code',
            'subject_name' => 'required|string|max:255',
            'units' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'subject_code.required' => 'Subject code is required',
            'subject_code.unique' => 'Subject code already exists',
            'subject_name.required' => 'Subject name is required',
            'units.required' => 'Units is required',
            'units.integer' => 'Units must be a number',
            'units.min' => 'Units must be at least 1',
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
