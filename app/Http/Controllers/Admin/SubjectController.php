<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Imports\SubjectsImport;
use Maatwebsite\Excel\Facades\Excel;

class SubjectController extends Controller
{
    /**
     * Display a listing of the subjects (grouped by department).
     */
    public function index(Request $request)
    {
        $selectedDepartment = $request->input('department');
        $selectedYear = $request->input('school_year');
        $selectedSemester = $request->input('semester');
        $selectedCourseYear = $request->input('course_year');

        $departments = Subject::select('department')->whereNotNull('department')->distinct()->pluck('department');
        $schoolYears = Subject::select('school_year')->whereNotNull('school_year')->distinct()->pluck('school_year');
        $semesters = Subject::select('semester')->whereNotNull('semester')->distinct()->pluck('semester');
        $courseYears = Subject::select('course_year')->whereNotNull('course_year')->distinct()->pluck('course_year');

        $search = $request->input('search');

        $subjects = Subject::query()
            ->when($selectedDepartment, fn($q) => $q->where('department', $selectedDepartment))
            ->when($selectedYear, fn($q) => $q->where('school_year', $selectedYear))
            ->when($selectedSemester, fn($q) => $q->where('semester', $selectedSemester))
            ->when($selectedCourseYear, fn($q) => $q->where('course_year', $selectedCourseYear))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('subject_name', 'like', "%{$search}%")
                        ->orWhere('subject_code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
    ->orderBy('department')
    ->get()
    ->groupBy('department');
        return view('admin.admin-subjects', [
            'subjects' => $subjects,
            'departments' => $departments,
            'schoolYears' => $schoolYears,
            'semesters' => $semesters,
            'courseYears' => $courseYears,
            'selectedDepartment' => $selectedDepartment,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
            'selectedCourseYear' => $selectedCourseYear,
            'search' => $search,
            'page_mode' => 'index'
        ]);
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        $schoolYears = $this->generateSchoolYears();
        $semesters = ['1st Semester', '2nd Semester', 'Summer'];
        $courseYears = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

        return view('admin.admin-subjects', [
            'page_mode' => 'create',
            'schoolYears' => $schoolYears,
            'semesters' => $semesters,
            'courseYears' => $courseYears,
        ]);
    }

    /**
     * Store a newly created subject.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_code' => 'required|string|unique:subjects,subject_code',
            'subject_name' => 'required|string',
            'units' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'department' => 'nullable|string|max:255',
            'course_year' => 'required|string',
            'semester' => 'required|string',
            'school_year' => 'required|string',
        ]);

        Subject::create($request->only([
            'subject_code',
            'subject_name',
            'units',
            'description',
            'department',
            'course_year',
            'semester',
            'school_year',
        ]));

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Edit an existing subject.
     */
    public function edit(Subject $subject)
    {
        $schoolYears = $this->generateSchoolYears();
        $semesters = ['1st Semester', '2nd Semester', 'Summer'];
        $courseYears = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

        return view('admin.subjects.edit', compact('subject', 'schoolYears', 'semesters', 'courseYears'));
    }

    /**
     * Update subject.
     */
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'subject_code' => 'required|string|unique:subjects,subject_code,' . $subject->id,
            'subject_name' => 'required|string',
            'units' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'department' => 'nullable|string|max:255',
            'course_year' => 'required|string',
            'semester' => 'required|string',
            'school_year' => 'required|string',
        ]);

        $subject->update($request->only([
            'subject_code',
            'subject_name',
            'units',
            'description',
            'department',
            'course_year',
            'semester',
            'school_year',
        ]));

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Delete a subject.
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    /**
     * Import page.
     */
    public function import()
    {
        return view('admin.admin-subjects', [
            'page_mode' => 'import'
        ]);
    }

    /**
     * Process import file.
     */
    public function processImport(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv,xls|max:2048']);

        try {
            $import = new SubjectsImport;
            Excel::import($import, $request->file('file'));
            return redirect()->route('admin.subjects.index')
                ->with('success', 'Subjects imported successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="subjects_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['subject_code', 'subject_name', 'units', 'description', 'department', 'course_year', 'semester', 'school_year']);
            fputcsv($file, ['CS101', 'Intro to Programming', '3', 'Basic programming', 'Computer Studies', '1st Year', '1st Semester', '2025-2026']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper: Generate next 5 school years dynamically.
     */
    private function generateSchoolYears()
    {
        $years = [];
        $current = date('Y');
        for ($i = 0; $i < 5; $i++) {
            $start = $current + $i;
            $end = $start + 1;
            $years[] = "{$start}-{$end}";
        }
        return $years;
    }
}
