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
});

require __DIR__.'/settings.php';
