<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Seating;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(): JsonResponse
    {
        $today = now()->toDateString();

        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->with(['attendanceRecords' => function ($query) use ($today) {
                $query->whereDate('timestamp', $today)->latest();
            }])
            ->get()
            ->map(function ($student) {
                $latestAttendance = $student->attendanceRecords->first();
                $status = $latestAttendance ? $latestAttendance->status : 'absent';
                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_id' => $student->student_id,
                    'status' => $status,
                    'present' => $status === 'present',
                ];
            });

        return response()->json([
            'students' => $students,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'student_id' => 'required|string|max:255|unique:students,student_id',
            'phone' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'subject_id' => 'nullable|integer|exists:subjects,id',
            // Guardian fields
            'guardian_name' => 'nullable|string|max:255',
            'guardian_email' => 'nullable|email|max:255',
            'guardian_phone' => 'nullable|string|max:50',
            'guardian_relationship' => 'nullable|string|max:100',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create guardian if info provided
            $guardianId = null;
            if (!empty($validated['guardian_name']) || !empty($validated['guardian_email']) || !empty($validated['guardian_phone'])) {
                $guardianNameParts = explode(' ', $validated['guardian_name'] ?? 'Guardian', 2);
                
                $guardian = Guardian::create([
                    'first_name' => $guardianNameParts[0],
                    'last_name' => $guardianNameParts[1] ?? '',
                    'email' => $validated['guardian_email'] ?? null,
                    'phone' => $validated['guardian_phone'] ?? null,
                    'relationship' => $validated['guardian_relationship'] ?? 'Parent',
                    'is_primary' => true,
                ]);
                
                $guardianId = $guardian->id;
            }
            
            // Create student (user_id is now nullable)
            $student = Student::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'student_id' => $validated['student_id'],
                'phone' => $validated['phone'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'guardian_id' => $guardianId,
                'status' => 'active',
                'enrollment_date' => now()->toDateString(),
                'qr_code_active' => true,
            ]);
            
            // Generate QR code for the student
            $student->generateQrCode();
            
            // If subject_id is provided, enroll the student
            if (!empty($validated['subject_id'])) {
                $student->subjects()->attach($validated['subject_id']);
            }
            
            DB::commit();

            return response()->json([
                'message' => 'Student created successfully',
                'student' => $student->load('guardian'),
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create student: ' . $e->getMessage(),
            ], 500);
        }
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
