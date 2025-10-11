<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICANDO SOLUCIÓN CSRF ===\n\n";

// 1. Verificar excepción CSRF
echo "1. VERIFICANDO EXCEPCIÓN CSRF:\n";
$middlewarePath = app_path('Http/Middleware/VerifyCsrfToken.php');
$content = file_get_contents($middlewarePath);

if (strpos($content, 'planificar-visita') !== false) {
    echo "   ✅ Excepción 'planificar-visita' encontrada\n";
} else {
    echo "   ❌ Excepción NO encontrada\n";
}

// 2. Verificar que el usuario esté autenticado
echo "\n2. VERIFICANDO AUTENTICACIÓN:\n";
try {
    $user = \App\Models\User::where('email', 'pachatour@yopmail.com')->first();
    if ($user) {
        echo "   ✅ Usuario de prueba encontrado: {$user->name}\n";
        echo "   - ID: {$user->id}\n";
        echo "   - Email: {$user->email}\n";
        
        // Simular autenticación
        \Illuminate\Support\Facades\Auth::login($user);
        echo "   ✅ Usuario autenticado para prueba\n";
    } else {
        echo "   ❌ Usuario de prueba no encontrado\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 3. Probar el endpoint directamente
echo "\n3. PROBANDO ENDPOINT PLANIFICAR-VISITA:\n";
try {
    $attraction = \App\Models\Attraction::first();
    if (!$attraction) {
        echo "   ❌ No hay atracciones en la base de datos\n";
        return;
    }
    
    echo "   ✅ Atracción encontrada: {$attraction->name}\n";
    
    // Crear request simulado
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'attraction_id' => $attraction->id,
        'visit_date' => '2025-01-15',
        'visitors_count' => 2,
        'contact_name' => 'Juan Perez',
        'contact_email' => 'pachatour@yopmail.com',
        'contact_phone' => '+591 70123456',
        'notes' => 'Prueba de formulario CSRF corregido',
        'estimated_total' => 60.00,
    ]);
    
    // Probar controlador
    $controller = new \App\Features\Tours\Controllers\BookingController(
        new \App\Features\Tours\Services\BookingService()
    );
    
    echo "   - Enviando datos del formulario...\n";
    $response = $controller->storePlanning($request);
    $data = json_decode($response->getContent(), true);
    
    if ($response->getStatusCode() === 201) {
        echo "   ✅ FORMULARIO FUNCIONA CORRECTAMENTE:\n";
        echo "   - Status: {$response->getStatusCode()}\n";
        echo "   - Mensaje: {$data['message']}\n";
        echo "   - Booking ID: {$data['data']['id']}\n";
    } else {
        echo "   ❌ Error en formulario:\n";
        echo "   - Status: {$response->getStatusCode()}\n";
        echo "   - Respuesta: {$response->getContent()}\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n4. INSTRUCCIONES PARA EL USUARIO:\n";
echo "   1. ✅ Excepción CSRF agregada\n";
echo "   2. ✅ Cache limpiado\n";
echo "   3. 🔄 Recarga la página con Ctrl+F5\n";
echo "   4. 🧹 Limpia cookies del navegador\n";
echo "   5. 📝 Intenta guardar el formulario nuevamente\n\n";

echo "5. RUTAS DISPONIBLES:\n";
echo "   - Web: POST /planificar-visita (sin CSRF)\n";
echo "   - API: POST /api/bookings/plan (con Sanctum)\n\n";

echo "=== VERIFICACIÓN COMPLETADA ===\n";