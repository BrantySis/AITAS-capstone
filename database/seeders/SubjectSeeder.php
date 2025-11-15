<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            // Hospitality Management
            ['subject_code' => 'HM101', 'subject_name' => 'Introduction to Hospitality', 'department' => 'Hospitality Management', 'course_year' => '1', 'semester' => '1', 'school_year' => '2025-2026', 'units' => 3, 'description' => 'Basics of hospitality management.'],
            ['subject_code' => 'HM102', 'subject_name' => 'Food and Beverage Service', 'department' => 'Hospitality Management', 'course_year' => '1', 'semester' => '2', 'school_year' => '2025-2026', 'units' => 3],
            ['subject_code' => 'HM201', 'subject_name' => 'Hotel Operations', 'department' => 'Hospitality Management', 'course_year' => '2', 'semester' => '1', 'school_year' => '2025-2026', 'units' => 4],

            // Engineering
            ['subject_code' => 'ENG101', 'subject_name' => 'Engineering Mathematics', 'department' => 'Engineering', 'course_year' => '1', 'semester' => '1', 'school_year' => '2025-2026', 'units' => 4],
            ['subject_code' => 'ENG102', 'subject_name' => 'Physics for Engineers', 'department' => 'Engineering', 'course_year' => '1', 'semester' => '2', 'school_year' => '2025-2026', 'units' => 4],

            // College of Computer Studies
            ['subject_code' => 'CS101', 'subject_name' => 'Introduction to Programming', 'department' => 'College of Computer Studies', 'course_year' => '1', 'semester' => '1', 'school_year' => '2025-2026', 'units' => 3],
            ['subject_code' => 'CS102', 'subject_name' => 'Computer Systems', 'department' => 'College of Computer Studies', 'course_year' => '1', 'semester' => '2', 'school_year' => '2025-2026', 'units' => 3],

            // Marine Engineering
            ['subject_code' => 'ME101', 'subject_name' => 'Marine Machinery', 'department' => 'Marine Engineering', 'course_year' => '1', 'semester' => '1', 'school_year' => '2025-2026', 'units' => 4],
            ['subject_code' => 'ME102', 'subject_name' => 'Marine Electrical Systems', 'department' => 'Marine Engineering', 'course_year' => '1', 'semester' => '2', 'school_year' => '2025-2026', 'units' => 4],

            // Marine Transportation
            ['subject_code' => 'MT101', 'subject_name' => 'Navigation Basics', 'department' => 'Marine Transportation', 'course_year' => '1', 'semester' => '1', 'school_year' => '2025-2026', 'units' => 4],
            ['subject_code' => 'MT102', 'subject_name' => 'Ship Operations', 'department' => 'Marine Transportation', 'course_year' => '1', 'semester' => '2', 'school_year' => '2025-2026', 'units' => 4],

            // Nursing
            ['subject_code' => 'NUR101', 'subject_name' => 'Fundamentals of Nursing', 'department' => 'Nursing', 'course_year' => '1', 'semester' => '1', 'school_year' => '2025-2026', 'units' => 3],
            ['subject_code' => 'NUR102', 'subject_name' => 'Anatomy and Physiology', 'department' => 'Nursing', 'course_year' => '1', 'semester' => '2', 'school_year' => '2025-2026', 'units' => 4],

            // Add more for remaining departments as needed...
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['subject_code' => $subject['subject_code']], // unique identifier
                $subject
            );
        }
    }
}
