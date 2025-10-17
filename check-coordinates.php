<?php
// check-coordinates.php
// Verificar coordenadas de departamentos

use App\Features\Departments\Models\Department;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICACIÓN COORDENADAS DEPARTAMENTOS ===\n\n";

$departments = Department::all();

echo "Total departamentos: " . $departments->count() . "\n\n";

foreach ($departments as $dept) {
    echo "Departamento: " . $dept->name . "\n";
    echo "  Latitude: " . $dept->latitude . " (tipo: " . gettype($dept->latitude) . ")\n";
    echo "  Longitude: " . $dept->longitude . " (tipo: " . gettype($dept->longitude) . ")\n";
    
    if ($dept->latitude !== null && $dept->longitude !== null) {
        echo "  Es número lat: " . (is_numeric($dept->latitude) ? 'SÍ' : 'NO') . "\n";
        echo "  Es número lng: " . (is_numeric($dept->longitude) ? 'SÍ' : 'NO') . "\n";
        
        if (is_numeric($dept->latitude) && is_numeric($dept->longitude)) {
            echo "  Formatted: " . number_format((float)$dept->latitude, 4) . ", " . number_format((float)$dept->longitude, 4) . "\n";
        }
    }
    echo "\n";
}

echo "=== FIN VERIFICACIÓN ===\n";