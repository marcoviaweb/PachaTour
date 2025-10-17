<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Features\Departments\Models\Department;

echo "=== REVISIÓN GESTIÓN DE DEPARTAMENTOS ===\n\n";

// 1. Verificar departamentos en BD
$count = Department::count();
echo "1. Departamentos en base de datos: {$count}\n";

if ($count > 0) {
    echo "\n   Primeros 5 departamentos:\n";
    $departments = Department::take(5)->get(['id', 'name', 'slug', 'is_active']);
    foreach ($departments as $dept) {
        $status = $dept->is_active ? 'Activo' : 'Inactivo';
        echo "   - {$dept->name} (ID: {$dept->id}, Slug: {$dept->slug}, Estado: {$status})\n";
    }
} else {
    echo "   ⚠️  No hay departamentos en la base de datos\n";
}

// 2. Verificar estructura de tabla
echo "\n2. Verificando estructura de tabla departments...\n";
try {
    $sample = Department::first();
    if ($sample) {
        echo "   ✅ Tabla departments accesible\n";
        echo "   📋 Campos disponibles en el modelo:\n";
        $fillable = (new Department())->getFillable();
        foreach ($fillable as $field) {
            echo "      - {$field}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error accediendo a tabla: " . $e->getMessage() . "\n";
}

echo "\n=== FIN REVISIÓN ===\n";