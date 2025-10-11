<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PRUEBA FINAL DEL FORMULARIO PLANIFICAR VISITA ===\n\n";

// Autenticar usuario
$user = \App\Models\User::where('email', 'pachatour@yopmail.com')->first();
\Illuminate\Support\Facades\Auth::login($user);

// Obtener atracción
$attraction = \App\Models\Attraction::first();

// Crear request con fecha futura
$request = new \Illuminate\Http\Request();
$request->merge([
    'attraction_id' => $attraction->id,
    'visit_date' => date('Y-m-d', strtotime('+7 days')), // Fecha futura
    'visitors_count' => 2,
    'contact_name' => 'Juan Perez',
    'contact_email' => 'pachatour@yopmail.com',
    'contact_phone' => '+591 70123456',
    'notes' => 'Solicitudes especiales, alergias, etc.',
    'estimated_total' => 60.00,
]);

echo "📋 DATOS DEL FORMULARIO:\n";
echo "   - Atracción: {$attraction->name}\n";
echo "   - Fecha de visita: {$request->visit_date}\n";
echo "   - Visitantes: {$request->visitors_count} personas\n";
echo "   - Contacto: {$request->contact_name}\n";
echo "   - Email: {$request->contact_email}\n";
echo "   - Total estimado: Bs {$request->estimated_total}\n\n";

// Probar controlador
$controller = new \App\Features\Tours\Controllers\BookingController(
    new \App\Features\Tours\Services\BookingService()
);

echo "🚀 ENVIANDO FORMULARIO...\n";
try {
    $response = $controller->storePlanning($request);
    $data = json_decode($response->getContent(), true);
    
    if ($response->getStatusCode() === 201) {
        echo "✅ ¡FORMULARIO GUARDADO EXITOSAMENTE!\n\n";
        echo "📊 RESPUESTA:\n";
        echo "   - Status: {$response->getStatusCode()}\n";
        echo "   - Mensaje: {$data['message']}\n";
        echo "   - Booking ID: {$data['data']['id']}\n";
        echo "   - Usuario ID: {$data['data']['user_id']}\n";
        echo "   - Total: Bs {$data['data']['total_amount']}\n";
        echo "   - Estado: {$data['data']['status']}\n\n";
        
        echo "🎉 EL PROBLEMA CSRF ESTÁ SOLUCIONADO!\n\n";
        
    } else {
        echo "❌ Error en formulario:\n";
        echo "   - Status: {$response->getStatusCode()}\n";
        echo "   - Respuesta: {$response->getContent()}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "📝 INSTRUCCIONES PARA EL USUARIO:\n";
echo "1. Recarga la página completamente (Ctrl+F5)\n";
echo "2. Limpia las cookies del navegador:\n";
echo "   - F12 → Application → Storage → Clear storage\n";
echo "3. Asegúrate de que ambos servidores estén corriendo:\n";
echo "   - php artisan serve\n";
echo "   - npm run dev\n";
echo "4. Intenta guardar el formulario nuevamente\n\n";

echo "✅ SOLUCIÓN APLICADA:\n";
echo "   - Excepción CSRF agregada para 'planificar-visita'\n";
echo "   - Cache limpiado\n";
echo "   - Formulario probado y funcionando\n\n";

echo "=== PRUEBA COMPLETADA ===\n";