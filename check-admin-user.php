<?php
// check-admin-user.php
// Verificar que existe un usuario admin

use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICACIÓN USUARIO ADMIN ===\n\n";

// Buscar usuario admin
$adminUser = User::where('role', 'admin')->first();

if ($adminUser) {
    echo "✅ Usuario admin encontrado:\n";
    echo "   ID: " . $adminUser->id . "\n";
    echo "   Email: " . $adminUser->email . "\n";
    echo "   Nombre: " . $adminUser->name . "\n";
    echo "   Rol: " . $adminUser->role . "\n\n";
    
    echo "📧 Para probar, usa estas credenciales:\n";
    echo "   Email: " . $adminUser->email . "\n";
    echo "   Password: password (por defecto)\n\n";
} else {
    echo "❌ No se encontró usuario admin\n";
    echo "Creando usuario admin...\n\n";
    
    $adminUser = User::create([
        'name' => 'Administrador',
        'email' => 'admin@pachatour.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    
    echo "✅ Usuario admin creado:\n";
    echo "   Email: " . $adminUser->email . "\n";
    echo "   Password: password\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";