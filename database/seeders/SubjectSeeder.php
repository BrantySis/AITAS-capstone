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
            // College of Computer Studies (5 subjects)
            [
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Programming',
                'department' => 'College of Computer Studies',
                'course_year' => '1st',
                'semester' => '1st',
                'school_year' => '2025-2026',
                'units' => 3,
                'description' => 'Basics of programming using Python.',
            ],
            [
                'subject_code' => 'CS102',
                'subject_name' => 'Computer Systems',
                'department' => 'College of Computer Studies',
                'course_year' => '1st',
                'semester' => '2nd',
                'school_year' => '2025-2026',
                'units' => 3,
                'description' => 'Introduction to computer hardware and software.',
            ],
            [
                'subject_code' => 'CS201',
                'subject_name' => 'Data Structures',
                'department' => 'College of Computer Studies',
                'course_year' => '2nd',
                'semester' => '1st',
                'school_year' => '2025-2026',
                'units' => 3,
            ],
            [
                'subject_code' => 'CS202',
                'subject_name' => 'Database Systems',
                'department' => 'College of Computer Studies',
                'course_year' => '2nd',
                'semester' => '2nd',
                'school_year' => '2025-2026',
                'units' => 3,
            ],
            [
                'subject_code' => 'CS301',
                'subject_name' => 'Web Development',
                'department' => 'College of Computer Studies',
                'course_year' => '3rd',
                'semester' => '1st',
                'school_year' => '2025-2026',
                'units' => 3,
            ],

            // Nursing (5 subjects)
            [
                'subject_code' => 'NUR101',
                'subject_name' => 'Fundamentals of Nursing',
                'department' => 'Nursing',
                'course_year' => '1st',
                'semester' => '1st',
                'school_year' => '2025-2026',
                'units' => 3,
            ],
            [
                'subject_code' => 'NUR102',
                'subject_name' => 'Anatomy and Physiology',
                'department' => 'Nursing',
                'course_year' => '1st',
                'semester' => '2nd',
                'school_year' => '2025-2026',
                'units' => 4,
            ],
            [
                'subject_code' => 'NUR201',
                'subject_name' => 'Microbiology for Nursing',
                'department' => 'Nursing',
                'course_year' => '2nd',
                'semester' => '1st',
                'school_year' => '2025-2026',
                'units' => 3,
            ],
            [
                'subject_code' => 'NUR202',
                'subject_name' => 'Pharmacology',
                'department' => 'Nursing',
                'course_year' => '2nd',
                'semester' => '2nd',
                'school_year' => '2025-2026',
                'units' => 4,
            ],
            [
                'subject_code' => 'NUR301',
                'subject_name' => 'Community Health Nursing',
                'department' => 'Nursing',
                'course_year' => '3rd',
                'semester' => '1st',
                'school_year' => '2025-2026',
                'units' => 3,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['subject_code' => $subject['subject_code']], // unique identifier
                $subject
            );
        }
    }
}
