<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\Attraction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

echo "🔍 DIAGNÓSTICO DEL FORMULARIO 'PLANIFICAR VISITA'\n";
echo "================================================\n\n";

// 1. Verificar usuario Juan Pérez
echo "1️⃣ Verificando usuario Juan Pérez:\n";
$user = User::where('email', 'juan.perez@example.com')->first();
if (!$user) {
    $user = User::where('name', 'Juan')->where('last_name', 'Pérez')->first();
}
if (!$user) {
    $user = User::where('name', 'LIKE', '%Juan%')->first();
}

if ($user) {
    echo "   ✅ Usuario encontrado: ID {$user->id}, Email: {$user->email}\n";
    echo "   - Nombre: {$user->name} {$user->last_name}\n";
} else {
    echo "   ❌ Usuario Juan Pérez no encontrado\n";
    echo "   - Usuarios disponibles:\n";
    $users = User::take(5)->get(['id', 'name', 'last_name', 'email']);
    foreach ($users as $u) {
        echo "     * ID {$u->id}: {$u->name} {$u->last_name} ({$u->email})\n";
    }
}

// 2. Verificar estructura de bookings
echo "\n2️⃣ Verificando estructura tabla bookings:\n";
try {
    $columns = Schema::getColumnListing('bookings');
    echo "   ✅ Tabla bookings existe\n";
    echo "   - Columnas: " . implode(', ', $columns) . "\n";
    
    // Verificar si tour_schedule_id es nullable
    $result = DB::select("SHOW COLUMNS FROM bookings WHERE Field = 'tour_schedule_id'");
    if (!empty($result)) {
        $nullable = $result[0]->Null === 'YES' ? 'SÍ' : 'NO';
        echo "   - tour_schedule_id nullable: {$nullable}\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 3. Verificar bookings existentes
echo "\n3️⃣ Verificando bookings existentes:\n";
$totalBookings = Booking::count();
echo "   - Total bookings: {$totalBookings}\n";

if ($user) {
    $userBookings = Booking::where('user_id', $user->id)->count();
    echo "   - Bookings de Juan Pérez: {$userBookings}\n";
    
    if ($userBookings > 0) {
        echo "   - Últimos bookings de Juan:\n";
        $recentBookings = Booking::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get(['id', 'booking_number', 'status', 'tour_schedule_id', 'notes', 'created_at']);
            
        foreach ($recentBookings as $booking) {
            $type = $booking->tour_schedule_id ? 'Reserva' : 'Planificación';
            echo "     * #{$booking->booking_number} ({$type}) - {$booking->status} - {$booking->created_at}\n";
        }
    }
}

// 4. Verificar rutas API
echo "\n4️⃣ Verificando rutas API:\n";
try {
    $routes = Route::getRoutes();
    $planRoute = null;
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'bookings/plan') && in_array('POST', $route->methods())) {
            $planRoute = $route;
            break;
        }
    }
    
    if ($planRoute) {
        echo "   ✅ Ruta POST /api/bookings/plan encontrada\n";
        echo "   - URI: {$planRoute->uri()}\n";
        echo "   - Acción: {$planRoute->getActionName()}\n";
    } else {
        echo "   ❌ Ruta POST /api/bookings/plan NO encontrada\n";
        echo "   - Rutas de bookings disponibles:\n";
        foreach ($routes as $route) {
            if (str_contains($route->uri(), 'booking')) {
                echo "     * {$route->methods()[0]} /{$route->uri()}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error verificando rutas: " . $e->getMessage() . "\n";
}

// 5. Verificar atracciones
echo "\n5️⃣ Verificando atracciones:\n";
$attractionCount = Attraction::count();
echo "   - Total atracciones: {$attractionCount}\n";

if ($attractionCount > 0) {
    $attraction = Attraction::first();
    echo "   - Primera atracción: ID {$attraction->id} - {$attraction->name}\n";
} else {
    echo "   ❌ No hay atracciones disponibles\n";
}

// 6. Probar creación manual de booking
echo "\n6️⃣ Probando creación manual de booking:\n";
if ($user && $attractionCount > 0) {
    try {
        $attraction = Attraction::first();
        
        $booking = new Booking();
        $booking->user_id = $user->id;
        $booking->booking_number = 'PLAN-' . strtoupper(uniqid());
        $booking->status = 'pending';
        $booking->tour_schedule_id = null; // Planificación
        $booking->participants_count = 2;
        $booking->total_amount = 160.00;
        $booking->currency = 'BOB';
        $booking->contact_name = $user->name . ' ' . $user->last_name;
        $booking->contact_email = $user->email;
        $booking->contact_phone = '+591 70123456';
        $booking->notes = "PLANIFICACIÓN - Atracción ID: {$attraction->id}, Fecha: 2025-01-11";
        $booking->payment_status = 'pending';
        
        $booking->save();
        
        echo "   ✅ Booking de prueba creado exitosamente\n";
        echo "   - ID: {$booking->id}\n";
        echo "   - Número: {$booking->booking_number}\n";
        echo "   - Atracción: {$attraction->name}\n";
        
    } catch (Exception $e) {
        echo "   ❌ Error creando booking: " . $e->getMessage() . "\n";
        echo "   - Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
} else {
    echo "   ⚠️  No se puede probar (falta usuario o atracciones)\n";
}

// 7. Verificar logs recientes
echo "\n7️⃣ Verificando logs recientes:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -20);
    
    $errorFound = false;
    foreach ($recentLines as $line) {
        if (str_contains($line, 'ERROR') || str_contains($line, 'booking') || str_contains($line, 'planning')) {
            echo "   - " . substr($line, 0, 100) . "...\n";
            $errorFound = true;
        }
    }
    
    if (!$errorFound) {
        echo "   ✅ No hay errores recientes relacionados con bookings\n";
    }
} else {
    echo "   ⚠️  Archivo de log no encontrado\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 RESUMEN DEL DIAGNÓSTICO:\n\n";

if (!$user) {
    echo "❌ PROBLEMA: Usuario Juan Pérez no encontrado\n";
    echo "   Solución: Crear usuario o usar uno existente\n\n";
}

if ($attractionCount === 0) {
    echo "❌ PROBLEMA: No hay atracciones disponibles\n";
    echo "   Solución: php artisan db:seed --class=AttractionSeeder\n\n";
}

if (!$planRoute) {
    echo "❌ PROBLEMA: Ruta API /api/bookings/plan no existe\n";
    echo "   Solución: Verificar routes/api.php\n\n";
}

echo "💡 PRÓXIMOS PASOS:\n";
echo "1. Ejecutar: php complete-solution.php\n";
echo "2. Probar formulario en navegador\n";
echo "3. Revisar logs si hay errores\n";