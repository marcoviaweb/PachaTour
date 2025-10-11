<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Features\Users\Controllers\UserDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "🧪 Probando API del Dashboard...\n\n";

// Buscar usuario Juan Pérez
$user = User::where('email', 'juan.perez@example.com')->first();

if (!$user) {
    echo "❌ Usuario Juan Pérez no encontrado\n";
    echo "Ejecuta primero: php create-test-booking.php\n";
    exit;
}

echo "✅ Usuario encontrado: {$user->name} {$user->last_name}\n";

// Simular autenticación
Auth::login($user);

echo "🔐 Usuario autenticado como: " . Auth::user()->name . "\n\n";

// Crear instancia del controlador
$controller = new UserDashboardController();

// Probar dashboardStats
echo "📊 Probando dashboardStats()...\n";
try {
    $statsResponse = $controller->dashboardStats();
    $stats = json_decode($statsResponse->getContent(), true);
    
    echo "✅ Stats obtenidas:\n";
    echo "  - Reservas activas: " . $stats['activeBookings'] . "\n";
    echo "  - Reservas completadas: " . $stats['completedBookings'] . "\n";
    echo "  - Reseñas: " . $stats['reviewsCount'] . "\n";
    echo "  - Destinos visitados: " . $stats['visitedDestinations'] . "\n";
} catch (Exception $e) {
    echo "❌ Error en dashboardStats: " . $e->getMessage() . "\n";
}

echo "\n";

// Probar upcomingBookings
echo "📅 Probando upcomingBookings()...\n";
try {
    $upcomingResponse = $controller->upcomingBookings();
    $upcoming = json_decode($upcomingResponse->getContent(), true);
    
    echo "✅ Próximas reservas obtenidas:\n";
    if (empty($upcoming['data'])) {
        echo "  - No hay reservas próximas\n";
    } else {
        foreach ($upcoming['data'] as $booking) {
            echo "  - Booking #{$booking['booking_number']}: {$booking['tour_name']} - {$booking['tour_date']}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error en upcomingBookings: " . $e->getMessage() . "\n";
}

echo "\n";

// Probar bookingHistory
echo "📚 Probando bookingHistory()...\n";
try {
    $request = new Request();
    $historyResponse = $controller->bookingHistory($request);
    $history = json_decode($historyResponse->getContent(), true);
    
    echo "✅ Historial de reservas obtenido:\n";
    if (empty($history['data'])) {
        echo "  - No hay historial de reservas\n";
    } else {
        foreach ($history['data'] as $booking) {
            echo "  - Booking #{$booking['booking_number']}: {$booking['tour_name']} - {$booking['tour_date']} ({$booking['status']})\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error en bookingHistory: " . $e->getMessage() . "\n";
}

echo "\n";

// Verificar bookings directamente en la base de datos
echo "🔍 Verificación directa en BD:\n";
$userBookings = Booking::where('user_id', $user->id)->with('tourSchedule.tour')->get();

if ($userBookings->isEmpty()) {
    echo "❌ No hay bookings en la BD para este usuario\n";
} else {
    echo "✅ Bookings encontrados en BD:\n";
    foreach ($userBookings as $booking) {
        $tour = $booking->tourSchedule->tour ?? null;
        $tourName = $tour ? $tour->name : 'Tour no encontrado';
        echo "  - ID: {$booking->id}, Tour: {$tourName}, Estado: {$booking->status}\n";
    }
}

echo "\n🎯 Diagnóstico:\n";

// Verificar relaciones
$bookingsWithRelations = Booking::where('user_id', $user->id)
    ->with(['tourSchedule.tour.attractions.department'])
    ->get();

foreach ($bookingsWithRelations as $booking) {
    echo "Booking ID {$booking->id}:\n";
    echo "  - TourSchedule: " . ($booking->tourSchedule ? "✅" : "❌") . "\n";
    if ($booking->tourSchedule) {
        echo "  - Tour: " . ($booking->tourSchedule->tour ? "✅" : "❌") . "\n";
        if ($booking->tourSchedule->tour) {
            echo "  - Attractions: " . ($booking->tourSchedule->tour->attractions->count() > 0 ? "✅" : "❌") . "\n";
        }
    }
}

echo "\n✅ Prueba completada.\n";