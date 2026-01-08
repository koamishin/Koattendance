<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class GuardianSeeder extends Seeder
{
    public function run(): void
    {
        $guardians = [
            ['first_name' => 'Mark', 'last_name' => 'Smith', 'relationship' => 'father'],
            ['first_name' => 'Linda', 'last_name' => 'Smith', 'relationship' => 'mother'],
            ['first_name' => 'Thomas', 'last_name' => 'Doe', 'relationship' => 'father'],
            ['first_name' => 'Patricia', 'last_name' => 'Doe', 'relationship' => 'mother'],
            ['first_name' => 'William', 'last_name' => 'Johnson', 'relationship' => 'father'],
        ];

        foreach ($guardians as $index => $guardianData) {
            $user = User::factory()->guardian()->create([
                'name' => "{$guardianData['first_name']} {$guardianData['last_name']}",
                'email' => strtolower($guardianData['first_name']).'.'.strtolower($guardianData['last_name']).'@email.com',
            ]);

            $guardian = new \App\Models\Guardian;
            $guardian->user_id = $user->id;
            $guardian->first_name = $guardianData['first_name'];
            $guardian->last_name = $guardianData['last_name'];
            $guardian->email = $user->email;
            $guardian->phone = fake()->phoneNumber();
            $guardian->relationship = $guardianData['relationship'];
            $guardian->is_primary = $index % 2 === 0;
            $guardian->alert_preferences = [
                'email' => true,
                'sms' => fake()->boolean(50),
                'absence_threshold' => 3,
                'consecutive_absence' => 2,
            ];
            $guardian->save();
        }
    }
}
