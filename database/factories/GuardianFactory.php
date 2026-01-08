<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuardianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->guardian(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'relationship' => fake()->randomElement(['parent', 'mother', 'father', 'guardian', 'aunt', 'uncle']),
            'is_primary' => fake()->boolean(70),
            'alert_preferences' => [
                'email' => true,
                'sms' => fake()->boolean(50),
                'absence_threshold' => 3,
                'consecutive_absence' => 2,
            ],
        ];
    }
}
