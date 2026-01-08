<?php

namespace Database\Factories;

use App\Models\Guardian;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'user_id' => User::factory()->student(),
            'student_id' => 'STU-'.strtoupper(fake()->unique()->bothify('????####')),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => fake()->optional()->middleName(),
            'birth_date' => fake()->dateBetween('-20 years', '-5 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'guardian_id' => Guardian::factory(),
            'current_grade_level' => fake()->numberBetween(1, 12),
            'section_id' => Section::factory(),
            'status' => 'active',
            'enrollment_date' => fake()->dateBetween('-3 years', 'now'),
            'qr_code_data' => fake()->uuid(),
            'qr_code_active' => true,
            'qr_code_regenerated_at' => now(),
        ];
    }
}
