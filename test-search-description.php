<?php
// test-search-description.php
// Verificar búsqueda en descripción

use App\Features\Departments\Models\Department;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRUEBA BÚSQUEDA EN DESCRIPCIÓN ===\n\n";

// Obtener algunos fragmentos de descripción para probar
$departments = Department::take(3)->get(['name', 'description']);

foreach ($departments as $dept) {
    echo "Departamento: {$dept->name}\n";
    echo "Descripción: " . substr($dept->description, 0, 100) . "...\n";
    
    // Probar búsqueda con una palabra de la descripción
    $words = explode(' ', $dept->description);
    if (count($words) > 3) {
        $testWord = strtolower($words[3]); // Tomar la 4ta palabra en minúsculas
        
        $results = Department::where(function ($q) use ($testWord) {
            $q->where('name', 'ILIKE', "%{$testWord}%")
              ->orWhere('capital', 'ILIKE', "%{$testWord}%")
              ->orWhere('description', 'ILIKE', "%{$testWord}%");
        })->count();
        
        echo "Probando búsqueda con: '$testWord' - Resultados: $results\n";
    }
    echo "\n";
}

echo "=== FIN PRUEBA ===\n";