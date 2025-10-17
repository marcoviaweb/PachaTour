<?php
// test-search-case-insensitive.php
// Verificar que la búsqueda case-insensitive funciona

use App\Features\Departments\Models\Department;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRUEBA BÚSQUEDA CASE-INSENSITIVE ===\n\n";

$testSearches = [
    'la paz',
    'LA PAZ', 
    'La Paz',
    'LA',
    'santa',
    'SANTA CRUZ',
    'cochabamba',
    'COCHABAMBA'
];

foreach ($testSearches as $search) {
    echo "Buscando: '$search'\n";
    
    $results = Department::where(function ($q) use ($search) {
        $q->where('name', 'ILIKE', "%{$search}%")
          ->orWhere('capital', 'ILIKE', "%{$search}%")
          ->orWhere('description', 'ILIKE', "%{$search}%");
    })->get(['name', 'capital']);
    
    if ($results->count() > 0) {
        echo "  ✅ Encontrados " . $results->count() . " resultado(s):\n";
        foreach ($results as $dept) {
            echo "    - {$dept->name} (Capital: {$dept->capital})\n";
        }
    } else {
        echo "  ❌ No se encontraron resultados\n";
    }
    echo "\n";
}

echo "=== FIN PRUEBA ===\n";