<?php

namespace Database\Factories;

use App\Models\GradeScale;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeScaleItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'grade_scale_id' => GradeScale::factory(),
            'letter_grade' => fake()->randomElement(['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F']),
            'min_percentage' => fake()->numberBetween(0, 95),
            'max_percentage' => fake()->numberBetween(60, 100),
            'gpa_points' => fake()->randomElement([4.0, 3.7, 3.3, 3.0, 2.7, 2.3, 2.0, 1.7, 1.3, 1.0, 0.0]),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
