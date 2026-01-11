<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function getStats(): JsonResponse
    {
        $totalStudents = Student::count();
        
        $today = \Carbon\Carbon::today();
        $todayRecords = AttendanceRecord::whereDate('date', $today)->get();
        
        $presentToday = $todayRecords->where('status', 'present')->count();
        $absentToday = $todayRecords->where('status', 'absent')->count();
        $lateToday = $todayRecords->where('status', 'late')->count();
        
        $averageGrade = Grade::avg('grade');
        $averageGrade = $averageGrade ? round($averageGrade, 2) : 0;

        return response()->json([
            'stats' => [
                [
                    'title' => 'Total Students',
                    'value' => $totalStudents,
                    'icon' => 'Users',
                    'color' => 'text-blue-600',
                    'bgColor' => 'bg-blue-100 dark:bg-blue-900/30',
                ],
                [
                    'title' => 'Present Today',
                    'value' => $presentToday,
                    'icon' => 'CheckCircle2',
                    'color' => 'text-green-600',
                    'bgColor' => 'bg-green-100 dark:bg-green-900/30',
                ],
                [
                    'title' => 'Average Grade',
                    'value' => $averageGrade,
                    'icon' => 'TrendingUp',
                    'color' => 'text-purple-600',
                    'bgColor' => 'bg-purple-100 dark:bg-purple-900/30',
                ],
                [
                    'title' => 'Absent Today',
                    'value' => $absentToday,
                    'icon' => 'AlertCircle',
                    'color' => 'text-red-600',
                    'bgColor' => 'bg-red-100 dark:bg-red-900/30',
                ],
            ],
            'latestDate' => $today->format('F d, Y'),
        ]);
    }

    public function getAttendanceSummary(): JsonResponse
    {
        $allRecords = AttendanceRecord::orderBy('date', 'desc')->get();
        $latestDate = $allRecords->first()?->date;

        $records = $allRecords
            ->when($latestDate, function ($collection) use ($latestDate) {
                return $collection->filter(fn ($record) => $record->date->toDateString() === $latestDate->toDateString());
            })
            ->sortBy('student_name')
            ->take(5)
            ->map(fn ($record) => [
                'id' => $record->id,
                'name' => $record->student_name,
                'status' => $record->status,
                'time' => $record->status === 'absent' ? '-' : ($record->time ? date('h:i A', strtotime($record->time)) : '-'),
            ])
            ->values();

        return response()->json([
            'recentRecords' => $records,
            'latestDate' => $latestDate ? $latestDate->format('M d, Y') : null,
        ]);
    }

    public function getGradeSummary(): JsonResponse
    {
        // Get top performing student by average grade
        $studentAverages = Grade::selectRaw('student_name, AVG(CAST(grade AS REAL)) as avg_grade')
            ->groupBy('student_name')
            ->orderByRaw('AVG(CAST(grade AS REAL)) DESC')
            ->limit(1)
            ->get();

        $topGrades = $studentAverages->map(fn ($student) => [
            'name' => $student->student_name,
            'subject' => 'Class Average',
            'grade' => round($student->avg_grade, 2),
        ]);

        $gradeDistribution = [
            'A (90-100)' => Grade::whereRaw('CAST(grade AS REAL) >= 90')->count(),
            'B (80-89)' => Grade::whereRaw('CAST(grade AS REAL) >= 80 AND CAST(grade AS REAL) < 90')->count(),
            'C (70-79)' => Grade::whereRaw('CAST(grade AS REAL) >= 70 AND CAST(grade AS REAL) < 80')->count(),
            'D (60-69)' => Grade::whereRaw('CAST(grade AS REAL) >= 60 AND CAST(grade AS REAL) < 70')->count(),
            'F (<60)' => Grade::whereRaw('CAST(grade AS REAL) < 60')->count(),
        ];

        return response()->json([
            'topGrades' => $topGrades,
            'gradeDistribution' => $gradeDistribution,
        ]);
    }
}
