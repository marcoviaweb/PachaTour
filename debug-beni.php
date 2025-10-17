<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Features\Departments\Models\Department;

echo "=== VERIFICACIÓN DEL DEPARTAMENTO BENI ===\n\n";

$department = Department::where('slug', 'beni')->first();

if (!$department) {
    echo "❌ ERROR: No se encontró el departamento 'beni'\n";
    echo "Departamentos disponibles:\n";
    $departments = Department::all(['name', 'slug']);
    foreach ($departments as $dept) {
        echo "  - {$dept->name} (slug: {$dept->slug})\n";
    }
    exit(1);
}

$department->load('media');

echo "✅ Departamento encontrado: {$department->name}\n";
echo "   URL esperada: /admin/departments/beni/edit\n\n";

echo "=== DATOS COMPLETOS ===\n";
$data = [
    'department' => $department->toArray()
];

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\n=== VERIFICACIÓN ESPECÍFICA ===\n";
echo "¿Tiene nombre? " . ($department->name ? "✅ Sí: '{$department->name}'" : "❌ No") . "\n";
echo "¿Tiene capital? " . ($department->capital ? "✅ Sí: '{$department->capital}'" : "❌ No") . "\n";
echo "¿Tiene descripción? " . ($department->description ? "✅ Sí (" . strlen($department->description) . " chars)" : "❌ No") . "\n";
echo "¿Tiene coordenadas? " . ($department->latitude && $department->longitude ? "✅ Sí: {$department->latitude}, {$department->longitude}" : "❌ No") . "\n";

echo "\n=== COMPARACIÓN CON COCHABAMBA ===\n";
$cochabamba = Department::where('slug', 'cochabamba')->first();
if ($cochabamba) {
    echo "Cochabamba tiene nombre: " . ($cochabamba->name ? "✅ Sí" : "❌ No") . "\n";
    echo "Beni tiene nombre: " . ($department->name ? "✅ Sí" : "❌ No") . "\n";
    echo "Estructura similar: " . (get_class($cochabamba) === get_class($department) ? "✅ Sí" : "❌ No") . "\n";
}