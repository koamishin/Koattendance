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

    Route::get('dashboard/grades', function () {
        return Inertia::render('Dashboard/Grades');
    })->name('dashboard.grades');

    Route::get('api/grades', [\App\Http\Controllers\GradeController::class, 'index'])->name('api.grades');
    Route::get('api/attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('api.attendance');
    Route::get('api/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'getStats'])->name('api.dashboard.stats');
    Route::get('api/dashboard/attendance-summary', [\App\Http\Controllers\DashboardController::class, 'getAttendanceSummary'])->name('api.dashboard.attendance-summary');
    Route::get('api/dashboard/grade-summary', [\App\Http\Controllers\DashboardController::class, 'getGradeSummary'])->name('api.dashboard.grade-summary');
    Route::post('attendance/{id}/update-status', [\App\Http\Controllers\AttendanceController::class, 'updateStatus'])->name('attendance.updateStatus');
});

require __DIR__.'/settings.php';
