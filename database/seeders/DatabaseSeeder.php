<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Iniciando seeders de Pacha Tour...');
        
        // Orden importante: primero entidades base, luego dependientes
        $this->call([
            DepartmentSeeder::class,    // 1. Departamentos (base)
            UserSeeder::class,          // 2. Usuarios (base)
            AttractionSeeder::class,    // 3. Atractivos (depende de departamentos)
            TourSeeder::class,          // 4. Tours (independiente)
            TourScheduleSeeder::class,  // 5. Horarios (depende de tours)
            BookingSeeder::class,       // 6. Reservas (depende de usuarios y horarios)
            ReviewSeeder::class,        // 7. Reseñas (depende de usuarios y entidades)
        ]);

        $this->command->info('🎉 ¡Seeders completados exitosamente!');
        $this->command->info('📊 Datos creados para Pacha Tour - Plataforma de Turismo de Bolivia');
    }
}