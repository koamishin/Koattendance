<?php

use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\User;

test('api returns student present status from todays attendance', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    AttendanceRecord::create([
        'student_id' => $student->id,
        'student_name' => $student->name,
        'status' => 'present',
        'date' => now()->toDateString(),
        'time' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/students');

    $response->assertSuccessful();
    $student_data = $response->json('students.0');
    expect($student_data['present'])->toBeTrue();
});

test('api returns student absent status from todays attendance', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    AttendanceRecord::create([
        'student_id' => $student->id,
        'student_name' => $student->name,
        'status' => 'absent',
        'date' => now()->toDateString(),
        'time' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/students');

    $response->assertSuccessful();
    $student_data = $response->json('students.0');
    expect($student_data['present'])->toBeFalse();
});

test('api returns false for students with no attendance today', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    // No attendance record created

    $response = $this->actingAs($user)->getJson('/api/students');

    $response->assertSuccessful();
    $student_data = $response->json('students.0');
    expect($student_data['present'])->toBeFalse();
});
