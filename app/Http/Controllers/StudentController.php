<?php

namespace App\Http\Controllers;

use App\Models\Seating;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(): JsonResponse
    {
        $today = now()->toDateString();

        $students = Student::select('id', 'name', 'email', 'student_id')
            ->with(['attendanceRecords' => function ($query) use ($today) {
                $query->whereDate('date', $today)->latest();
            }])
            ->get()
            ->map(function ($student) {
                $latestAttendance = $student->attendanceRecords->first();
                $status = $latestAttendance ? $latestAttendance->status : 'absent';
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'student_id' => $student->student_id,
                    'status' => $status,
                    'present' => $status === 'present',
                ];
            });

        return response()->json([
            'students' => $students,
        ]);
    }

    public function getSeating(): JsonResponse
    {
        $arrangement = \App\Models\SeatingArrangement::firstOrCreate(
            ['room' => '301'],
            ['rows' => 4, 'columns' => 4]
        );

        $seatings = Seating::with('student')
            ->where('room', '301')
            ->orderBy('seat_number')
            ->get();

        $totalSeats = $arrangement->rows * $arrangement->columns;

        return response()->json([
            'arrangement' => [
                'rows' => $arrangement->rows,
                'columns' => $arrangement->columns,
                'totalSeats' => $totalSeats,
            ],
            'seatings' => $seatings,
        ]);
    }

    public function updateSeating(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'seatings' => 'required|array',
            'seatings.*.seat_number' => 'required|integer',
            'seatings.*.student_id' => 'nullable|integer|exists:students,id',
        ]);

        foreach ($validated['seatings'] as $seating) {
            Seating::updateOrCreate(
                [
                    'seat_number' => $seating['seat_number'],
                    'room' => '301',
                ],
                [
                    'student_id' => $seating['student_id'],
                ]
            );
        }

        return response()->json([
            'message' => 'Seating arrangement updated successfully',
        ]);
    }

    public function updateGridDimensions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rows' => 'required|integer|min:1|max:10',
            'columns' => 'required|integer|min:1|max:10',
        ]);

        $arrangement = \App\Models\SeatingArrangement::updateOrCreate(
            ['room' => '301'],
            ['rows' => $validated['rows'], 'columns' => $validated['columns']]
        );

        return response()->json([
            'message' => 'Grid dimensions updated successfully',
            'arrangement' => [
                'rows' => $arrangement->rows,
                'columns' => $arrangement->columns,
                'totalSeats' => $arrangement->rows * $arrangement->columns,
            ],
        ]);
    }
}
