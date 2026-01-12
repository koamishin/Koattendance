<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GradeScaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Standard Grade Scale '.fake()->year(),
            'description' => 'Standard grading scale for academic year '.fake()->year(),
            'is_default' => fake()->boolean(20),
            'academic_year' => fake()->randomElement(['2024-2025', '2025-2026']),
            'is_active' => true,
        ];
    }
}
