<?php
// debug-auth.php
// Script para debuggear problemas de autenticación

use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG AUTENTICACIÓN ===\n\n";

// Buscar usuario admin
$adminUser = User::where('email', 'admin@pachatour.bo')->first();

if ($adminUser) {
    echo "✅ Usuario admin encontrado:\n";
    echo "   ID: " . $adminUser->id . "\n";
    echo "   Email: " . $adminUser->email . "\n";
    echo "   Nombre: " . $adminUser->name . "\n";
    echo "   Rol: " . $adminUser->role . "\n";
    echo "   Created: " . $adminUser->created_at . "\n\n";
    
    // Verificar datos de sesión si están disponibles
    if (session()->getId()) {
        echo "📊 Estado de sesión:\n";
        echo "   Session ID: " . session()->getId() . "\n";
        echo "   Auth check: " . (auth()->check() ? 'SÍ' : 'NO') . "\n";
        
        if (auth()->check()) {
            $currentUser = auth()->user();
            echo "   Usuario actual: " . $currentUser->email . "\n";
            echo "   Es admin?: " . ($currentUser->role === 'admin' ? 'SÍ' : 'NO') . "\n";
        }
    } else {
        echo "⚠️  No hay sesión activa\n";
    }
    
    echo "\n";
    
    // Simular middleware checks
    echo "🛡️  Verificaciones de middleware:\n";
    
    // AdminMiddleware logic
    $isAdminEmail = str_contains($adminUser->email, 'admin');
    $isAdminId = $adminUser->id === 1;
    $isAdminRole = isset($adminUser->role) && $adminUser->role === 'admin';
    $isAdmin = $isAdminEmail || $isAdminId || $isAdminRole;
    
    echo "   Email contiene 'admin': " . ($isAdminEmail ? 'SÍ' : 'NO') . "\n";
    echo "   ID es 1: " . ($isAdminId ? 'SÍ' : 'NO') . "\n";
    echo "   Rol es 'admin': " . ($isAdminRole ? 'SÍ' : 'NO') . "\n";
    echo "   Resultado AdminMiddleware: " . ($isAdmin ? 'PERMITIDO' : 'DENEGADO') . "\n\n";
    
    // RoleMiddleware logic
    $hasAdminRole = $adminUser->role === 'admin';
    echo "   Resultado RoleMiddleware (admin): " . ($hasAdminRole ? 'PERMITIDO' : 'DENEGADO') . "\n\n";
    
} else {
    echo "❌ No se encontró usuario admin\n";
}

echo "=== FIN DEBUG ===\n";