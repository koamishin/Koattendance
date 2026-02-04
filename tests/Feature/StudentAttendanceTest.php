<?php

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('api returns student present status from todays attendance', function () {
    Notification::fake();

    $user = User::factory()->create();
    $student = Student::factory()->create();
    $session = ClassSession::factory()->create([
        'status' => 'in_progress',
        'scheduled_date' => now()->toDateString(),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ]);

    AttendanceRecord::create([
        'session_id' => $session->id,
        'student_id' => $student->id,
        'status' => 'present',
        'timestamp' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/students');

    $response->assertSuccessful();
    $student_data = collect($response->json('students'))->firstWhere('id', $student->id);
    expect($student_data['present'])->toBeTrue();
});

test('api returns student absent status from todays attendance', function () {
    Notification::fake();

    $user = User::factory()->create();
    $student = Student::factory()->create();
    $session = ClassSession::factory()->create([
        'status' => 'in_progress',
        'scheduled_date' => now()->toDateString(),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ]);

    AttendanceRecord::create([
        'session_id' => $session->id,
        'student_id' => $student->id,
        'status' => 'absent',
        'timestamp' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/students');

    $response->assertSuccessful();
    $student_data = collect($response->json('students'))->firstWhere('id', $student->id);
    expect($student_data['present'])->toBeFalse();
});

test('api returns false for students with no attendance today', function () {
    Notification::fake();

    $user = User::factory()->create();
    $student = Student::factory()->create();

    // No attendance record created

    $response = $this->actingAs($user)->getJson('/api/students');

    $response->assertSuccessful();
    $student_data = collect($response->json('students'))->firstWhere('id', $student->id);
    expect($student_data['present'])->toBeFalse();
});
