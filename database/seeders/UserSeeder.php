<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'name' => 'Admin',
            'last_name' => 'Pacha Tour',
            'email' => 'admin@pachatour.bo',
            'role' => User::ROLE_ADMIN,
            'phone' => '+591 2 123-4567',
            'nationality' => 'Boliviana',
            'country' => 'Bolivia',
            'city' => 'La Paz',
            'preferred_language' => 'es',
            'interests' => ['Administración', 'Turismo', 'Cultura'],
            'bio' => 'Administrador principal de Pacha Tour, plataforma de turismo de Bolivia.',
            'newsletter_subscription' => true,
            'marketing_emails' => true,
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
        ]);

        // Crear usuario turista de prueba
        User::create([
            'name' => 'María',
            'last_name' => 'González',
            'email' => 'maria@example.com',
            'role' => User::ROLE_TOURIST,
            'phone' => '+591 7 987-6543',
            'birth_date' => '1990-05-15',
            'gender' => 'female',
            'nationality' => 'Argentina',
            'country' => 'Argentina',
            'city' => 'Buenos Aires',
            'preferred_language' => 'es',
            'interests' => ['Naturaleza', 'Fotografía', 'Aventura', 'Cultura'],
            'bio' => 'Amante de los viajes y la fotografía de naturaleza. Especializada en turismo de aventura.',
            'newsletter_subscription' => true,
            'marketing_emails' => false,
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Crear usuario visitante
        User::create([
            'name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'role' => User::ROLE_VISITOR,
            'nationality' => 'Estadounidense',
            'country' => 'Estados Unidos',
            'city' => 'New York',
            'preferred_language' => 'en',
            'interests' => ['Historia', 'Arqueología', 'Cultura'],
            'newsletter_subscription' => false,
            'marketing_emails' => false,
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Crear usuarios adicionales con factory
        $this->command->info('Creando usuarios adicionales...');
        
        // 5 usuarios bolivianos
        User::factory()
            ->count(5)
            ->bolivian()
            ->tourist()
            ->create();

        // 10 usuarios internacionales
        User::factory()
            ->count(10)
            ->international()
            ->create();

        // 5 usuarios visitantes
        User::factory()
            ->count(5)
            ->state(['role' => User::ROLE_VISITOR])
            ->create();

        $this->command->info('✅ Creados usuarios de prueba (admin + turistas + visitantes)');
    }
}
