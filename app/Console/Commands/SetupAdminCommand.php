<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetupAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:setup {email=admin@pachatour.com} {password=admin123}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup or update admin user credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("Configurando usuario admin...");

        // Buscar usuario admin
        $adminUser = User::where('email', $email)->first();

        if ($adminUser) {
            // Actualizar password del usuario existente
            $adminUser->update([
                'password' => Hash::make($password),
                'name' => 'Administrador PachaTour',
                'email_verified_at' => now(),
            ]);
            $this->info("Usuario admin actualizado: {$adminUser->email}");
        } else {
            // Verificar si hay algún usuario y convertirlo en admin
            $firstUser = User::first();
            if ($firstUser) {
                try {
                    $firstUser->update([
                        'email' => $email,
                        'password' => Hash::make($password),
                        'name' => 'Administrador PachaTour',
                        'email_verified_at' => now(),
                    ]);
                    $this->info("Usuario convertido a admin: {$firstUser->email}");
                } catch (\Exception $e) {
                    $this->error("Error al actualizar usuario: " . $e->getMessage());
                    return 1;
                }
            } else {
                // Crear nuevo usuario admin
                $newAdmin = User::create([
                    'name' => 'Administrador PachaTour',
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Hash::make($password),
                ]);
                $this->info("Nuevo usuario admin creado: {$newAdmin->email}");
            }
        }

        // Mostrar todos los usuarios
        $this->info("\nUsuarios en la base de datos:");
        $users = User::all(['id', 'name', 'email']);
        
        $headers = ['ID', 'Nombre', 'Email'];
        $rows = $users->map(function($user) {
            return [$user->id, $user->name, $user->email];
        })->toArray();

        $this->table($headers, $rows);

        $this->info("\nCredenciales de acceso:");
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");
        $this->info("URL Admin: " . url('/admin/dashboard'));

        return 0;
    }
}
