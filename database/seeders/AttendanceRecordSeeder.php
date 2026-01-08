<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Student;
use Illuminate\Database\Seeder;

class AttendanceRecordSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = ClassSession::all();
        $students = Student::all();

        if ($sessions->isEmpty() || $students->isEmpty()) {
            return;
        }

        foreach ($sessions as $session) {
            foreach ($students->random(min(5, $students->count())) as $student) {
                AttendanceRecord::create([
                    'session_id' => $session->id,
                    'student_id' => $student->id,
                    'status' => fake()->randomElement(['present', 'absent', 'late', 'excused']),
                    'timestamp' => now(),
                    'recorded_by' => $session->teacher->user->id ?? null,
                    'scan_event_id' => null,
                    'device_info' => null,
                    'notes' => fake()->optional()->sentence(),
                ]);
            }
        }
    }
}
