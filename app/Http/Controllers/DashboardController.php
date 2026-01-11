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
        $allRecords = AttendanceRecord::with('subject')->orderBy('date', 'desc')->get();
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
                'subject' => $record->subject?->name ?? 'N/A',
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
        // Get top performing students with 95+ average
        $studentAverages = Grade::selectRaw('student_name, AVG(CAST(grade AS REAL)) as avg_grade')
            ->groupBy('student_name')
            ->havingRaw('AVG(CAST(grade AS REAL)) >= 95')
            ->orderByRaw('AVG(CAST(grade AS REAL)) DESC')
            ->limit(5)
            ->get();

        $topGrades = $studentAverages->map(fn ($student) => [
            'name' => $student->student_name,
            'subject' => 'Class Average',
            'grade' => round($student->avg_grade, 2),
        ]);

        // Count unique students by average grade
        $studentAverages = Grade::selectRaw('student_name, AVG(CAST(grade AS REAL)) as avg_grade')
            ->groupBy('student_name')
            ->get();

        $gradeDistribution = [
            'A (90-100)' => $studentAverages->filter(fn ($s) => $s->avg_grade >= 90)->count(),
            'B (80-89)' => $studentAverages->filter(fn ($s) => $s->avg_grade >= 80 && $s->avg_grade < 90)->count(),
            'C (70-79)' => $studentAverages->filter(fn ($s) => $s->avg_grade >= 70 && $s->avg_grade < 80)->count(),
            'D (60-69)' => $studentAverages->filter(fn ($s) => $s->avg_grade >= 60 && $s->avg_grade < 70)->count(),
            'F (<60)' => $studentAverages->filter(fn ($s) => $s->avg_grade < 60)->count(),
        ];

        return response()->json([
            'topGrades' => $topGrades,
            'gradeDistribution' => $gradeDistribution,
        ]);
    }

    public function getWeeklyAttendance(): JsonResponse
    {
        $today = \Carbon\Carbon::today();
        
        // Get the Monday of the current week
        $monday = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        
        // Define the days (Monday to Saturday)
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $weeklyData = [];
        
        foreach ($days as $index => $dayName) {
            $dayDate = $monday->copy()->addDays($index);
            
            $dayRecords = AttendanceRecord::whereDate('date', $dayDate)->get();
            
            $present = $dayRecords->where('status', 'present')->count();
            $absent = $dayRecords->where('status', 'absent')->count();
            $late = $dayRecords->where('status', 'late')->count();
            
            $weeklyData[] = [
                'day' => substr($dayName, 0, 3),
                'date' => $dayDate->format('Y-m-d'),
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'total' => $present + $absent + $late,
            ];
        }
        
        return response()->json([
            'weeklyAttendance' => $weeklyData,
        ]);
    }

    public function getSubjectPerformance(): JsonResponse
    {
        // Get all unique subjects and calculate average and max grade for each
        $subjectPerformance = Grade::join('subjects', 'grades.subject_id', '=', 'subjects.id')
            ->selectRaw('subjects.name as subject, AVG(CAST(grades.grade AS REAL)) as avg_grade, MAX(CAST(grades.grade AS REAL)) as max_grade')
            ->groupBy('subjects.id', 'subjects.name')
            ->orderBy('subjects.name')
            ->get()
            ->map(fn ($subject) => [
                'subject' => $subject->subject,
                'avgGrade' => round($subject->avg_grade, 2),
                'maxGrade' => (int) $subject->max_grade,
            ])
            ->values();

        return response()->json([
            'subjectPerformance' => $subjectPerformance,
        ]);
    }
}
