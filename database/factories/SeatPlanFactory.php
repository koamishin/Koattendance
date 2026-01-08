<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeatPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'name' => 'Seat Plan '.fake()->randomElement(['A', 'B', 'C']),
            'layout' => [
                'rows' => fake()->numberBetween(4, 8),
                'columns' => fake()->numberBetween(4, 6),
            ],
        ];
    }
}
