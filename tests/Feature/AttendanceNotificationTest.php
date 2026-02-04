<?php

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\AttendanceStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('does not queue the attendance notification', function () {
    expect(is_subclass_of(AttendanceStatusNotification::class, ShouldQueue::class))->toBeFalse();
});

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

it('sends absent notifications when a teacher ends a session', function () {
    Notification::fake();

    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

    $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);
    $course = Course::factory()->create();

    $absentStudentUser = User::factory()->student()->create(['email' => 'student@example.com']);
    $guardian = Guardian::factory()->create(['email' => 'guardian@example.com']);
    $absentStudent = Student::factory()->create([
        'user_id' => $absentStudentUser->id,
        'guardian_id' => $guardian->id,
    ]);

    $presentStudent = Student::factory()->create();

    $subject->students()->attach([$absentStudent->id, $presentStudent->id]);

    $session = ClassSession::factory()->create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'course_id' => $course->id,
        'section_id' => null,
        'scheduled_date' => now()->toDateString(),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
        'status' => 'in_progress',
        'attendance_mode' => 'qr_scan',
        'late_threshold_minutes' => 15,
    ]);

    AttendanceRecord::create([
        'session_id' => $session->id,
        'student_id' => $presentStudent->id,
        'subject_id' => $subject->id,
        'status' => 'present',
        'timestamp' => now(),
        'recorded_by' => $teacherUser->id,
    ]);

    $response = $this->actingAs($teacherUser)->postJson(route('api.sessions.end', $session));

    $response->assertSuccessful();

    $this->assertDatabaseHas('attendance_records', [
        'session_id' => $session->id,
        'student_id' => $absentStudent->id,
        'status' => 'absent',
    ]);

    Notification::assertSentTo(
        $absentStudentUser,
        AttendanceStatusNotification::class,
        fn (AttendanceStatusNotification $notification) => $notification->record->session_id === $session->id
            && $notification->record->student_id === $absentStudent->id
            && $notification->record->status === 'absent'
    );

    Notification::assertSentTo(
        $guardian,
        AttendanceStatusNotification::class,
        fn (AttendanceStatusNotification $notification) => $notification->record->session_id === $session->id
            && $notification->record->student_id === $absentStudent->id
            && $notification->record->status === 'absent'
    );
});
