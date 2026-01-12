<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function updateStatus($id): \Illuminate\Http\Response | \Illuminate\Http\RedirectResponse
    {
        // Handle unmarked students (id will be null, but studentName will be provided)
        if ($id === 'null' || $id == null) {
            $studentName = request('studentName');
            $student = Student::where('name', $studentName)->first();
            
            // If student is not found by name, try to find by ID if provided in request context
            // Or if we change the frontend to pass student ID.
            if (!$student) {
                 // Try to find by ID if we have it in the request payload or query
                 // Ideally frontend should send student_id for creation.
            }

            if (! $student) {
                return back()->with('error', 'Student not found');
            }

            // Use the provided date or get the latest date
            $date = request('date') ? \Carbon\Carbon::parse(request('date')) : (AttendanceRecord::orderBy('date', 'desc')->first()?->date ?? now());
            
            $subjectId = request('subjectId') ? (int) request('subjectId') : null;

            AttendanceRecord::create([
                'student_id' => $student->id,
                'student_name' => $studentName, // We should deprecate this
                'status' => request('status'),
                'date' => $date,
                'time' => now(),
                'subject_id' => $subjectId,
            ]);

            return back()->with('success', 'Attendance marked successfully');
        }

        $record = AttendanceRecord::find($id);

        if (! $record) {
            return back()->with('error', 'Record not found');
        }

        $record->update([
            'status' => request('status'),
        ]);

        return back()->with('success', 'Attendance updated successfully');
    }

    public function index(): JsonResponse
    {
        $selectedSubjectId = request('subjectId') ? (int) request('subjectId') : null;
        $selectedDate = request('date') 
            ? \Carbon\Carbon::parse(request('date')) 
            : \Carbon\Carbon::today();

        // 1. Get Enrolled Students for the selected Subject
        $enrolledStudents = collect();
        if ($selectedSubjectId) {
            $subject = Subject::with('students')->find($selectedSubjectId);
            if ($subject) {
                $enrolledStudents = $subject->students;
            }
        } else {
            // If no subject selected, maybe show all students? 
            // Or better, return empty list until subject is picked.
            // For backward compatibility or "Overview" mode, we might fetch all.
            // But user wants "Per Subject".
            // Let's return empty if no subject is selected, forcing selection.
            $enrolledStudents = collect(); 
        }

        // 2. Get Attendance Records for the selected date and subject
        $recordsQuery = AttendanceRecord::query()
            ->with('student')
            ->whereDate('date', $selectedDate);

        if ($selectedSubjectId) {
            $recordsQuery->where('subject_id', $selectedSubjectId);
        }

        $attendanceRecords = $recordsQuery->get()->keyBy('student_id');

        // 3. Merge Enrolled Students with Attendance Records
        $mergedRecords = $enrolledStudents->map(function ($student) use ($attendanceRecords, $selectedDate) {
            $record = $attendanceRecords->get($student->id);

            if ($record) {
                return [
                    'id' => $record->id,
                    'student_id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name, // Using new fields
                    'status' => $record->status,
                    'date' => $record->date->format('M d, Y'),
                    'dayOfWeek' => $record->date->format('l'),
                    'time' => $record->status === 'absent' ? '-' : ($record->time ? date('h:i A', strtotime($record->time)) : '-'),
                ];
            }

            return [
                'id' => null, // No record ID yet
                'student_id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'status' => 'unmarked',
                'date' => $selectedDate->format('M d, Y'),
                'dayOfWeek' => $selectedDate->format('l'),
                'time' => '-',
            ];
        })->sortBy('name')->values();

        // Stats calculation based on merged records (enrolled students)
        $stats = [
            'present' => $mergedRecords->where('status', 'present')->count(),
            'absent' => $mergedRecords->where('status', 'absent')->count(),
            'late' => $mergedRecords->where('status', 'late')->count(),
            'unmarked' => $mergedRecords->where('status', 'unmarked')->count(),
            'total' => $mergedRecords->count(),
        ];

        // Dates for selection (this might need to be smarter, e.g., session dates)
        // For now, keep the "Last 30 days + Future" logic or just rely on date picker
        $today = \Carbon\Carbon::today();
        $availableDates = [];
        for ($i = 0; $i <= 30; $i++) {
            $availableDates[] = $today->copy()->addDays($i)->format('Y-m-d');
        }
        // Also add dates that have records
        $existingDates = AttendanceRecord::where('subject_id', $selectedSubjectId)
            ->pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();
        
        $availableDates = array_unique(array_merge($existingDates, $availableDates));
        rsort($availableDates);

        // Get all subjects for dropdown
        $subjects = Subject::all()->map(fn ($subject) => [
            'id' => $subject->id,
            'name' => $subject->name,
            'students_count' => $subject->students()->count(),
        ])->values();

        return response()->json([
            'attendanceRecords' => $mergedRecords,
            'stats' => $stats,
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'availableDates' => array_values($availableDates),
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
        ]);
    }

    /**
     * Enroll a student in a subject.
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        
        // Check if already attached
        if (!$subject->students()->where('student_id', $request->student_id)->exists()) {
            $subject->students()->attach($request->student_id);
            return back()->with('success', 'Student added to class successfully.');
        }

        return back()->with('info', 'Student is already enrolled in this class.');
    }
    
    /**
     * Search students not enrolled in a subject.
     */
    public function searchStudents(Request $request)
    {
        $search = $request->get('query');
        $subjectId = $request->get('subject_id');

        return Student::query()
            ->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            })
            ->whereDoesntHave('subjects', function($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            })
            ->limit(10)
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_id' => $student->student_id,
                ];
            });
    }
}
