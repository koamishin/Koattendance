<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('seatplan', function () {
    return Inertia::render('Seatplan');
})->name('seatplan');

Route::get('attendance', function () {
    return Inertia::render('Attendance');
})->name('attendance');

Route::get('grades', function () {
    return Inertia::render('Grades');
})->name('grades');

require __DIR__.'/settings.php';
