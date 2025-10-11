<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\Attraction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "🔍 Diagnosticando problema de guardado de bookings...\n\n";

// 1. Verificar usuario Juan Pérez
echo "1️⃣ Verificando usuario Juan Pérez:\n";
$user = User::where('email', 'juan.perez@example.com')->first();

if (!$user) {
    echo "❌ Usuario no encontrado. Creando usuario...\n";
    $user = User::create([
        'name' => 'Juan',
        'last_name' => 'Pérez',
        'email' => 'juan.perez@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
    ]);
    echo "✅ Usuario creado: ID {$user->id}\n";
} else {
    echo "✅ Usuario encontrado: ID {$user->id}, Nombre: {$user->name}\n";
}

// 2. Verificar tabla bookings
echo "\n2️⃣ Verificando tabla bookings:\n";
try {
    $count = DB::table('bookings')->count();
    echo "   - Total bookings en BD: {$count}\n";
    
    $userBookings = DB::table('bookings')->where('user_id', $user->id)->count();
    echo "   - Bookings de Juan Pérez: {$userBookings}\n";
    
} catch (Exception $e) {
    echo "❌ Error accediendo a tabla bookings: " . $e->getMessage() . "\n";
}

// 3. Verificar atracción
echo "\n3️⃣ Verificando atracciones:\n";
$attraction = Attraction::first();
if (!$attraction) {
    echo "❌ No hay atracciones disponibles\n";
    exit;
}
echo "✅ Atracción encontrada: {$attraction->name} (ID: {$attraction->id})\n";

// 4. Probar creación manual de booking
echo "\n4️⃣ Probando creación manual de booking:\n";
Auth::login($user);

try {
    $booking = Booking::create([
        'user_id' => $user->id,
        'tour_schedule_id' => null, // Para planificación
        'participants_count' => 2,
        'total_amount' => 100.00,
        'currency' => 'BOB',
        'status' => 'pending', // Usar estado que sabemos que existe
        'payment_status' => 'pending',
        'contact_name' => $user->name,
        'contact_email' => $user->email,
        'contact_phone' => '+591 70123456',
        'notes' => 'Booking de prueba manual',
    ]);
    
    echo "✅ Booking creado manualmente:\n";
    echo "   - ID: {$booking->id}\n";
    echo "   - Número: {$booking->booking_number}\n";
    echo "   - Estado: {$booking->status}\n";
    
} catch (Exception $e) {
    echo "❌ Error creando booking manual: " . $e->getMessage() . "\n";
    echo "   Esto indica un problema con el modelo o la tabla\n";
}

// 5. Probar API de planificación
echo "\n5️⃣ Probando API storePlanning:\n";
try {
    $controller = new \App\Features\Tours\Controllers\BookingController(
        new \App\Features\Tours\Services\BookingService()
    );
    
    // Simular request
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'attraction_id' => $attraction->id,
        'visit_date' => now()->addDays(3)->toDateString(),
        'visitors_count' => 2,
        'contact_name' => $user->name,
        'contact_email' => $user->email,
        'contact_phone' => '+591 70123456',
        'notes' => 'Prueba API storePlanning',
        'estimated_total' => 150.00,
    ]);
    
    $response = $controller->storePlanning($request);
    $data = json_decode($response->getContent(), true);
    
    if ($response->getStatusCode() === 201) {
        echo "✅ API storePlanning funciona:\n";
        echo "   - Mensaje: " . $data['message'] . "\n";
        echo "   - Booking ID: " . $data['data']['id'] . "\n";
    } else {
        echo "❌ Error en API storePlanning:\n";
        echo "   - Status: " . $response->getStatusCode() . "\n";
        echo "   - Respuesta: " . $response->getContent() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error probando API: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// 6. Verificar ruta API
echo "\n6️⃣ Verificando ruta API:\n";
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $planRoute = null;
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'bookings/plan')) {
            $planRoute = $route;
            break;
        }
    }
    
    if ($planRoute) {
        echo "✅ Ruta /api/bookings/plan encontrada\n";
        echo "   - Métodos: " . implode(', ', $planRoute->methods()) . "\n";
        echo "   - Middleware: " . implode(', ', $planRoute->middleware()) . "\n";
    } else {
        echo "❌ Ruta /api/bookings/plan NO encontrada\n";
        echo "   Esto explica por qué el formulario no funciona\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error verificando rutas: " . $e->getMessage() . "\n";
}

echo "\n✅ Diagnóstico completado.\n";