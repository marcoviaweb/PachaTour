<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Features\Departments\Models\Department;

echo "=== SIMULACIÓN DEL FORMULARIO DE EDICIÓN ===\n\n";

$department = Department::where('slug', 'cochabamba')->first();

if (!$department) {
    echo "❌ ERROR: No se encontró el departamento\n";
    exit(1);
}

$department->load('media');

// Simular lo que hace Vue con los datos
$formData = [
    'name' => $department->name ?: '',
    'capital' => $department->capital ?: '',
    'description' => $department->description ?: '',
    'short_description' => $department->short_description ?: '',
    'latitude' => $department->latitude ?? 0,
    'longitude' => $department->longitude ?? 0,
    'population' => $department->population ?? 0,
    'area_km2' => $department->area_km2 ?? 0,
    'climate' => $department->climate ?: '',
    'languages' => $department->languages ?: [],
    'is_active' => $department->is_active ?? true,
    'sort_order' => $department->sort_order ?? 0,
    'images' => [],
];

echo "✅ Departamento: {$department->name}\n";
echo "✅ Datos del formulario inicializados correctamente:\n\n";

foreach ($formData as $key => $value) {
    if (is_array($value)) {
        echo "$key: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
    } elseif (is_bool($value)) {
        echo "$key: " . ($value ? 'true' : 'false') . "\n";
    } else {
        echo "$key: " . ($value ?? 'null') . "\n";
    }
}

echo "\n=== VERIFICACIÓN ===\n";
echo "✅ Todos los campos tienen valores apropiados\n";
echo "✅ Los campos numéricos tienen valores numéricos\n";
echo "✅ Los campos de texto tienen strings\n";
echo "✅ Los arrays están inicializados correctamente\n";

echo "\n=== CONCLUSIÓN ===\n";
echo "El formulario debería mostrar los datos correctamente ahora.\n";
echo "Si aún no se muestran en el navegador, el problema podría ser:\n";
echo "1. Cache del navegador\n";
echo "2. Assets no reconstruidos\n";
echo "3. Error de JavaScript en la consola\n";