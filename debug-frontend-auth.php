<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "🔐 DEBUG DE AUTENTICACIÓN FRONTEND\n";
echo "==================================\n\n";

// 1. Verificar usuario
echo "1️⃣ Verificando usuario Juan Pérez:\n";
$user = User::where('email', 'pachatour@yopmail.com')->first();

if ($user) {
    echo "   ✅ Usuario encontrado: {$user->name} (ID: {$user->id})\n";
    echo "   - Email: {$user->email}\n";
    echo "   - Creado: {$user->created_at}\n";
} else {
    echo "   ❌ Usuario no encontrado\n";
    exit;
}

// 2. Verificar configuración de autenticación
echo "\n2️⃣ Verificando configuración de autenticación:\n";

$authConfig = config('auth');
echo "   - Guard por defecto: {$authConfig['defaults']['guard']}\n";
echo "   - Provider por defecto: {$authConfig['defaults']['passwords']}\n";

$guards = $authConfig['guards'];
foreach ($guards as $guardName => $guardConfig) {
    echo "   - Guard '{$guardName}': driver={$guardConfig['driver']}, provider={$guardConfig['provider']}\n";
}

// 3. Verificar middleware en rutas API
echo "\n3️⃣ Verificando middleware en rutas API:\n";

try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'api/bookings/plan') && in_array('POST', $route->methods())) {
            echo "   ✅ Ruta encontrada: POST /{$route->uri()}\n";
            echo "   - Middleware: " . implode(', ', $route->middleware()) . "\n";
            echo "   - Acción: {$route->getActionName()}\n";
            break;
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error verificando rutas: " . $e->getMessage() . "\n";
}

// 4. Generar código JavaScript mejorado para debugging
echo "\n4️⃣ Código JavaScript para debugging en navegador:\n";
echo "   Copia este código en la consola del navegador:\n\n";

$jsCode = "
// Código de debugging para autenticación
const debugAuth = async () => {
    console.log('🔐 DEBUGGING AUTENTICACIÓN');
    console.log('========================');
    
    // 1. Verificar CSRF token
    const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content');
    console.log('1️⃣ CSRF Token:', csrfToken ? '✅ Encontrado' : '❌ No encontrado');
    
    // 2. Verificar si el usuario está autenticado
    try {
        const authResponse = await fetch('/api/auth/me', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include'
        });
        
        console.log('2️⃣ Estado de autenticación:', authResponse.status);
        
        if (authResponse.ok) {
            const userData = await authResponse.json();
            console.log('   ✅ Usuario autenticado:', userData.user?.name);
            console.log('   - Email:', userData.user?.email);
        } else {
            console.log('   ❌ Usuario no autenticado');
            console.log('   - Respuesta:', await authResponse.text());
        }
    } catch (error) {
        console.log('   ❌ Error verificando auth:', error);
    }
    
    // 3. Probar API de planificación con debugging
    console.log('3️⃣ Probando API de planificación...');
    
    const planData = {
        attraction_id: 1,
        visit_date: '2025-10-18',
        visitors_count: 2,
        contact_name: 'Juan Perez',
        contact_email: 'pachatour@yopmail.com',
        contact_phone: '+591 70123456',
        notes: 'Prueba con debugging completo',
        estimated_total: 280.52
    };
    
    console.log('   - Datos a enviar:', planData);
    
    try {
        const response = await fetch('/api/bookings/plan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(planData)
        });
        
        console.log('   - Status de respuesta:', response.status);
        console.log('   - Headers de respuesta:', Object.fromEntries(response.headers.entries()));
        
        const result = await response.json();
        console.log('   - Respuesta completa:', result);
        
        if (response.ok) {
            console.log('   ✅ Planificación guardada exitosamente!');
            console.log('   - Booking ID:', result.data?.id);
            console.log('   - Número:', result.data?.booking_number);
        } else {
            console.log('   ❌ Error en la API:');
            console.log('   - Mensaje:', result.message);
            console.log('   - Errores:', result.errors);
        }
    } catch (error) {
        console.error('   ❌ Error de red:', error);
    }
    
    console.log('========================');
    console.log('🎯 Debugging completado');
};

// Ejecutar debugging
debugAuth();
";

echo $jsCode;

// 5. Verificar si hay problemas con Sanctum
echo "\n5️⃣ Verificando configuración de Sanctum:\n";

try {
    $sanctumConfig = config('sanctum');
    echo "   - Stateful domains: " . implode(', ', $sanctumConfig['stateful'] ?? []) . "\n";
    echo "   - Guard: " . ($sanctumConfig['guard'][0] ?? 'no configurado') . "\n";
    echo "   - Middleware: " . implode(', ', $sanctumConfig['middleware']['verify_csrf_token'] ?? []) . "\n";
} catch (Exception $e) {
    echo "   ❌ Error leyendo config de Sanctum: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 INSTRUCCIONES PARA DEBUGGING:\n\n";
echo "1. Ir a: http://127.0.0.1:8000/login\n";
echo "2. Iniciar sesión con: pachatour@yopmail.com\n";
echo "3. Abrir consola del navegador (F12)\n";
echo "4. Copiar y pegar el código JavaScript de arriba\n";
echo "5. Analizar los resultados paso a paso\n\n";

echo "💡 POSIBLES PROBLEMAS:\n";
echo "- CSRF token no se está enviando correctamente\n";
echo "- Usuario no está autenticado en la sesión web\n";
echo "- Middleware de Sanctum no está funcionando\n";
echo "- Problema con cookies de sesión\n";
echo "\n" . str_repeat("=", 50) . "\n";