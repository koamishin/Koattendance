<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassSessionFactory extends Factory
{
    public function definition(): array
    {
        $scheduledDate = fake()->dateTimeBetween('-1 month', '+2 months');
        $startTime = fake()->time('H:i', '08:00');
        $endTime = fake()->dateTimeBetween($startTime, '+60 minutes')->format('H:i');

        return [
            'course_id' => Course::factory(),
            'section_id' => Section::factory(),
            'teacher_id' => Teacher::factory(),
            'room' => 'Room '.fake()->randomElement(['101', '102', '103', '201', '202', '301']),
            'scheduled_date' => $scheduledDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => fake()->randomElement(['scheduled', 'in_progress', 'completed', 'cancelled']),
            'attendance_mode' => 'qr_code',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
