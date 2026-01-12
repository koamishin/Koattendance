<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = Teacher::all();

        if ($teachers->isEmpty()) {
            return;
        }

        $sections = [
            ['name' => 'Section A', 'grade_level' => 1, 'academic_year' => '2024-2025', 'semester' => 'first', 'max_students' => 30],
            ['name' => 'Section B', 'grade_level' => 1, 'academic_year' => '2024-2025', 'semester' => 'first', 'max_students' => 28],
            ['name' => 'Section A', 'grade_level' => 2, 'academic_year' => '2024-2025', 'semester' => 'first', 'max_students' => 30],
            ['name' => 'Section B', 'grade_level' => 2, 'academic_year' => '2024-2025', 'semester' => 'first', 'max_students' => 25],
            ['name' => 'Section A', 'grade_level' => 3, 'academic_year' => '2024-2025', 'semester' => 'first', 'max_students' => 30],
        ];

        foreach ($sections as $index => $section) {
            $section['advisor_id'] = $teachers->random()->id;
            $section['schedule_template'] = [
                'monday' => ['period1' => 'MATH101', 'period2' => 'ENG101'],
                'tuesday' => ['period1' => 'SCI101', 'period2' => 'HIST101'],
                'wednesday' => ['period1' => 'MATH101', 'period2' => 'ENG101'],
                'thursday' => ['period1' => 'SCI101', 'period2' => 'PE101'],
                'friday' => ['period1' => 'ART101', 'period2' => 'CS101'],
            ];
            Section::create($section);
        }
    }
}
