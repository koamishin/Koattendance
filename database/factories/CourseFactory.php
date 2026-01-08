<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('???##')),
            'name' => fake()->randomElement([
                'Mathematics', 'English Language Arts', 'Science', 'History',
                'Physical Education', 'Art', 'Music', 'Computer Science',
                'Biology', 'Chemistry', 'Physics', 'Geography',
            ]),
            'description' => fake()->sentence(),
            'grade_level' => fake()->numberBetween(1, 12),
            'credits' => fake()->numberBetween(1, 5),
            'type' => fake()->randomElement(['core', 'elective', 'required', 'advanced']),
            'is_active' => true,
        ];
    }
}
