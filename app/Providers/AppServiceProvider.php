<?php

namespace App\Providers;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Observers\AttendanceRecordObserver;
use App\Observers\GradeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AttendanceRecord::observe(AttendanceRecordObserver::class);
        Grade::observe(GradeObserver::class);
    }
}
