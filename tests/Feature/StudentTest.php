<?php

use App\Models\Student;
use App\Models\User;

test('can fetch all students from api', function () {
    $user = User::factory()->create();

    Student::factory()->count(5)->create();

    $response = $this->actingAs($user)->getJson('/api/students');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'students' => [
            '*' => [
                'id',
                'name',
                'email',
                'student_id',
            ],
        ],
    ]);
    expect($response->json('students'))->toHaveCount(5);
});

test('returns empty array when no students exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/students');

    $response->assertSuccessful();
    expect($response->json('students'))->toBeEmpty();
});

test('student api requires authentication', function () {
    $response = $this->getJson('/api/students');

    $response->assertUnauthorized();
});
