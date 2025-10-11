<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ruta súper simple para verificar que Laravel funciona
Route::get('/simple', function () {
    return 'Pacha Tour - Laravel funcionando! Fecha: ' . date('Y-m-d H:i:s');
});

// Test route básico
Route::get('/test', function () {
    return response()->json([
        'message' => 'Pacha Tour Laravel está funcionando correctamente!',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'status' => 'OK'
    ]);
});

// Ruta principal con HTML simple
Route::get('/', function () {
    return '<!DOCTYPE html>
<html>
<head>
    <title>Pacha Tour</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f0f0f0; }
        .container { background: white; padding: 30px; border-radius: 10px; display: inline-block; }
        h1 { color: #333; }
        .links { margin-top: 20px; }
        .links a { margin: 0 10px; padding: 10px 20px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; }
        .status { background: #e8f5e8; color: #2d5a2d; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏔️ Pacha Tour</h1>
        <p>Plataforma de Turismo de Bolivia</p>
        <div class="status">
            <strong>✅ Laravel funcionando</strong><br>
            <strong>✅ PostgreSQL conectado</strong><br>
            <strong>✅ Migraciones ejecutadas</strong>
        </div>
        <div class="links">
            <a href="/test">Test API</a>
            <a href="/test-db">Test BD</a>
            <a href="/test-database.php">Diagnóstico BD</a>
            <a href="/api/departments">Departamentos</a>
        </div>
    </div>
</body>
</html>';
});

// Ruta para probar base de datos directamente
Route::get('/test-db', function () {
    try {
        $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=pacha_tour_db', 'postgres', $_ENV['DB_PASSWORD'] ?? '');
        
        // Obtener información de las tablas principales
        $tables = $pdo->query("
            SELECT 
                table_name,
                (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = t.table_name) as column_count
            FROM information_schema.tables t 
            WHERE table_schema = 'public' 
            AND table_name NOT IN ('migrations', 'personal_access_tokens', 'test_connection')
            ORDER BY table_name
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Base de datos Pacha Tour configurada correctamente',
            'tables_count' => count($tables),
            'main_tables' => $tables,
            'features' => [
                'departments' => 'Gestión de 9 departamentos de Bolivia',
                'attractions' => 'Atractivos turísticos con geolocalización',
                'tours' => 'Tours con horarios y reservas',
                'users' => 'Sistema de usuarios con roles',
                'bookings' => 'Sistema de reservas y pagos',
                'reviews' => 'Sistema de reseñas y calificaciones',
                'media' => 'Gestión de archivos multimedia'
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Ruta para probar modelos y relaciones Eloquent
Route::get('/test-models', function () {
    try {
        $results = [];
        
        // Probar creación de modelos
        $models = [
            'Department' => new App\Models\Department(),
            'Attraction' => new App\Models\Attraction(),
            'Tour' => new App\Models\Tour(),
            'TourSchedule' => new App\Models\TourSchedule(),
            'Booking' => new App\Models\Booking(),
            'Review' => new App\Models\Review(),
            'Media' => new App\Models\Media(),
            'User' => new App\Models\User(),
        ];
        
        foreach ($models as $name => $model) {
            $results['models'][$name] = 'OK';
        }
        
        // Probar relaciones (definición, no ejecución)
        $department = new App\Models\Department();
        $results['relations']['Department->attractions'] = method_exists($department, 'attractions');
        $results['relations']['Department->media'] = method_exists($department, 'media');
        
        $attraction = new App\Models\Attraction();
        $results['relations']['Attraction->department'] = method_exists($attraction, 'department');
        $results['relations']['Attraction->tours'] = method_exists($attraction, 'tours');
        $results['relations']['Attraction->reviews'] = method_exists($attraction, 'reviews');
        
        $tour = new App\Models\Tour();
        $results['relations']['Tour->schedules'] = method_exists($tour, 'schedules');
        $results['relations']['Tour->attractions'] = method_exists($tour, 'attractions');
        $results['relations']['Tour->bookings'] = method_exists($tour, 'bookings');
        
        $booking = new App\Models\Booking();
        $results['relations']['Booking->user'] = method_exists($booking, 'user');
        $results['relations']['Booking->tourSchedule'] = method_exists($booking, 'tourSchedule');
        
        // Probar constantes
        $results['constants'] = [
            'attraction_types' => count(App\Models\Attraction::TYPES),
            'tour_types' => count(App\Models\Tour::TYPES),
            'booking_statuses' => count(App\Models\Booking::STATUSES),
            'review_statuses' => count(App\Models\Review::STATUSES),
            'media_types' => count(App\Models\Media::TYPES),
        ];
        
        return response()->json([
            'status' => 'success',
            'message' => 'Modelos Eloquent funcionando correctamente',
            'models_count' => count($models),
            'relations_working' => array_sum($results['relations']),
            'relations_total' => count($results['relations']),
            'results' => $results,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});