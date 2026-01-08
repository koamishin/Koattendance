<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AlertConfigurationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Threshold Alert',
                'Consecutive Absence Alert',
                'Pattern Detection',
                'Daily Digest',
            ]),
            'description' => fake()->sentence(),
            'alert_type' => fake()->randomElement([
                'absence_threshold',
                'consecutive_absence',
                'pattern_detected',
                'daily_digest',
            ]),
            'condition' => [
                'threshold' => fake()->numberBetween(2, 5),
                'consecutive_days' => fake()->numberBetween(2, 4),
            ],
            'notification_channels' => ['email'],
            'is_active' => true,
            'priority' => fake()->numberBetween(1, 5),
        ];
    }
}
