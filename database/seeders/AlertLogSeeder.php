<?php

namespace Database\Seeders;

use App\Models\AlertConfiguration;
use App\Models\AlertLog;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Database\Seeder;

class AlertLogSeeder extends Seeder
{
    public function run(): void
    {
        $configs = AlertConfiguration::all();
        $students = Student::all();
        $guardians = Guardian::all();

        if ($configs->isEmpty() || $students->isEmpty()) {
            return;
        }

        foreach ($students->take(3) as $student) {
            AlertLog::create([
                'alert_config_id' => $configs->where('alert_type', 'absence_threshold')->first()?->id ?? $configs->first()->id,
                'student_id' => $student->id,
                'guardian_id' => $student->guardian_id ?? $guardians->first()?->id,
                'alert_type' => 'absence_threshold',
                'message' => "Alert: {$student->first_name} {$student->last_name} has reached the absence threshold.",
                'data' => [
                    'absences_count' => 3,
                    'date' => now()->format('Y-m-d'),
                ],
                'status' => 'sent',
                'sent_at' => now()->subHours(2),
                'acknowledged_at' => fake()->boolean(50) ? now()->subHour() : null,
            ]);

            AlertLog::create([
                'alert_config_id' => $configs->where('alert_type', 'consecutive_absence')->first()?->id ?? $configs->first()->id,
                'student_id' => $student->id,
                'guardian_id' => $student->guardian_id ?? $guardians->first()?->id,
                'alert_type' => 'consecutive_absence',
                'message' => "Alert: {$student->first_name} {$student->last_name} has been absent for 2 consecutive days.",
                'data' => [
                    'consecutive_days' => 2,
                    'dates' => [now()->subDay()->format('Y-m-d'), now()->format('Y-m-d')],
                ],
                'status' => 'sent',
                'sent_at' => now()->subHours(4),
                'acknowledged_at' => fake()->boolean(30) ? now()->subHours(3) : null,
            ]);
        }
    }
}
