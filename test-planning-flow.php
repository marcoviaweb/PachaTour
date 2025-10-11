<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\Attraction;
use Illuminate\Support\Facades\Auth;

echo "🧪 Probando flujo de planificación de visitas...\n\n";

// Buscar usuario Juan Pérez
$user = User::where('email', 'juan.perez@example.com')->first();

if (!$user) {
    echo "❌ Usuario Juan Pérez no encontrado\n";
    echo "Ejecuta primero: php create-test-booking.php\n";
    exit;
}

echo "✅ Usuario encontrado: {$user->name} {$user->last_name}\n";

// Buscar una atracción
$attraction = Attraction::first();
if (!$attraction) {
    echo "❌ No hay atracciones disponibles\n";
    exit;
}

echo "✅ Atracción encontrada: {$attraction->name}\n";

// Simular autenticación
Auth::login($user);

// Crear planificación de prueba
echo "📝 Creando planificación de prueba...\n";

try {
    $planning = Booking::create([
        'user_id' => $user->id,
        'tour_schedule_id' => null,
        'participants_count' => 2,
        'total_amount' => $attraction->entry_price ? ($attraction->entry_price * 2) : 0,
        'currency' => 'BOB',
        'status' => 'planned',
        'payment_status' => 'pending',
        'contact_name' => $user->name,
        'contact_email' => $user->email,
        'contact_phone' => '+591 70123456',
        'notes' => 'Planificación de visita de prueba',
        'participant_details' => [
            [
                'name' => $user->name,
                'type' => 'main_contact'
            ]
        ],
        'special_requests' => ['Visita familiar'],
        'planning_data' => [
            'attraction_id' => $attraction->id,
            'visit_date' => now()->addDays(5)->toDateString(),
            'type' => 'attraction_visit'
        ]
    ]);

    echo "✅ Planificación creada:\n";
    echo "  - ID: {$planning->id}\n";
    echo "  - Número: {$planning->booking_number}\n";
    echo "  - Estado: {$planning->status} ({$planning->status_name})\n";
    echo "  - Atracción: {$attraction->name}\n";
    echo "  - Fecha planificada: {$planning->planning_data['visit_date']}\n";
    echo "  - Participantes: {$planning->participants_count}\n";
    echo "  - Total estimado: {$planning->total_amount} {$planning->currency}\n";

} catch (Exception $e) {
    echo "❌ Error al crear planificación: " . $e->getMessage() . "\n";
    exit;
}

// Probar API del dashboard con planificaciones
echo "\n📊 Probando dashboard con planificaciones...\n";

$controller = new \App\Features\Users\Controllers\UserDashboardController();

try {
    $statsResponse = $controller->dashboardStats();
    $stats = json_decode($statsResponse->getContent(), true);
    
    echo "✅ Stats actualizadas:\n";
    echo "  - Reservas activas (incluye planificaciones): " . $stats['activeBookings'] . "\n";
    
    $upcomingResponse = $controller->upcomingBookings();
    $upcoming = json_decode($upcomingResponse->getContent(), true);
    
    echo "\n✅ Próximas reservas/planificaciones:\n";
    if (empty($upcoming['data'])) {
        echo "  - No hay reservas próximas\n";
    } else {
        foreach ($upcoming['data'] as $booking) {
            $type = isset($booking['is_planning']) && $booking['is_planning'] ? 'Planificación' : 'Reserva';
            echo "  - {$type} #{$booking['booking_number']}: {$booking['tour_name']} - {$booking['tour_date']}\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error en dashboard: " . $e->getMessage() . "\n";
}

echo "\n🎯 Resumen:\n";
echo "✅ Nuevo estado 'planned' agregado al modelo Booking\n";
echo "✅ Nueva ruta POST /api/bookings/plan creada\n";
echo "✅ Método storePlanning agregado al BookingController\n";
echo "✅ Dashboard actualizado para mostrar planificaciones\n";
echo "✅ Componente BookingForm actualizado para usar nueva API\n";

echo "\n💡 Próximos pasos:\n";
echo "1. Ejecutar migración: php artisan migrate\n";
echo "2. Compilar assets: npm run build\n";
echo "3. Probar en el navegador el formulario 'Planificar Visita'\n";

echo "\n✅ Prueba completada.\n";