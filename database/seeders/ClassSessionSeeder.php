<?php

namespace Database\Seeders;

use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassSessionSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();
        $sections = Section::all();
        $teachers = Teacher::all();

        if ($courses->isEmpty() || $sections->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $sessions = [
            [
                'course_id' => $courses->where('code', 'MATH101')->first()->id,
                'section_id' => $sections->where('grade_level', 1)->first()->id,
                'teacher_id' => $teachers->random()->id,
                'room' => 'Room 101',
                'scheduled_date' => now()->addDays(1),
                'start_time' => '08:00',
                'end_time' => '09:00',
                'status' => 'scheduled',
                'attendance_mode' => 'qr_code',
            ],
            [
                'course_id' => $courses->where('code', 'ENG101')->first()->id,
                'section_id' => $sections->where('grade_level', 1)->first()->id,
                'teacher_id' => $teachers->random()->id,
                'room' => 'Room 102',
                'scheduled_date' => now()->addDays(1),
                'start_time' => '09:30',
                'end_time' => '10:30',
                'status' => 'scheduled',
                'attendance_mode' => 'qr_code',
            ],
            [
                'course_id' => $courses->where('code', 'SCI101')->first()->id,
                'section_id' => $sections->where('grade_level', 1)->first()->id,
                'teacher_id' => $teachers->random()->id,
                'room' => 'Lab 201',
                'scheduled_date' => now()->addDays(2),
                'start_time' => '08:00',
                'end_time' => '10:00',
                'status' => 'scheduled',
                'attendance_mode' => 'qr_code',
            ],
            [
                'course_id' => $courses->where('code', 'MATH201')->first()->id,
                'section_id' => $sections->where('grade_level', 2)->first()->id,
                'teacher_id' => $teachers->random()->id,
                'room' => 'Room 201',
                'scheduled_date' => now()->addDays(1),
                'start_time' => '10:00',
                'end_time' => '11:30',
                'status' => 'scheduled',
                'attendance_mode' => 'qr_code',
            ],
            [
                'course_id' => $courses->where('code', 'CS101')->first()->id,
                'section_id' => $sections->random()->id,
                'teacher_id' => $teachers->random()->id,
                'room' => 'Computer Lab 301',
                'scheduled_date' => now()->addDays(3),
                'start_time' => '14:00',
                'end_time' => '15:30',
                'status' => 'scheduled',
                'attendance_mode' => 'qr_code',
            ],
        ];

        foreach ($sessions as $session) {
            ClassSession::create($session);
        }
    }
}
