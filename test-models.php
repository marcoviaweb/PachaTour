<?php

require __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Cargar aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🧪 Probando modelos Eloquent de Pacha Tour\n";
echo "==========================================\n\n";

try {
    // Probar todos los modelos
    $models = [
        'Department' => App\Models\Department::class,
        'Attraction' => App\Models\Attraction::class,
        'Tour' => App\Models\Tour::class,
        'TourSchedule' => App\Models\TourSchedule::class,
        'Booking' => App\Models\Booking::class,
        'Review' => App\Models\Review::class,
        'Media' => App\Models\Media::class,
        'User' => App\Models\User::class,
    ];
    
    foreach ($models as $name => $class) {
        $model = new $class();
        echo "✅ {$name} model: OK\n";
    }
    
    echo "\n🎉 Todos los modelos cargan correctamente!\n";
    echo "📊 Total de modelos probados: " . count($models) . "\n";
    
    // Probar constantes
    echo "\n📋 Constantes definidas:\n";
    echo "- Tipos de atractivos: " . count(App\Models\Attraction::TYPES) . "\n";
    echo "- Tipos de tours: " . count(App\Models\Tour::TYPES) . "\n";
    echo "- Estados de reserva: " . count(App\Models\Booking::STATUSES) . "\n";
    echo "- Estados de reseñas: " . count(App\Models\Review::STATUSES) . "\n";
    echo "- Tipos de media: " . count(App\Models\Media::TYPES) . "\n";
    
    // Verificar que los métodos de relación existen
    echo "\n🔗 Verificando métodos de relación:\n";
    
    $department = new App\Models\Department();
    echo "✅ Department tiene método attractions(): " . (method_exists($department, 'attractions') ? 'Sí' : 'No') . "\n";
    
    $attraction = new App\Models\Attraction();
    echo "✅ Attraction tiene método department(): " . (method_exists($attraction, 'department') ? 'Sí' : 'No') . "\n";
    
    $tour = new App\Models\Tour();
    echo "✅ Tour tiene método schedules(): " . (method_exists($tour, 'schedules') ? 'Sí' : 'No') . "\n";
    
    $booking = new App\Models\Booking();
    echo "✅ Booking tiene método user(): " . (method_exists($booking, 'user') ? 'Sí' : 'No') . "\n";
    
    echo "\n🎯 Estructura de modelos Eloquent completada exitosamente!\n";
    echo "📝 Próximo paso: Crear seeders y factories para datos de prueba\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
?>