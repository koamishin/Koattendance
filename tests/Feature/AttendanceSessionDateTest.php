<?php

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('teacher can create a session for a past date', function () {
    Notification::fake();

    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);

    $pastDate = now()->subDays(3)->toDateString();

    $response = $this->actingAs($teacherUser)->postJson(route('api.sessions.start'), [
        'subject_id' => $subject->id,
        'scheduled_date' => $pastDate,
        'late_threshold_minutes' => 10,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('session.scheduled_date', $pastDate)
        ->assertJsonPath('session.status', 'completed')
        ->assertJsonPath('is_new', true);
});

test('attendance api shows records for selected session date', function () {
    Notification::fake();

    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);

    $student = Student::factory()->create();
    $subject->students()->attach($student->id);

    $futureDate = now()->addDay()->toDateString();

    $session = ClassSession::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'course_id' => Course::factory(),
        'section_id' => null,
        'scheduled_date' => $futureDate,
        'status' => 'in_progress',
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
        'late_threshold_minutes' => 15,
    ]);

    AttendanceRecord::create([
        'session_id' => $session->id,
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'present',
        'timestamp' => now(),
        'recorded_by' => $teacherUser->id,
    ]);

    $response = $this->actingAs($teacherUser)->getJson('/api/attendance?subjectId='.$subject->id.'&date='.$futureDate);

    $response->assertSuccessful()
        ->assertJsonPath('selectedDate', $futureDate)
        ->assertJsonPath('session.id', $session->id)
        ->assertJsonFragment([
            'student_id' => $student->id,
            'status' => 'present',
        ]);
});

