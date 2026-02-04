<?php

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('can update existing attendance record', function () {
    Notification::fake();

    $user = User::factory()->create();
    $record = AttendanceRecord::factory()->create(['status' => 'absent']);

    $response = $this->actingAs($user)->post("/attendance/{$record->id}/update-status", [
        'status' => 'present',
    ]);

    $response->assertFound();
    $record->refresh();
    expect($record->status)->toBe('present');
});

test('can mark unmarked student as present', function () {
    Notification::fake();

    $user = User::factory()->create();
    $student = Student::factory()->create();
    $session = ClassSession::factory()->create([
        'status' => 'in_progress',
        'scheduled_date' => now()->toDateString(),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ]);

    // Mark an unmarked student (no existing attendance record)
    $response = $this->actingAs($user)->post('/attendance/null/update-status', [
        'status' => 'present',
        'student_id' => $student->id,
        'session_id' => $session->id,
    ]);

    $response->assertFound();
    $this->assertDatabaseHas('attendance_records', [
        'student_id' => $student->id,
        'session_id' => $session->id,
        'status' => 'present',
    ]);
});

test('returns error when student not found for unmarked student', function () {
    Notification::fake();

    $user = User::factory()->create();
    $session = ClassSession::factory()->create([
        'status' => 'in_progress',
        'scheduled_date' => now()->toDateString(),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ]);

    $response = $this->actingAs($user)->post('/attendance/null/update-status', [
        'status' => 'present',
        'student_id' => 999999,
        'session_id' => $session->id,
    ]);

    $response->assertFound();
});

test('returns error when record not found for existing record', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/attendance/9999/update-status', [
        'status' => 'present',
    ]);

    $response->assertNotFound();
});
