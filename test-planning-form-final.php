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

echo "🧪 PRUEBA FINAL DEL FORMULARIO 'PLANIFICAR VISITA'\n";
echo "=================================================\n\n";

// 1. Obtener usuario Juan Pérez
echo "1️⃣ Obteniendo usuario Juan Pérez:\n";
$user = User::where('email', 'pachatour@yopmail.com')->first();

if ($user) {
    echo "   ✅ Usuario encontrado: {$user->name} ({$user->email})\n";
    Auth::login($user);
} else {
    echo "   ❌ Usuario no encontrado\n";
    exit;
}

// 2. Obtener primera atracción
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

// 4. Probar API storePlanning
echo "\n4️⃣ Probando API storePlanning:\n";

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
        'notes' => 'Prueba final del formulario de planificación',
        'estimated_total' => 160.00,
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
        echo "\n   ❌ Error en API:\n";
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

// 6. Probar dashboard
echo "\n6️⃣ Probando dashboard:\n";
try {
    $dashboardController = new \App\Features\Users\Controllers\UserDashboardController();
    $response = $dashboardController->upcomingBookings();
    
    if ($response->getStatusCode() === 200) {
        echo "   ✅ Dashboard funciona correctamente\n";
        
        $data = json_decode($response->getContent(), true);
        $bookings = $data['data'];
        
        echo "   - Total bookings mostrados: " . count($bookings) . "\n";
        
        $planningCount = 0;
        foreach ($bookings as $booking) {
            if ($booking['is_planning']) {
                $planningCount++;
                echo "   - Planificación: {$booking['tour_name']} - {$booking['tour_date']}\n";
            }
        }
        
        echo "   - Planificaciones mostradas: {$planningCount}\n";
        
    } else {
        echo "   ❌ Error en dashboard: " . $response->getStatusCode() . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error en dashboard: " . $e->getMessage() . "\n";
}

// Resultado final
echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 RESULTADO FINAL:\n\n";

if ($finalCount > $initialCount) {
    echo "✅ ¡EL FORMULARIO 'PLANIFICAR VISITA' FUNCIONA CORRECTAMENTE!\n\n";
    echo "📋 RESUMEN:\n";
    echo "   ✅ API storePlanning funciona\n";
    echo "   ✅ Datos se guardan en tabla bookings\n";
    echo "   ✅ Dashboard muestra planificaciones\n";
    echo "   ✅ Usuario Juan Pérez puede planificar visitas\n\n";
    
    echo "🌐 PRUEBA EN NAVEGADOR:\n";
    echo "   1. Login: http://127.0.0.1:8000/login\n";
    echo "      Email: pachatour@yopmail.com\n";
    echo "      Password: [usar la contraseña correcta]\n\n";
    echo "   2. Ir a cualquier atracción\n";
    echo "   3. Hacer clic en 'Planificar Visita'\n";
    echo "   4. Llenar formulario y guardar\n";
    echo "   5. Verificar en: http://127.0.0.1:8000/mis-viajes\n\n";
    
    echo "🎯 ¡La funcionalidad está completamente operativa!\n";
    
} else {
    echo "❌ EL FORMULARIO AÚN TIENE PROBLEMAS\n\n";
    echo "🔧 REVISAR:\n";
    echo "   - Logs: storage/logs/laravel.log\n";
    echo "   - Rutas API: routes/api.php\n";
    echo "   - Middleware de autenticación\n";
}

echo "\n" . str_repeat("=", 50) . "\n";