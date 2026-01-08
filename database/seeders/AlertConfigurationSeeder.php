<?php

namespace Database\Seeders;

use App\Models\AlertConfiguration;
use Illuminate\Database\Seeder;

class AlertConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'name' => 'Absence Threshold Alert',
                'description' => 'Alert guardians when a student reaches the absence threshold',
                'alert_type' => 'absence_threshold',
                'condition' => ['threshold' => 3],
                'notification_channels' => ['email'],
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'name' => 'Consecutive Absence Alert',
                'description' => 'Alert guardians when a student is absent for consecutive days',
                'alert_type' => 'consecutive_absence',
                'condition' => ['consecutive_days' => 2],
                'notification_channels' => ['email'],
                'is_active' => true,
                'priority' => 2,
            ],
            [
                'name' => 'Pattern Detection Alert',
                'description' => 'Alert when unusual attendance patterns are detected',
                'alert_type' => 'pattern_detected',
                'condition' => ['pattern_type' => 'frequent_monday_friday'],
                'notification_channels' => ['email'],
                'is_active' => true,
                'priority' => 3,
            ],
            [
                'name' => 'Daily Digest',
                'description' => 'Send daily summary of absences to guardians',
                'alert_type' => 'daily_digest',
                'condition' => ['include_all' => true],
                'notification_channels' => ['email'],
                'is_active' => false,
                'priority' => 4,
            ],
        ];

        foreach ($configs as $config) {
            AlertConfiguration::create($config);
        }
    }
}
