<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\TourSchedule;
use App\Models\Tour;

echo "🔍 Verificando bookings en la base de datos...\n\n";

// Buscar usuario Juan Pérez
$user = User::where('name', 'LIKE', '%Juan%')
    ->orWhere('email', 'LIKE', '%juan%')
    ->first();

if (!$user) {
    echo "❌ No se encontró el usuario Juan Pérez\n";
    echo "Usuarios disponibles:\n";
    User::all()->each(function($u) {
        echo "  - ID: {$u->id}, Nombre: {$u->name}, Email: {$u->email}\n";
    });
    exit;
}

echo "✅ Usuario encontrado:\n";
echo "  - ID: {$user->id}\n";
echo "  - Nombre: {$user->name}\n";
echo "  - Email: {$user->email}\n\n";

// Verificar bookings del usuario
$bookings = Booking::where('user_id', $user->id)->get();

echo "📋 Bookings del usuario:\n";
if ($bookings->isEmpty()) {
    echo "❌ No hay bookings para este usuario\n\n";
} else {
    foreach ($bookings as $booking) {
        echo "  - ID: {$booking->id}\n";
        echo "    Número: {$booking->booking_number}\n";
        echo "    Estado: {$booking->status}\n";
        echo "    Tour Schedule ID: {$booking->tour_schedule_id}\n";
        echo "    Participantes: {$booking->participants_count}\n";
        echo "    Total: {$booking->total_amount} {$booking->currency}\n";
        echo "    Creado: {$booking->created_at}\n";
        
        // Verificar tour schedule
        $schedule = TourSchedule::find($booking->tour_schedule_id);
        if ($schedule) {
            echo "    Fecha tour: {$schedule->date}\n";
            echo "    Hora: {$schedule->start_time}\n";
            
            // Verificar tour
            $tour = Tour::find($schedule->tour_id);
            if ($tour) {
                echo "    Tour: {$tour->name}\n";
            } else {
                echo "    ❌ Tour no encontrado (ID: {$schedule->tour_id})\n";
            }
        } else {
            echo "    ❌ TourSchedule no encontrado (ID: {$booking->tour_schedule_id})\n";
        }
        echo "\n";
    }
}

// Verificar todos los bookings en la base de datos
echo "📊 Resumen general de bookings:\n";
$totalBookings = Booking::count();
echo "  - Total bookings: {$totalBookings}\n";

if ($totalBookings > 0) {
    echo "  - Bookings por usuario:\n";
    Booking::selectRaw('user_id, count(*) as count')
        ->groupBy('user_id')
        ->get()
        ->each(function($booking) {
            $user = User::find($booking->user_id);
            $userName = $user ? $user->name : 'Usuario no encontrado';
            echo "    User ID {$booking->user_id} ({$userName}): {$booking->count} bookings\n";
        });
    
    echo "\n  - Bookings por estado:\n";
    Booking::selectRaw('status, count(*) as count')
        ->groupBy('status')
        ->get()
        ->each(function($booking) {
            echo "    {$booking->status}: {$booking->count}\n";
        });
}

// Verificar tour schedules
echo "\n🗓️  Tour Schedules disponibles:\n";
$schedules = TourSchedule::with('tour')->get();
if ($schedules->isEmpty()) {
    echo "❌ No hay tour schedules\n";
} else {
    foreach ($schedules->take(5) as $schedule) {
        echo "  - ID: {$schedule->id}\n";
        echo "    Tour: {$schedule->tour->name ?? 'Sin tour'}\n";
        echo "    Fecha: {$schedule->date}\n";
        echo "    Hora: {$schedule->start_time}\n\n";
    }
    if ($schedules->count() > 5) {
        echo "  ... y " . ($schedules->count() - 5) . " más\n";
    }
}

echo "\n✅ Verificación completada.\n";