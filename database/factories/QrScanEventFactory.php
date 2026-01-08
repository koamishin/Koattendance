<?php

namespace Database\Factories;

use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class QrScanEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => ClassSession::factory(),
            'teacher_id' => Teacher::factory(),
            'student_id' => Student::factory(),
            'scanned_at' => now(),
            'device_info' => [
                'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                'os' => fake()->randomElement(['Windows', 'macOS', 'Android', 'iOS']),
            ],
            'location' => fake()->optional()->latitude() ? [
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
            ] : null,
            'status' => fake()->randomElement(['success', 'failed', 'duplicate', 'invalid']),
            'error_message' => fake()->optional()->sentence(),
        ];
    }
}
