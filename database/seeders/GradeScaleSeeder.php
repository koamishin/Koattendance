<?php

namespace Database\Seeders;

use App\Models\GradeScale;
use Illuminate\Database\Seeder;

class GradeScaleSeeder extends Seeder
{
    public function run(): void
    {
        $gradeScale = GradeScale::create([
            'name' => 'Standard Grade Scale 2024-2025',
            'description' => 'Standard grading scale for academic year 2024-2025',
            'is_default' => true,
            'academic_year' => '2024-2025',
            'is_active' => true,
        ]);

        $gradeItems = [
            ['letter_grade' => 'A+', 'min_percentage' => 97, 'max_percentage' => 100, 'gpa_points' => 4.0, 'description' => 'Excellent'],
            ['letter_grade' => 'A', 'min_percentage' => 93, 'max_percentage' => 96, 'gpa_points' => 4.0, 'description' => 'Excellent'],
            ['letter_grade' => 'A-', 'min_percentage' => 90, 'max_percentage' => 92, 'gpa_points' => 3.7, 'description' => 'Very Good'],
            ['letter_grade' => 'B+', 'min_percentage' => 87, 'max_percentage' => 89, 'gpa_points' => 3.3, 'description' => 'Good'],
            ['letter_grade' => 'B', 'min_percentage' => 83, 'max_percentage' => 86, 'gpa_points' => 3.0, 'description' => 'Good'],
            ['letter_grade' => 'B-', 'min_percentage' => 80, 'max_percentage' => 82, 'gpa_points' => 2.7, 'description' => 'Satisfactory'],
            ['letter_grade' => 'C+', 'min_percentage' => 77, 'max_percentage' => 79, 'gpa_points' => 2.3, 'description' => 'Satisfactory'],
            ['letter_grade' => 'C', 'min_percentage' => 73, 'max_percentage' => 76, 'gpa_points' => 2.0, 'description' => 'Average'],
            ['letter_grade' => 'C-', 'min_percentage' => 70, 'max_percentage' => 72, 'gpa_points' => 1.7, 'description' => 'Below Average'],
            ['letter_grade' => 'D+', 'min_percentage' => 67, 'max_percentage' => 69, 'gpa_points' => 1.3, 'description' => 'Poor'],
            ['letter_grade' => 'D', 'min_percentage' => 63, 'max_percentage' => 66, 'gpa_points' => 1.0, 'description' => 'Poor'],
            ['letter_grade' => 'F', 'min_percentage' => 0, 'max_percentage' => 59, 'gpa_points' => 0.0, 'description' => 'Failing'],
        ];

        foreach ($gradeItems as $item) {
            $gradeScale->items()->create($item);
        }
    }
}
