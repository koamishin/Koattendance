<?php

namespace Database\Factories;

use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subject = Subject::factory();
        $teacher = Teacher::factory();

        return [
            'session_id' => ClassSession::factory()->state(function () use ($subject, $teacher) {
                return [
                    'teacher_id' => $teacher,
                    'subject_id' => $subject,
                    'course_id' => Course::factory(),
                    'section_id' => null,
                    'room' => 'Default',
                    'scheduled_date' => now()->toDateString(),
                    'start_time' => now()->format('H:i:s'),
                    'end_time' => now()->addHour()->format('H:i:s'),
                    'status' => 'in_progress',
                    'attendance_mode' => 'qr_scan',
                    'late_threshold_minutes' => 15,
                ];
            }),
            'student_id' => Student::factory(),
            'subject_id' => $subject,
            'status' => fake()->randomElement(['present', 'absent', 'late']),
            'timestamp' => fake()->dateTimeBetween('-1 month', 'now'),
            'recorded_by' => User::factory(),
            'device_info' => ['source' => 'factory'],
        ];
    }
}
