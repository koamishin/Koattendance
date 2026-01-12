<?php

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;

test('can fetch grades', function () {
    $user = User::factory()->create();

    Subject::factory()->create(['name' => 'Math']);
    Subject::factory()->create(['name' => 'Science']);

    Grade::factory()->create([
        'student_name' => 'John Doe',
        'subject_id' => 1,
        'grade' => 85,
    ]);

    Grade::factory()->create([
        'student_name' => 'John Doe',
        'subject_id' => 2,
        'grade' => 90,
    ]);

    $response = $this->actingAs($user)->get('/dashboard/grades');

    $response->assertSuccessful();
    $response->assertInertiaHasKeys([
        'gradeRecords',
        'subjects',
    ]);
});

test('can update grade', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Math']);

    $grade = Grade::factory()->create([
        'student_name' => 'Jane Smith',
        'subject_id' => $subject->id,
        'grade' => 75,
    ]);

    $response = $this->actingAs($user)->patch("/dashboard/grades/{$grade->id}", [
        'grade' => 92,
    ]);

    $response->assertRedirectToRoute('dashboard.grades');
    $response->assertSessionHas('success', 'Grade updated successfully');

    $this->assertDatabaseHas('grades', [
        'id' => $grade->id,
        'grade' => 92,
    ]);
});

test('grade update requires valid range', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Math']);

    $grade = Grade::factory()->create([
        'student_name' => 'Jane Smith',
        'subject_id' => $subject->id,
        'grade' => 75,
    ]);

    // Test invalid grade below range
    $response = $this->actingAs($user)->patch("/dashboard/grades/{$grade->id}", [
        'grade' => -5,
    ]);

    $response->assertInvalid();

    // Test invalid grade above range
    $response = $this->actingAs($user)->patch("/dashboard/grades/{$grade->id}", [
        'grade' => 150,
    ]);

    $response->assertInvalid();

    // Original grade should remain unchanged
    $this->assertDatabaseHas('grades', [
        'id' => $grade->id,
        'grade' => 75,
    ]);
});

test('grade update requires grade field', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Math']);

    $grade = Grade::factory()->create([
        'student_name' => 'Jane Smith',
        'subject_id' => $subject->id,
        'grade' => 75,
    ]);

    $response = $this->actingAs($user)->patch("/dashboard/grades/{$grade->id}", []);

    $response->assertInvalid();
});

test('grades page accessible to authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard/grades');

    $response->assertSuccessful();
});

test('grades page requires authentication', function () {
    $response = $this->get('/dashboard/grades');

    $response->assertRedirectToRoute('login');
});
