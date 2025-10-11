<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;

echo "📊 ESTADO ACTUAL DE BOOKINGS\n";
echo "============================\n\n";

$user = User::where('email', 'pachatour@yopmail.com')->first();

if ($user) {
    $totalBookings = Booking::count();
    $userBookings = Booking::where('user_id', $user->id)->count();
    $planningBookings = Booking::where('user_id', $user->id)
        ->whereNull('tour_schedule_id')
        ->where('notes', 'LIKE', '%PLANIFICACIÓN%')
        ->count();

    echo "Usuario: {$user->name} (ID: {$user->id})\n";
    echo "Email: {$user->email}\n\n";
    
    echo "📈 ESTADÍSTICAS:\n";
    echo "   - Total bookings sistema: {$totalBookings}\n";
    echo "   - Bookings de Juan: {$userBookings}\n";
    echo "   - Planificaciones de Juan: {$planningBookings}\n\n";
    
    if ($userBookings > 0) {
        echo "📋 ÚLTIMOS BOOKINGS DE JUAN:\n";
        $recentBookings = Booking::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'booking_number', 'status', 'tour_schedule_id', 'participants_count', 'total_amount', 'created_at']);
            
        foreach ($recentBookings as $booking) {
            $type = $booking->tour_schedule_id ? 'Reserva' : 'Planificación';
            echo "   - #{$booking->booking_number} ({$type})\n";
            echo "     * {$booking->participants_count} personas, Bs {$booking->total_amount}\n";
            echo "     * {$booking->created_at}\n\n";
        }
    }
    
    echo "🎯 PARA MONITOREAR EN TIEMPO REAL:\n";
    echo "   Ejecuta este comando después de cada prueba:\n";
    echo "   php check-current-bookings.php\n\n";
    
} else {
    echo "❌ Usuario Juan Pérez no encontrado\n";
}

echo "============================\n";