<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\Attraction;
use Illuminate\Support\Facades\Auth;

echo "🧪 Prueba simple de booking...\n";

// Buscar usuario
$user = User::where('email', 'juan.perez@example.com')->first();
if (!$user) {
    $user = User::create([
        'name' => 'Juan',
        'email' => 'juan.perez@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
    ]);
}

Auth::login($user);

// Buscar atracción
$attraction = Attraction::first();

// Crear booking simple
try {
    $booking = Booking::create([
        'user_id' => $user->id,
        'tour_schedule_id' => null,
        'participants_count' => 2,
        'total_amount' => 100.00,
        'currency' => 'BOB',
        'commission_rate' => 0.00,
        'commission_amount' => 0.00,
        'status' => 'pending',
        'payment_status' => 'pending',
        'contact_name' => $user->name,
        'contact_email' => $user->email,
        'notes' => 'Prueba simple',
    ]);
    
    echo "✅ Booking creado: ID {$booking->id}\n";
    echo "✅ Total bookings: " . Booking::count() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}