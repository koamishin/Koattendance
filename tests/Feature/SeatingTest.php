<?php

use App\Models\Seating;
use App\Models\Student;
use App\Models\User;

test('can fetch seating arrangement', function () {
    $user = User::factory()->create();
    $students = Student::factory()->count(5)->create();

    // Create some seating records
    foreach ($students->take(3) as $index => $student) {
        Seating::create([
            'student_id' => $student->id,
            'seat_number' => $index + 1,
            'room' => '301',
        ]);
    }

    $response = $this->actingAs($user)->getJson('/api/seating');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'seatings' => [
            '*' => [
                'id',
                'student_id',
                'seat_number',
                'room',
            ],
        ],
    ]);
});

test('can update seating arrangement', function () {
    $user = User::factory()->create();
    $students = Student::factory()->count(3)->create();

    $response = $this->actingAs($user)->postJson('/api/seating', [
        'seatings' => [
            ['seat_number' => 1, 'student_id' => $students[0]->id],
            ['seat_number' => 2, 'student_id' => $students[1]->id],
            ['seat_number' => 3, 'student_id' => null],
        ],
    ]);

    $response->assertSuccessful();

    expect(Seating::where('seat_number', 1)->first()->student_id)
        ->toBe($students[0]->id);
    expect(Seating::where('seat_number', 2)->first()->student_id)
        ->toBe($students[1]->id);
    expect(Seating::where('seat_number', 3)->first()->student_id)
        ->toBeNull();
});

test('can remove student from seat', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    Seating::create([
        'student_id' => $student->id,
        'seat_number' => 1,
        'room' => '301',
    ]);

    $this->actingAs($user)->postJson('/api/seating', [
        'seatings' => [
            ['seat_number' => 1, 'student_id' => null],
        ],
    ]);

    expect(Seating::where('seat_number', 1)->first()->student_id)
        ->toBeNull();
});

test('seating api validates student id', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/seating', [
        'seatings' => [
            ['seat_number' => 1, 'student_id' => 9999],
        ],
    ]);

    $response->assertUnprocessable();
});
