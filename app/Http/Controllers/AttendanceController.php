<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function updateStatus($id): \Illuminate\Http\Response | \Illuminate\Http\RedirectResponse
    {
        // Handle unmarked students (id will be null, but studentName will be provided)
        if ($id === 'null' || $id == null) {
            $studentName = request('studentName');
            $student = Student::where('name', $studentName)->first();

            if (! $student) {
                return back()->with('error', 'Student not found');
            }

            // Use the provided date or get the latest date
            $date = request('date') ? \Carbon\Carbon::parse(request('date')) : (AttendanceRecord::orderBy('date', 'desc')->first()?->date ?? now());
            
            $subjectId = request('subjectId') ? (int) request('subjectId') : null;

            AttendanceRecord::create([
                'student_id' => $student->id,
                'student_name' => $studentName,
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
        $allRecords = AttendanceRecord::with('subject')->orderBy('date', 'desc')->get();
        Log::info('Attendance Records Count: '.$allRecords->count());
        
        // Get selected date and subject from query parameters
        $selectedDate = request('date') 
            ? \Carbon\Carbon::parse(request('date')) 
            : \Carbon\Carbon::today();
        
        $selectedSubjectId = request('subjectId') ? (int) request('subjectId') : null;
        
        $latestDate = $allRecords->first()?->date;

        // Get all unique dates from records for the date selector
        $recordDates = $allRecords
            ->pluck('date')
            ->unique()
            ->map(fn ($date) => $date->format('Y-m-d'));

        // Add today and the next 30 days
        $today = \Carbon\Carbon::today();
        $futureDates = collect();
        for ($i = 0; $i <= 30; $i++) {
            $futureDates->push($today->copy()->addDays($i)->format('Y-m-d'));
        }

        $availableDates = $recordDates
            ->merge($futureDates)
            ->unique()
            ->sort()
            ->reverse()
            ->values();

        // Get all subjects
        $subjects = Subject::all()->map(fn ($subject) => [
            'id' => $subject->id,
            'name' => $subject->name,
        ])->values();

        // Get all students (show all students for any subject)
        // The subject_id is just used to organize attendance records by subject
        $students = \App\Models\Student::all();

        // Build attendance map for the selected date and subject
        $attendanceMap = $allRecords
            ->when($selectedDate, function ($collection) use ($selectedDate) {
                return $collection->filter(fn ($record) => $record->date->toDateString() === $selectedDate->toDateString());
            })
            ->when($selectedSubjectId, function ($collection) use ($selectedSubjectId) {
                return $collection->filter(fn ($record) => $record->subject_id === $selectedSubjectId);
            })
            ->keyBy('student_name');

        // Create records for filtered students
        $records = $students->map(function ($student) use ($attendanceMap, $selectedDate) {
            $record = $attendanceMap->get($student->name);

            if ($record) {
                return [
                    'id' => $record->id,
                    'name' => $student->name,
                    'status' => $record->status,
                    'date' => $record->date->format('M d, Y'),
                    'dayOfWeek' => $record->date->format('l'),
                    'time' => $record->status === 'absent' ? '-' : ($record->time ? date('h:i A', strtotime($record->time)) : '-'),
                ];
            }

            return [
                'id' => null,
                'name' => $student->name,
                'status' => 'unmarked',
                'date' => $selectedDate ? $selectedDate->format('M d, Y') : 'N/A',
                'dayOfWeek' => $selectedDate ? $selectedDate->format('l') : 'N/A',
                'time' => '-',
            ];
        })->sortBy('name')->values();

        // Group records by day of week (Monday to Saturday)
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $groupedRecords = $records->groupBy('dayOfWeek')->sortBy(function ($group, $day) use ($dayOrder) {
            return array_search($day, $dayOrder, true);
        });

        $stats = [
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'total' => $records->count(),
        ];

        return response()->json([
            'attendanceRecords' => $records,
            'groupedRecords' => $groupedRecords,
            'stats' => $stats,
            'selectedDate' => $selectedDate ? $selectedDate->format('F d, Y') : null,
            'latestDate' => $latestDate ? $latestDate->format('F d, Y') : null,
            'availableDates' => $availableDates,
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
        ]);
    }
}
