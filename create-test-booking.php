<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\TourSchedule;
use App\Models\Tour;
use App\Models\Attraction;
use Carbon\Carbon;

echo "🔧 Creando booking de prueba para Juan Pérez...\n\n";

// Buscar o crear usuario Juan Pérez
$user = User::where('email', 'juan.perez@example.com')->first();

if (!$user) {
    echo "👤 Creando usuario Juan Pérez...\n";
    $user = User::create([
        'name' => 'Juan',
        'last_name' => 'Pérez',
        'email' => 'juan.perez@example.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password123'),
        'phone' => '+591 70123456',
        'country' => 'Bolivia',
        'city' => 'La Paz',
        'preferred_language' => 'es',
        'role' => 'user',
    ]);
    echo "✅ Usuario creado: ID {$user->id}\n";
} else {
    echo "✅ Usuario encontrado: ID {$user->id}\n";
}

// Verificar si hay tours disponibles
$tour = Tour::first();
if (!$tour) {
    echo "❌ No hay tours disponibles. Creando tour de prueba...\n";
    
    // Buscar una atracción
    $attraction = Attraction::first();
    if (!$attraction) {
        echo "❌ No hay atracciones disponibles\n";
        exit;
    }
    
    $tour = Tour::create([
        'name' => 'Tour Valle de la Luna',
        'slug' => 'tour-valle-de-la-luna',
        'description' => 'Explora las formaciones rocosas únicas del Valle de la Luna',
        'short_description' => 'Tour guiado por el Valle de la Luna',
        'duration_hours' => 3,
        'duration_minutes' => 0,
        'max_participants' => 15,
        'min_participants' => 2,
        'price_per_person' => 150.00,
        'currency' => 'BOB',
        'difficulty_level' => 'Fácil',
        'includes' => ['Guía especializado', 'Transporte', 'Entrada'],
        'excludes' => ['Comida', 'Bebidas'],
        'meeting_point' => 'Plaza San Francisco, La Paz',
        'is_active' => true,
    ]);
    
    // Asociar tour con atracción
    $tour->attractions()->attach($attraction->id);
    
    echo "✅ Tour creado: {$tour->name}\n";
}

// Verificar si hay horarios disponibles
$schedule = TourSchedule::where('tour_id', $tour->id)
    ->where('date', '>=', now()->toDateString())
    ->first();

if (!$schedule) {
    echo "📅 Creando horario de tour...\n";
    $schedule = TourSchedule::create([
        'tour_id' => $tour->id,
        'date' => now()->addDays(3)->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'available_spots' => 15,
        'reserved_spots' => 0,
        'price_per_person' => 150.00,
        'currency' => 'BOB',
        'guide_name' => 'Carlos Mamani',
        'guide_phone' => '+591 70987654',
        'is_active' => true,
    ]);
    echo "✅ Horario creado: {$schedule->date} a las {$schedule->start_time}\n";
}

// Crear booking
echo "📋 Creando booking...\n";

$booking = Booking::create([
    'user_id' => $user->id,
    'tour_schedule_id' => $schedule->id,
    'participants_count' => 2,
    'total_amount' => 300.00, // 150 x 2 personas
    'currency' => 'BOB',
    'commission_rate' => 10.00,
    'commission_amount' => 30.00,
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'credit_card',
    'contact_name' => 'Juan Pérez',
    'contact_email' => 'juan.perez@example.com',
    'contact_phone' => '+591 70123456',
    'participant_details' => [
        [
            'name' => 'Juan Pérez',
            'age' => 35,
            'document_type' => 'CI',
            'document_number' => '12345678'
        ],
        [
            'name' => 'María García',
            'age' => 32,
            'document_type' => 'CI',
            'document_number' => '87654321'
        ]
    ],
    'special_requests' => ['Vegetariano'],
    'confirmed_at' => now(),
]);

echo "✅ Booking creado:\n";
echo "  - ID: {$booking->id}\n";
echo "  - Número: {$booking->booking_number}\n";
echo "  - Usuario: {$user->name} {$user->last_name}\n";
echo "  - Tour: {$tour->name}\n";
echo "  - Fecha: {$schedule->date}\n";
echo "  - Hora: {$schedule->start_time}\n";
echo "  - Participantes: {$booking->participants_count}\n";
echo "  - Total: {$booking->total_amount} {$booking->currency}\n";
echo "  - Estado: {$booking->status}\n";

// Crear otro booking para el historial
echo "\n📋 Creando booking completado para el historial...\n";

$pastSchedule = TourSchedule::create([
    'tour_id' => $tour->id,
    'date' => now()->subDays(10)->toDateString(),
    'start_time' => '14:00:00',
    'end_time' => '17:00:00',
    'available_spots' => 15,
    'reserved_spots' => 3,
    'price_per_person' => 150.00,
    'currency' => 'BOB',
    'guide_name' => 'Ana Quispe',
    'guide_phone' => '+591 70555666',
    'is_active' => true,
]);

$pastBooking = Booking::create([
    'user_id' => $user->id,
    'tour_schedule_id' => $pastSchedule->id,
    'participants_count' => 3,
    'total_amount' => 450.00,
    'currency' => 'BOB',
    'commission_rate' => 10.00,
    'commission_amount' => 45.00,
    'status' => 'completed',
    'payment_status' => 'paid',
    'payment_method' => 'bank_transfer',
    'contact_name' => 'Juan Pérez',
    'contact_email' => 'juan.perez@example.com',
    'contact_phone' => '+591 70123456',
    'participant_details' => [
        [
            'name' => 'Juan Pérez',
            'age' => 35,
            'document_type' => 'CI',
            'document_number' => '12345678'
        ],
        [
            'name' => 'María García',
            'age' => 32,
            'document_type' => 'CI',
            'document_number' => '87654321'
        ],
        [
            'name' => 'Pedro Pérez',
            'age' => 8,
            'document_type' => 'CI',
            'document_number' => '11223344'
        ]
    ],
    'confirmed_at' => now()->subDays(12),
]);

echo "✅ Booking completado creado:\n";
echo "  - ID: {$pastBooking->id}\n";
echo "  - Número: {$pastBooking->booking_number}\n";
echo "  - Fecha: {$pastSchedule->date}\n";
echo "  - Estado: {$pastBooking->status}\n";

echo "\n🎉 ¡Bookings de prueba creados exitosamente!\n";
echo "\n💡 Ahora puedes:\n";
echo "  1. Iniciar sesión como juan.perez@example.com (password: password123)\n";
echo "  2. Ir a http://127.0.0.1:8000/mis-viajes\n";
echo "  3. Ver los bookings en el dashboard\n";

// Verificar que los bookings se crearon correctamente
echo "\n🔍 Verificación final:\n";
$userBookings = Booking::where('user_id', $user->id)->count();
echo "  - Total bookings para {$user->name}: {$userBookings}\n";

$upcomingBookings = Booking::where('user_id', $user->id)
    ->whereIn('status', ['pending', 'confirmed', 'paid'])
    ->whereHas('tourSchedule', function ($query) {
        $query->where('date', '>=', now()->toDateString());
    })
    ->count();
echo "  - Bookings próximos: {$upcomingBookings}\n";

$completedBookings = Booking::where('user_id', $user->id)
    ->where('status', 'completed')
    ->count();
echo "  - Bookings completados: {$completedBookings}\n";