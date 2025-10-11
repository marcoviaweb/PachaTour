<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $this->command->info('Creating user activities...');

        foreach ($users as $user) {
            // Create login activities
            \App\Models\UserActivity::factory()->count(rand(5, 15))->login()->create([
                'user_id' => $user->id
            ]);

            // Create booking activities (only for tourists)
            if ($user->role === 'tourist') {
                \App\Models\UserActivity::factory()->count(rand(2, 8))->booking()->create([
                    'user_id' => $user->id
                ]);
            }

            // Create admin activities (only for admins)
            if ($user->role === 'admin') {
                \App\Models\UserActivity::factory()->count(rand(10, 25))->adminAction()->create([
                    'user_id' => $user->id
                ]);
            }

            // Create general activities
            \App\Models\UserActivity::factory()->count(rand(3, 10))->create([
                'user_id' => $user->id
            ]);
        }

        $totalActivities = \App\Models\UserActivity::count();
        $this->command->info("Created {$totalActivities} user activities.");
    }
}
