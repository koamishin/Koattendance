<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    public function definition(): array
    {
        $academicYear = fake()->randomElement(['2024-2025', '2025-2026', '2026-2027']);
        $semester = fake()->randomElement(['first', 'second']);

        return [
            'name' => fake()->randomElement(['A', 'B', 'C', 'D']).'-'.fake()->numberBetween(1, 5),
            'grade_level' => fake()->numberBetween(1, 12),
            'academic_year' => $academicYear,
            'semester' => $semester,
            'advisor_id' => Teacher::factory(),
            'max_students' => fake()->numberBetween(20, 35),
            'schedule_template' => [
                'monday' => ['period1', 'period2'],
                'tuesday' => ['period3', 'period4'],
                'wednesday' => ['period5', 'period6'],
                'thursday' => ['period7', 'period8'],
                'friday' => ['period9', 'period10'],
            ],
        ];
    }
}
