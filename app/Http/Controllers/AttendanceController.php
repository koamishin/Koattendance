<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function updateStatus($id)
    {
        $record = AttendanceRecord::find($id);

        if (!$record) {
            return back()->with('error', 'Record not found');
        }

        $record->update([
            'status' => request('status'),
        ]);

        return back()->with('success', 'Attendance updated successfully');
    }

    public function index(): JsonResponse
    {
        $allRecords = AttendanceRecord::orderBy('date', 'desc')->get();
        Log::info('Attendance Records Count: ' . $allRecords->count());
        $latestDate = $allRecords->first()?->date;

        // Get all students
        $students = \App\Models\Student::all();

        // Build attendance map for the latest date
        $attendanceMap = $allRecords
            ->when($latestDate, function ($collection) use ($latestDate) {
                return $collection->filter(fn ($record) => $record->date->toDateString() === $latestDate->toDateString());
            })
            ->keyBy('student_name');

        // Create records for all students
        $records = $students->map(function ($student) use ($attendanceMap, $latestDate) {
            $record = $attendanceMap->get($student->name);

            if ($record) {
                return [
                    'id' => $record->id,
                    'name' => $student->name,
                    'status' => $record->status,
                    'date' => $record->date->format('M d, Y'),
                    'time' => $record->status === 'absent' ? '-' : ($record->time ? date('h:i A', strtotime($record->time)) : '-'),
                ];
            }

            return [
                'id' => null,
                'name' => $student->name,
                'status' => 'unmarked',
                'date' => $latestDate ? $latestDate->format('M d, Y') : 'N/A',
                'time' => '-',
            ];
        })->sortBy('name')->values();

        $stats = [
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'total' => $records->count(),
        ];

        return response()->json([
            'attendanceRecords' => $records,
            'stats' => $stats,
            'latestDate' => $latestDate ? $latestDate->format('F d, Y') : null,
        ]);
    }
}
