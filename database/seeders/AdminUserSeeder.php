<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        $adminUser = User::where('email', 'admin@pachatour.com')->first();

        if (!$adminUser) {
            User::create([
                'name' => 'Administrador PachaTour',
                'email' => 'admin@pachatour.com',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]);

            $this->command->info('Usuario administrador creado: admin@pachatour.com / admin123');
        } else {
            // Update existing admin user password
            $adminUser->update([
                'password' => Hash::make('admin123'),
                'name' => 'Administrador PachaTour',
                'email_verified_at' => now(),
                'role' => 'admin',
            ]);
            $this->command->info('Usuario administrador actualizado: admin@pachatour.com / admin123');
        }

        // Also update first user to be admin if exists and is different
        $firstUser = User::first();
        if ($firstUser && $firstUser->email !== 'admin@pachatour.com') {
            $firstUser->update([
                'email' => 'admin@pachatour.com',
                'name' => 'Administrador PachaTour',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'role' => 'admin',
            ]);
            $this->command->info('Primer usuario actualizado a administrador');
        }
    }
}
