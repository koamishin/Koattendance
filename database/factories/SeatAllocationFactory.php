<?php

namespace Database\Factories;

use App\Models\SeatPlan;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeatAllocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'seat_plan_id' => SeatPlan::factory(),
            'student_id' => Student::factory(),
            'row' => fake()->numberBetween(1, 8),
            'column' => fake()->numberBetween(1, 6),
            'seat_label' => fake()->regexify('[A-Z][0-9]{1,2}'),
        ];
    }
}
