<?php

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\AttendanceStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('sends notification to guardian and student when attendance is recorded', function () {
    Notification::fake();

    $user = User::factory()->create(['role' => 'student']);
    $guardian = Guardian::factory()->create(['email' => 'guardian@example.com']);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'guardian_id' => $guardian->id,
    ]);

    $subject = Subject::factory()->create();
    $course = Course::factory()->create(['id' => $subject->id]); // Match ID for test if needed, or just use separate
    $session = ClassSession::factory()->create([
        'course_id' => $course->id,
    ]);

    $record = AttendanceRecord::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'session_id' => $session->id,
        'status' => 'present',
        'timestamp' => now(),
        'recorded_by' => User::factory()->create(['role' => 'teacher'])->id,
    ]);

    Notification::assertSentTo(
        [$guardian, $user],
        AttendanceStatusNotification::class
    );
});

it('sends notification when attendance status is updated', function () {
    Notification::fake();

    $user = User::factory()->create(['role' => 'student']);
    $guardian = Guardian::factory()->create(['email' => 'guardian@example.com']);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'guardian_id' => $guardian->id,
    ]);

    $subject = Subject::factory()->create();
    $course = Course::factory()->create(['id' => $subject->id]);
    $session = ClassSession::factory()->create([
        'course_id' => $course->id,
    ]);

    $record = AttendanceRecord::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'session_id' => $session->id,
        'status' => 'absent',
        'timestamp' => now(),
        'recorded_by' => User::factory()->create(['role' => 'teacher'])->id,
    ]);

    // Reset notification fake for update check
    Notification::fake();

    $record->update(['status' => 'present']);

    Notification::assertSentTo(
        [$guardian, $user],
        AttendanceStatusNotification::class
    );
});
