<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\Attraction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "🌐 PRUEBA DE API DESDE FRONTEND\n";
echo "==============================\n\n";

// 1. Obtener usuario
echo "1️⃣ Obteniendo usuario Juan Pérez:\n";
$user = User::where('email', 'pachatour@yopmail.com')->first();

if ($user) {
    echo "   ✅ Usuario encontrado: {$user->name} (ID: {$user->id})\n";
} else {
    echo "   ❌ Usuario no encontrado\n";
    exit;
}

// 2. Obtener atracción
echo "\n2️⃣ Obteniendo atracción:\n";
$attraction = Attraction::first();

if ($attraction) {
    echo "   ✅ Atracción encontrada: {$attraction->name} (ID: {$attraction->id})\n";
} else {
    echo "   ❌ No hay atracciones disponibles\n";
    exit;
}

// 3. Simular request desde frontend
echo "\n3️⃣ Simulando request desde frontend:\n";

// Datos que envía el frontend
$frontendData = [
    'user_id' => $user->id,
    'attraction_id' => $attraction->id,
    'attraction_name' => $attraction->name,
    'visit_date' => now()->addDays(7)->toDateString(),
    'visitors_count' => 2,
    'contact_name' => $user->name,
    'contact_email' => $user->email,
    'contact_phone' => '+591 70123456',
    'notes' => 'Prueba desde frontend simulado',
    'estimated_total' => 280.52,
    'status' => 'planned'
];

echo "   - Datos enviados desde frontend:\n";
foreach ($frontendData as $key => $value) {
    echo "     * {$key}: {$value}\n";
}

// 4. Probar API storePlanning directamente
echo "\n4️⃣ Probando API storePlanning:\n";

try {
    // Autenticar usuario
    Auth::login($user);
    
    $controller = new \App\Features\Tours\Controllers\BookingController(
        new \App\Features\Tours\Services\BookingService()
    );
    
    // Crear request con datos del frontend
    $request = new Request();
    $request->merge([
        'attraction_id' => $frontendData['attraction_id'],
        'visit_date' => $frontendData['visit_date'],
        'visitors_count' => $frontendData['visitors_count'],
        'contact_name' => $frontendData['contact_name'],
        'contact_email' => $frontendData['contact_email'],
        'contact_phone' => $frontendData['contact_phone'],
        'notes' => $frontendData['notes'],
        'estimated_total' => $frontendData['estimated_total'],
    ]);
    
    echo "   - Llamando storePlanning con datos del frontend...\n";
    
    $response = $controller->storePlanning($request);
    $data = json_decode($response->getContent(), true);
    
    if ($response->getStatusCode() === 201) {
        echo "   ✅ API storePlanning funciona correctamente:\n";
        echo "   - Status: {$response->getStatusCode()}\n";
        echo "   - Mensaje: {$data['message']}\n";
        echo "   - Booking ID: {$data['data']['id']}\n";
        echo "   - Número: {$data['data']['booking_number']}\n";
        
        // Verificar en BD
        $booking = Booking::find($data['data']['id']);
        echo "   - Verificado en BD: ✅\n";
        echo "   - Participantes: {$booking->participants_count}\n";
        echo "   - Total: Bs {$booking->total_amount}\n";
        
    } else {
        echo "   ❌ Error en API:\n";
        echo "   - Status: {$response->getStatusCode()}\n";
        echo "   - Respuesta: " . $response->getContent() . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Excepción: " . $e->getMessage() . "\n";
    echo "   - Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// 5. Verificar middleware de autenticación
echo "\n5️⃣ Verificando middleware de autenticación:\n";

try {
    // Simular request sin autenticación
    Auth::logout();
    
    $request = new Request();
    $request->merge([
        'attraction_id' => $attraction->id,
        'visit_date' => now()->addDays(7)->toDateString(),
        'visitors_count' => 2,
        'contact_name' => 'Test User',
        'contact_email' => 'test@example.com',
        'contact_phone' => '+591 70123456',
        'notes' => 'Prueba sin autenticación',
        'estimated_total' => 280.52,
    ]);
    
    $response = $controller->storePlanning($request);
    
    if ($response->getStatusCode() === 401) {
        echo "   ✅ Middleware de autenticación funciona (rechaza sin login)\n";
    } else {
        echo "   ⚠️  Middleware permite acceso sin autenticación\n";
        echo "   - Status: {$response->getStatusCode()}\n";
    }
    
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Unauthenticated') || str_contains($e->getMessage(), 'auth')) {
        echo "   ✅ Middleware de autenticación funciona (excepción de auth)\n";
    } else {
        echo "   ❌ Error inesperado: " . $e->getMessage() . "\n";
    }
}

// 6. Generar código JavaScript para probar en navegador
echo "\n6️⃣ Código JavaScript para probar en navegador:\n";
echo "   Copia este código en la consola del navegador (F12):\n\n";

$jsCode = "
// Código para probar en consola del navegador
const testPlanningAPI = async () => {
    const data = {
        attraction_id: {$attraction->id},
        visit_date: '{$frontendData['visit_date']}',
        visitors_count: 2,
        contact_name: '{$user->name}',
        contact_email: '{$user->email}',
        contact_phone: '+591 70123456',
        notes: 'Prueba desde consola del navegador',
        estimated_total: 280.52
    };
    
    try {
        const response = await fetch('/api/bookings/plan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content'),
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        console.log('Status:', response.status);
        console.log('Response:', result);
        
        if (response.ok) {
            console.log('✅ Planificación guardada exitosamente!');
        } else {
            console.log('❌ Error:', result.message);
        }
    } catch (error) {
        console.error('❌ Error de red:', error);
    }
};

// Ejecutar la prueba
testPlanningAPI();
";

echo $jsCode;

echo "\n\n" . str_repeat("=", 50) . "\n";
echo "🎯 INSTRUCCIONES PARA PROBAR EN NAVEGADOR:\n\n";
echo "1. Ir a: http://127.0.0.1:8000/login\n";
echo "2. Iniciar sesión con: pachatour@yopmail.com\n";
echo "3. Abrir consola del navegador (F12)\n";
echo "4. Copiar y pegar el código JavaScript de arriba\n";
echo "5. Presionar Enter para ejecutar\n";
echo "6. Ver resultado en la consola\n\n";

echo "💡 Si funciona en consola pero no en el formulario,\n";
echo "   el problema está en el componente Vue.js\n";
echo "\n" . str_repeat("=", 50) . "\n";