<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Buscar usuario admin
$adminUser = User::where('email', 'admin@pachatour.com')->first();

if ($adminUser) {
    // Actualizar password
    $adminUser->update([
        'password' => Hash::make('admin123'),
        'name' => 'Administrador PachaTour',
        'email_verified_at' => now(),
    ]);
    echo "Usuario admin actualizado: {$adminUser->email}\n";
} else {
    // Verificar si hay algún usuario y convertirlo en admin
    $firstUser = User::first();
    if ($firstUser) {
        $firstUser->update([
            'email' => 'admin@pachatour.com',
            'password' => Hash::make('admin123'),
            'name' => 'Administrador PachaTour',
            'email_verified_at' => now(),
        ]);
        echo "Usuario convertido a admin: {$firstUser->email}\n";
    } else {
        // Crear nuevo usuario admin
        $newAdmin = User::create([
            'name' => 'Administrador PachaTour',
            'email' => 'admin@pachatour.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
        ]);
        echo "Nuevo usuario admin creado: {$newAdmin->email}\n";
    }
}

// Mostrar todos los usuarios
echo "\nUsuarios en la base de datos:\n";
User::all(['id', 'name', 'email'])->each(function($user) {
    echo "ID: {$user->id} - Nombre: {$user->name} - Email: {$user->email}\n";
});