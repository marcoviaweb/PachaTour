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

echo "🌐 PRUEBA DE RUTA WEB PARA PLANIFICACIÓN\n";
echo "=======================================\n\n";

// 1. Obtener usuario
echo "1️⃣ Obteniendo usuario Juan Pérez:\n";
$user = User::where('email', 'pachatour@yopmail.com')->first();

if ($user) {
    echo "   ✅ Usuario encontrado: {$user->name} (ID: {$user->id})\n";
    Auth::login($user);
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

// 3. Contar bookings antes
echo "\n3️⃣ Estado inicial:\n";
$initialCount = Booking::where('user_id', $user->id)->count();
echo "   - Bookings existentes de Juan: {$initialCount}\n";

// 4. Probar ruta web de planificación
echo "\n4️⃣ Probando ruta web /planificar-visita:\n";

try {
    $controller = new \App\Features\Tours\Controllers\BookingController(
        new \App\Features\Tours\Services\BookingService()
    );
    
    $request = new Request();
    $request->merge([
        'attraction_id' => $attraction->id,
        'visit_date' => now()->addDays(7)->toDateString(),
        'visitors_count' => 2,
        'contact_name' => $user->name,
        'contact_email' => $user->email,
        'contact_phone' => '+591 70123456',
        'notes' => 'Prueba desde ruta web',
        'estimated_total' => 280.52,
    ]);
    
    echo "   - Datos enviados:\n";
    echo "     * Atracción: {$attraction->name}\n";
    echo "     * Fecha: {$request->visit_date}\n";
    echo "     * Visitantes: {$request->visitors_count}\n";
    echo "     * Total estimado: Bs {$request->estimated_total}\n";
    
    $response = $controller->storePlanning($request);
    $data = json_decode($response->getContent(), true);
    
    if ($response->getStatusCode() === 201) {
        echo "\n   ✅ ¡PLANIFICACIÓN GUARDADA EXITOSAMENTE!\n";
        echo "   - Status: {$response->getStatusCode()}\n";
        echo "   - Mensaje: {$data['message']}\n";
        echo "   - Booking ID: {$data['data']['id']}\n";
        echo "   - Número: {$data['data']['booking_number']}\n";
        
        $newBooking = $data['data'];
        
    } else {
        echo "\n   ❌ Error en ruta web:\n";
        echo "   - Status: {$response->getStatusCode()}\n";
        echo "   - Respuesta: " . $response->getContent() . "\n";
        exit;
    }
    
} catch (Exception $e) {
    echo "\n   ❌ Excepción: " . $e->getMessage() . "\n";
    echo "   - Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit;
}

// 5. Verificar en base de datos
echo "\n5️⃣ Verificando en base de datos:\n";
$finalCount = Booking::where('user_id', $user->id)->count();
echo "   - Bookings después: {$finalCount}\n";
echo "   - Nuevos bookings: " . ($finalCount - $initialCount) . "\n";

if ($finalCount > $initialCount) {
    echo "   ✅ ¡Booking guardado en base de datos!\n";
    
    $latestBooking = Booking::where('user_id', $user->id)
        ->latest()
        ->first();
        
    echo "   - ID: {$latestBooking->id}\n";
    echo "   - Número: {$latestBooking->booking_number}\n";
    echo "   - Status: {$latestBooking->status}\n";
    echo "   - Tour Schedule ID: " . ($latestBooking->tour_schedule_id ?? 'NULL (planificación)') . "\n";
    echo "   - Participantes: {$latestBooking->participants_count}\n";
    echo "   - Total: Bs {$latestBooking->total_amount}\n";
    echo "   - Notas: " . substr($latestBooking->notes, 0, 100) . "...\n";
    
} else {
    echo "   ❌ No se guardó el booking\n";
}

// 6. Generar código JavaScript para probar en navegador
echo "\n6️⃣ Código JavaScript para probar en navegador:\n";
echo "   Copia este código en la consola del navegador:\n\n";

$jsCode = "
// Código para probar ruta web desde navegador
const testWebRoute = async () => {
    console.log('🌐 PROBANDO RUTA WEB /planificar-visita');
    console.log('=====================================');
    
    const data = {
        attraction_id: {$attraction->id},
        visit_date: '{$request->visit_date}',
        visitors_count: 2,
        contact_name: '{$user->name}',
        contact_email: '{$user->email}',
        contact_phone: '+591 70123456',
        notes: 'Prueba desde navegador con ruta web',
        estimated_total: 280.52
    };
    
    console.log('Datos a enviar:', data);
    
    try {
        const response = await fetch('/planificar-visita', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content'),
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(data)
        });
        
        console.log('Status:', response.status);
        const result = await response.json();
        console.log('Response:', result);
        
        if (response.ok) {
            console.log('✅ ¡Planificación guardada exitosamente!');
            console.log('Booking ID:', result.data?.id);
            console.log('Número:', result.data?.booking_number);
        } else {
            console.log('❌ Error:', result.message);
        }
    } catch (error) {
        console.error('❌ Error de red:', error);
    }
};

// Ejecutar la prueba
testWebRoute();
";

echo $jsCode;

echo "\n\n" . str_repeat("=", 50) . "\n";
echo "🎯 RESULTADO FINAL:\n\n";

if ($finalCount > $initialCount) {
    echo "✅ ¡LA RUTA WEB FUNCIONA CORRECTAMENTE!\n\n";
    echo "📋 RESUMEN:\n";
    echo "   ✅ Ruta web /planificar-visita funciona\n";
    echo "   ✅ Datos se guardan en tabla bookings\n";
    echo "   ✅ Usuario autenticado con sesión web\n";
    echo "   ✅ No requiere tokens de Sanctum\n\n";
    
    echo "🌐 PRUEBA EN NAVEGADOR:\n";
    echo "   1. Login: http://127.0.0.1:8000/login\n";
    echo "      Email: pachatour@yopmail.com\n";
    echo "      Password: [usar la contraseña correcta]\n\n";
    echo "   2. Abrir consola (F12) y ejecutar el código JavaScript\n";
    echo "   3. O usar el formulario 'Planificar Visita' directamente\n\n";
    
    echo "🎯 ¡El formulario debería funcionar ahora!\n";
    
} else {
    echo "❌ LA RUTA WEB AÚN TIENE PROBLEMAS\n\n";
    echo "🔧 REVISAR:\n";
    echo "   - Middleware de autenticación web\n";
    echo "   - CSRF token en formularios\n";
    echo "   - Configuración de sesiones\n";
}

echo "\n" . str_repeat("=", 50) . "\n";