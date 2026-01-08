<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->teacher(),
            'employee_id' => 'EMP-'.strtoupper(fake()->unique()->bothify('????####')),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'department' => fake()->randomElement(['Mathematics', 'Science', 'English', 'History', 'Physical Education', 'Art', 'Music']),
            'status' => 'active',
        ];
    }
}
