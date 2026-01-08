<?php

namespace Database\Factories;

use App\Models\AlertConfiguration;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertLogFactory extends Factory
{
    public function definition(): array
    {
        $sent = fake()->boolean(70);

        return [
            'alert_config_id' => AlertConfiguration::factory(),
            'student_id' => Student::factory(),
            'guardian_id' => Guardian::factory(),
            'alert_type' => fake()->randomElement([
                'absence_threshold',
                'consecutive_absence',
                'pattern_detected',
                'daily_digest',
            ]),
            'message' => fake()->sentence(),
            'data' => [
                'absences_count' => fake()->numberBetween(1, 5),
                'date' => fake()->date(),
            ],
            'status' => $sent ? 'sent' : 'pending',
            'sent_at' => $sent ? now() : null,
            'acknowledged_at' => fake()->boolean(50) ? now() : null,
        ];
    }
}
