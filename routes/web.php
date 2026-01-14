<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard/Index');
    })->name('dashboard');

    Route::get('dashboard/seatplan', function () {
        return Inertia::render('Dashboard/Seatplan');
    })->name('dashboard.seatplan');

    Route::get('dashboard/attendance', function () {
        return Inertia::render('Dashboard/Attendance');
    })->name('dashboard.attendance');

    Route::get('dashboard/subjects', function () {
        return Inertia::render('Dashboard/Subjects/Index');
    })->name('dashboard.subjects');

    Route::get('dashboard/subjects/{id}', [\App\Http\Controllers\SubjectController::class, 'show'])->name('dashboard.subjects.show');

    Route::get('dashboard/grades', [\App\Http\Controllers\GradeController::class, 'index'])->name('dashboard.grades');
    Route::patch('dashboard/grades/{grade}', [\App\Http\Controllers\GradeController::class, 'update'])->name('grades.update');
    Route::post('api/students', [\App\Http\Controllers\StudentController::class, 'store'])->name('api.students.store');
    Route::get('api/students', [\App\Http\Controllers\StudentController::class, 'index'])->name('api.students');
    Route::get('api/seating', [\App\Http\Controllers\StudentController::class, 'getSeating'])->name('api.seating');
    Route::post('api/seating', [\App\Http\Controllers\StudentController::class, 'updateSeating'])->name('api.seating.update');
    Route::post('api/seating/grid', [\App\Http\Controllers\StudentController::class, 'updateGridDimensions'])->name('api.seating.grid');
    Route::get('api/attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('api.attendance');
    Route::get('api/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'getStats'])->name('api.dashboard.stats');
    Route::get('api/dashboard/attendance-summary', [\App\Http\Controllers\DashboardController::class, 'getAttendanceSummary'])->name('api.dashboard.attendance-summary');
    Route::get('api/dashboard/grade-summary', [\App\Http\Controllers\DashboardController::class, 'getGradeSummary'])->name('api.dashboard.grade-summary');
    Route::get('api/dashboard/weekly-attendance', [\App\Http\Controllers\DashboardController::class, 'getWeeklyAttendance'])->name('api.dashboard.weekly-attendance');
    Route::get('api/dashboard/subject-performance', [\App\Http\Controllers\DashboardController::class, 'getSubjectPerformance'])->name('api.dashboard.subject-performance');
    Route::post('attendance/{id}/update-status', [\App\Http\Controllers\AttendanceController::class, 'updateStatus'])->name('attendance.updateStatus');
    
    // Attendance Management
    Route::post('api/attendance/enroll', [\App\Http\Controllers\AttendanceController::class, 'enroll'])->name('api.attendance.enroll');
    Route::get('api/attendance/search-students', [\App\Http\Controllers\AttendanceController::class, 'searchStudents'])->name('api.attendance.search-students');

    // Subject Management
    Route::apiResource('api/subjects', \App\Http\Controllers\Api\SubjectController::class);

    // QR Code Attendance Routes
    Route::get('api/attendance/sessions/today', [\App\Http\Controllers\Api\ClassSessionController::class, 'today'])->name('api.sessions.today');
    Route::post('api/attendance/sessions/start', [\App\Http\Controllers\Api\ClassSessionController::class, 'startForSubject'])->name('api.sessions.start');
    Route::post('api/attendance/sessions/{session}/end', [\App\Http\Controllers\Api\ClassSessionController::class, 'endSession'])->name('api.sessions.end');
    Route::get('api/attendance/sessions/{session}/status', [\App\Http\Controllers\Api\ClassSessionController::class, 'status'])->name('api.sessions.status');
    Route::post('api/attendance/scan', [\App\Http\Controllers\Api\AttendanceScanController::class, 'scan'])->name('api.attendance.scan');
    Route::get('api/students/{student}/qr-code', [\App\Http\Controllers\Api\StudentQrController::class, 'show'])->name('api.students.qr-code');
    Route::post('api/students/{student}/regenerate-qr', [\App\Http\Controllers\Api\StudentQrController::class, 'regenerate'])->name('api.students.regenerate-qr');
    Route::get('api/subjects/{subject}/students/qr-codes', [\App\Http\Controllers\Api\SubjectController::class, 'studentsWithQrCodes'])->name('api.subjects.students-qr-codes');
});

require __DIR__.'/settings.php';
