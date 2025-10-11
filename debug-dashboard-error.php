<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Features\Users\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

echo "🔍 Diagnosticando error del dashboard...\n\n";

// Verificar si la migración se ejecutó
echo "1️⃣ Verificando estructura de la tabla bookings:\n";

$columns = Schema::getColumnListing('bookings');
$hasPlanning = in_array('planning_data', $columns);

echo "   - Columna planning_data: " . ($hasPlanning ? "✅ Existe" : "❌ No existe") . "\n";

if (!$hasPlanning) {
    echo "\n❌ La migración no se ha ejecutado. Ejecuta:\n";
    echo "   php artisan migrate\n\n";
    exit;
}

// Verificar usuario
echo "\n2️⃣ Verificando usuario Juan Pérez:\n";
$user = User::where('email', 'juan.perez@example.com')->first();

if (!$user) {
    echo "❌ Usuario no encontrado\n";
    exit;
}

echo "✅ Usuario encontrado: {$user->name}\n";

// Simular autenticación
Auth::login($user);

// Verificar bookings del usuario
echo "\n3️⃣ Verificando bookings del usuario:\n";
$bookings = Booking::where('user_id', $user->id)->get();

echo "   - Total bookings: " . $bookings->count() . "\n";

foreach ($bookings as $booking) {
    echo "   - Booking #{$booking->id}: status={$booking->status}, tour_schedule_id=" . ($booking->tour_schedule_id ?? 'null') . "\n";
}

// Probar el método upcomingBookings
echo "\n4️⃣ Probando método upcomingBookings:\n";

try {
    $controller = new UserDashboardController();
    $response = $controller->upcomingBookings();
    $data = json_decode($response->getContent(), true);
    
    echo "✅ Método ejecutado exitosamente\n";
    echo "   - Bookings próximos: " . count($data['data']) . "\n";
    
    foreach ($data['data'] as $booking) {
        $type = $booking['is_planning'] ? 'Planificación' : 'Reserva';
        echo "   - {$type}: {$booking['tour_name']} - {$booking['tour_date']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en upcomingBookings:\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace:\n" . $e->getTraceAsString() . "\n";
}

// Probar dashboardStats
echo "\n5️⃣ Probando método dashboardStats:\n";

try {
    $statsResponse = $controller->dashboardStats();
    $stats = json_decode($statsResponse->getContent(), true);
    
    echo "✅ Stats obtenidas:\n";
    echo "   - Reservas activas: " . $stats['activeBookings'] . "\n";
    echo "   - Reservas completadas: " . $stats['completedBookings'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error en dashboardStats:\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
}

echo "\n✅ Diagnóstico completado.\n";