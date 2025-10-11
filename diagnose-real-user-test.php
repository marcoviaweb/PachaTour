<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\Attraction;
use Illuminate\Support\Facades\Auth;

echo "🔍 DIAGNÓSTICO COMPLETO - USUARIO REAL\n";
echo "====================================\n\n";

// 1. Verificar usuario Juan Pérez
echo "1️⃣ Verificando usuario Juan Pérez:\n";
$user = User::where('email', 'pachatour@yopmail.com')->first();

if ($user) {
    echo "   ✅ Usuario encontrado: {$user->name} (ID: {$user->id})\n";
    echo "   - Email: {$user->email}\n";
    echo "   - Activo: " . ($user->is_active ?? 'campo no existe') . "\n";
    echo "   - Creado: {$user->created_at}\n";
} else {
    echo "   ❌ Usuario no encontrado\n";
    
    // Buscar usuarios similares
    echo "   - Buscando usuarios similares:\n";
    $similarUsers = User::where('name', 'LIKE', '%Juan%')->orWhere('email', 'LIKE', '%juan%')->get();
    foreach ($similarUsers as $u) {
        echo "     * ID {$u->id}: {$u->name} ({$u->email})\n";
    }
    exit;
}

// 2. Verificar bookings actuales
echo "\n2️⃣ Estado actual de bookings:\n";
$totalBookings = Booking::count();
$userBookings = Booking::where('user_id', $user->id)->count();
$planningBookings = Booking::where('user_id', $user->id)
    ->whereNull('tour_schedule_id')
    ->where('notes', 'LIKE', '%PLANIFICACIÓN%')
    ->count();

echo "   - Total bookings en sistema: {$totalBookings}\n";
echo "   - Bookings de Juan Pérez: {$userBookings}\n";
echo "   - Planificaciones de Juan: {$planningBookings}\n";

if ($userBookings > 0) {
    echo "   - Últimos 3 bookings de Juan:\n";
    $recentBookings = Booking::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get(['id', 'booking_number', 'status', 'tour_schedule_id', 'created_at']);
        
    foreach ($recentBookings as $booking) {
        $type = $booking->tour_schedule_id ? 'Reserva' : 'Planificación';
        echo "     * #{$booking->booking_number} ({$type}) - {$booking->status} - {$booking->created_at}\n";
    }
}

// 3. Verificar atracciones
echo "\n3️⃣ Verificando atracciones:\n";
$attractionCount = Attraction::count();
echo "   - Total atracciones: {$attractionCount}\n";

if ($attractionCount > 0) {
    $attraction = Attraction::first();
    echo "   - Primera atracción: ID {$attraction->id} - {$attraction->name}\n";
    echo "   - Precio entrada: " . ($attraction->entry_price ?? 'No definido') . "\n";
} else {
    echo "   ❌ No hay atracciones disponibles\n";
    exit;
}

// 4. Simular exactamente lo que hace el navegador
echo "\n4️⃣ Simulando petición desde navegador:\n";

// Simular autenticación de sesión web
Auth::login($user);
echo "   - Usuario autenticado en sesión web: ✅\n";

// Crear request exactamente como lo envía el frontend
$requestData = [
    'attraction_id' => $attraction->id,
    'visit_date' => now()->addDays(7)->toDateString(),
    'visitors_count' => 2,
    'contact_name' => $user->name,
    'contact_email' => $user->email,
    'contact_phone' => '+591 70123456',
    'notes' => 'Prueba simulando navegador real',
    'estimated_total' => 280.52
];

echo "   - Datos que enviaría el navegador:\n";
foreach ($requestData as $key => $value) {
    echo "     * {$key}: {$value}\n";
}

// 5. Probar la ruta web directamente
echo "\n5️⃣ Probando ruta web /planificar-visita:\n";

try {
    // Crear request HTTP simulado
    $request = \Illuminate\Http\Request::create('/planificar-visita', 'POST', $requestData);
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    // Llamar al controlador
    $controller = new \App\Features\Tours\Controllers\BookingController(
        new \App\Features\Tours\Services\BookingService()
    );
    
    echo "   - Llamando storePlanning...\n";
    $response = $controller->storePlanning($request);
    
    echo "   - Status de respuesta: {$response->getStatusCode()}\n";
    
    if ($response->getStatusCode() === 201) {
        $data = json_decode($response->getContent(), true);
        echo "   ✅ Planificación guardada exitosamente:\n";
        echo "   - Booking ID: {$data['data']['id']}\n";
        echo "   - Número: {$data['data']['booking_number']}\n";
        
        // Verificar en BD
        $newBooking = Booking::find($data['data']['id']);
        if ($newBooking) {
            echo "   - Verificado en BD: ✅\n";
            echo "   - Participantes: {$newBooking->participants_count}\n";
            echo "   - Total: Bs {$newBooking->total_amount}\n";
        }
        
    } else {
        echo "   ❌ Error en la respuesta:\n";
        echo "   - Contenido: " . $response->getContent() . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Excepción: " . $e->getMessage() . "\n";
    echo "   - Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   - Trace: " . $e->getTraceAsString() . "\n";
}

// 6. Verificar logs de Laravel
echo "\n6️⃣ Verificando logs recientes:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -10);
    
    $hasErrors = false;
    foreach ($recentLines as $line) {
        if (str_contains($line, 'ERROR') || str_contains($line, 'Exception') || str_contains($line, 'booking')) {
            echo "   - " . substr($line, 0, 150) . "...\n";
            $hasErrors = true;
        }
    }
    
    if (!$hasErrors) {
        echo "   ✅ No hay errores recientes en los logs\n";
    }
} else {
    echo "   ⚠️  Archivo de log no encontrado\n";
}

// 7. Verificar estado final
echo "\n7️⃣ Estado final:\n";
$finalUserBookings = Booking::where('user_id', $user->id)->count();
$finalPlanningBookings = Booking::where('user_id', $user->id)
    ->whereNull('tour_schedule_id')
    ->where('notes', 'LIKE', '%PLANIFICACIÓN%')
    ->count();

echo "   - Bookings totales de Juan: {$finalUserBookings}\n";
echo "   - Planificaciones de Juan: {$finalPlanningBookings}\n";
echo "   - Incremento: " . ($finalUserBookings - $userBookings) . "\n";

// 8. Generar código de prueba para navegador
echo "\n8️⃣ Código para probar en navegador (consola F12):\n";
echo "   Copia y pega este código después de hacer login:\n\n";

$jsCode = "
// CÓDIGO DE PRUEBA COMPLETO PARA NAVEGADOR
console.log('🔍 INICIANDO PRUEBA COMPLETA DEL FORMULARIO');

// 1. Verificar autenticación
fetch('/api/auth/me', {
    headers: { 'Accept': 'application/json' },
    credentials: 'include'
}).then(r => r.json()).then(data => {
    console.log('1️⃣ Usuario autenticado:', data.user?.name || 'NO AUTENTICADO');
}).catch(e => console.log('1️⃣ Error auth:', e));

// 2. Probar ruta de planificación
const testData = {
    attraction_id: {$attraction->id},
    visit_date: '{$requestData['visit_date']}',
    visitors_count: 2,
    contact_name: '{$user->name}',
    contact_email: '{$user->email}',
    contact_phone: '+591 70123456',
    notes: 'Prueba desde consola del navegador',
    estimated_total: 280.52
};

console.log('2️⃣ Datos a enviar:', testData);

fetch('/planificar-visita', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content'),
        'Accept': 'application/json'
    },
    credentials: 'include',
    body: JSON.stringify(testData)
}).then(async response => {
    console.log('3️⃣ Status:', response.status);
    const result = await response.json();
    console.log('3️⃣ Respuesta:', result);
    
    if (response.ok) {
        console.log('✅ ¡PLANIFICACIÓN GUARDADA!');
        console.log('   - ID:', result.data?.id);
        console.log('   - Número:', result.data?.booking_number);
    } else {
        console.log('❌ ERROR:', result.message);
        console.log('   - Errores:', result.errors);
    }
}).catch(error => {
    console.error('❌ Error de red:', error);
});

console.log('🔍 Prueba iniciada - revisa los resultados arriba');
";

echo $jsCode;

echo "\n\n" . str_repeat("=", 60) . "\n";
echo "🎯 INSTRUCCIONES PARA EL USUARIO:\n\n";
echo "1. Ir a: http://127.0.0.1:8000/login\n";
echo "2. Iniciar sesión con:\n";
echo "   - Email: pachatour@yopmail.com\n";
echo "   - Password: [la contraseña correcta]\n\n";
echo "3. Abrir consola del navegador (F12)\n";
echo "4. Copiar y pegar el código JavaScript de arriba\n";
echo "5. Presionar Enter y revisar los resultados\n\n";

echo "💡 Si el código JavaScript funciona pero el formulario no,\n";
echo "   el problema está en el componente Vue.js o en Inertia.js\n\n";

echo "🔧 Si nada funciona, revisar:\n";
echo "   - Que el usuario esté realmente autenticado\n";
echo "   - Que el CSRF token esté presente\n";
echo "   - Los logs de Laravel en storage/logs/laravel.log\n";
echo "\n" . str_repeat("=", 60) . "\n";