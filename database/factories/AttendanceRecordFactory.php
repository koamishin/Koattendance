<?php

namespace Database\Factories;

use App\Models\ClassSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => ClassSession::factory(),
            'student_id' => Student::factory(),
            'status' => fake()->randomElement(['present', 'absent', 'late', 'excused', 'early_departure']),
            'timestamp' => now(),
            'recorded_by' => User::factory(),
            'scan_event_id' => null,
            'device_info' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
