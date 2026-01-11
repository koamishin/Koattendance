<?php

use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\User;

test('can update existing attendance record', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['name' => 'John Doe']);
    $record = AttendanceRecord::factory()->create([
        'student_id' => $student->id,
        'student_name' => $student->name,
        'status' => 'absent',
    ]);

    $response = $this->actingAs($user)->post("/attendance/{$record->id}/update-status", [
        'status' => 'present',
    ]);

    $response->assertFound();
    $record->refresh();
    expect($record->status)->toBe('present');
});

test('can mark unmarked student as present', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['name' => 'Jane Smith']);
    AttendanceRecord::factory()->create([
        'date' => now()->toDateString(),
    ]);

    // Mark an unmarked student (no existing attendance record)
    $response = $this->actingAs($user)->post('/attendance/null/update-status', [
        'status' => 'present',
        'studentName' => $student->name,
    ]);

    $response->assertFound();
    $this->assertDatabaseHas('attendance_records', [
        'student_id' => $student->id,
        'student_name' => 'Jane Smith',
        'status' => 'present',
    ]);
});

test('returns error when student not found for unmarked student', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/attendance/null/update-status', [
        'status' => 'present',
        'studentName' => 'Nonexistent Student',
    ]);

    $response->assertFound();
});

test('returns error when record not found for existing record', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/attendance/9999/update-status', [
        'status' => 'present',
    ]);

    $response->assertFound();
});
