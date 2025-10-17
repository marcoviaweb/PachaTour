<?php
// test-coordinates-fix.php
// Verificar que los accessors funcionen correctamente

use App\Features\Departments\Models\Department;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRUEBA ACCESSORS COORDENADAS ===\n\n";

$dept = Department::first();

echo "Departamento: " . $dept->name . "\n";
echo "Raw latitude: " . $dept->getAttributes()['latitude'] . " (tipo: " . gettype($dept->getAttributes()['latitude']) . ")\n";
echo "Accessor latitude: " . $dept->latitude . " (tipo: " . gettype($dept->latitude) . ")\n";
echo "Raw longitude: " . $dept->getAttributes()['longitude'] . " (tipo: " . gettype($dept->getAttributes()['longitude']) . ")\n";
echo "Accessor longitude: " . $dept->longitude . " (tipo: " . gettype($dept->longitude) . ")\n\n";

// Probar método toFixed directamente
echo "Prueba toFixed:\n";
try {
    $formatted = number_format($dept->latitude, 4) . ", " . number_format($dept->longitude, 4);
    echo "✅ Formateo exitoso: " . $formatted . "\n";
} catch (Exception $e) {
    echo "❌ Error en formateo: " . $e->getMessage() . "\n";
}

// Probar serialización JSON
echo "\nPrueba JSON:\n";
$json = json_encode([
    'latitude' => $dept->latitude,
    'longitude' => $dept->longitude
]);
echo "JSON: " . $json . "\n";

$decoded = json_decode($json, true);
echo "Latitude decodificada: " . $decoded['latitude'] . " (tipo: " . gettype($decoded['latitude']) . ")\n";

echo "\n=== FIN PRUEBA ===\n";