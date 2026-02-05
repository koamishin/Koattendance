<?php

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Section;
use App\Models\AttendanceRecord;
use App\Models\QrScanEvent;
use Illuminate\Support\Facades\Crypt;

test('student can generate qr code', function () {
    $student = Student::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('api.students.qr-code', $student));

    $response->assertStatus(200)
        ->assertJsonStructure(['qr_code', 'student_id', 'name']);

    $this->assertNotNull($student->fresh()->qr_code_data);
});

test('teacher can scan valid student qr code', function () {
    // Setup
    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    
    $course = Course::factory()->create();
    $section = Section::factory()->create();
    
    $session = ClassSession::factory()->create([
        'course_id' => $course->id,
        'section_id' => $section->id,
        'teacher_id' => $teacher->id,
        'status' => 'in_progress',
        'scheduled_date' => now()->toDateString(),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ]);

    $student = Student::factory()->create([
        'section_id' => $section->id,
    ]);
    
    // Generate QR
    $student->generateQrCode();
    $qrData = $student->qr_code_data;

    // Act
    $response = $this->actingAs($teacherUser)->postJson(route('api.attendance.scan'), [
        'qr_code' => $qrData,
        'session_id' => $session->id,
        'device_info' => ['browser' => 'Test Browser'],
    ]);

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Attendance recorded successfully',
            'scan_status' => 'success',
        ]);

    $this->assertDatabaseHas('attendance_records', [
        'session_id' => $session->id,
        'student_id' => $student->id,
        'status' => 'present',
    ]);

    $this->assertDatabaseHas('qr_scan_events', [
        'session_id' => $session->id,
        'student_id' => $student->id,
        'status' => 'success',
    ]);
});

test('duplicate scan updates existing attendance record', function () {
    // Setup
    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    $session = ClassSession::factory()->create([
        'teacher_id' => $teacher->id,
        'status' => 'in_progress',
        'scheduled_date' => now()->toDateString(),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ]);
    $student = Student::factory()->create();
    
    $student->generateQrCode();
    $qrData = $student->qr_code_data;

    // First Scan
    $this->actingAs($teacherUser)->postJson(route('api.attendance.scan'), [
        'qr_code' => $qrData,
        'session_id' => $session->id,
    ]);

    // Second Scan
    $response = $this->actingAs($teacherUser)->postJson(route('api.attendance.scan'), [
        'qr_code' => $qrData,
        'session_id' => $session->id,
    ]);

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Attendance updated successfully',
            'scan_status' => 'success',
        ]);
});

test('invalid qr code is rejected', function () {
    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    $session = ClassSession::factory()->create([
        'teacher_id' => $teacher->id,
        'status' => 'in_progress',
        'scheduled_date' => now()->toDateString(),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ]);

    $response = $this->actingAs($teacherUser)->postJson(route('api.attendance.scan'), [
        'qr_code' => 'invalid-garbage-data',
        'session_id' => $session->id,
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Invalid QR Code',
        ]);
});

test('tampered qr code is rejected', function () {
    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    $session = ClassSession::factory()->create([
        'teacher_id' => $teacher->id,
        'status' => 'in_progress',
        'scheduled_date' => now()->toDateString(),
        'start_time' => now()->format('H:i:s'),
        'end_time' => now()->addHour()->format('H:i:s'),
    ]);
    $student = Student::factory()->create();
    
    // Create a fake encrypted string that decrypts to something else
    $fakeData = Crypt::encrypt(['id' => 999999, 'student_id' => 'fake']);

    $response = $this->actingAs($teacherUser)->postJson(route('api.attendance.scan'), [
        'qr_code' => $fakeData,
        'session_id' => $session->id,
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Student not found',
        ]);
});
