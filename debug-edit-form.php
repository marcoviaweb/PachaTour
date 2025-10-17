<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Route;
use App\Models\Department;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEPURACIÓN DEL FORMULARIO DE EDICIÓN ===\n\n";

// Buscar el departamento de Cochabamba
$department = Department::where('slug', 'cochabamba')->first();

if (!$department) {
    echo "❌ ERROR: No se encontró el departamento 'cochabamba'\n";
    echo "Departamentos disponibles:\n";
    $departments = Department::all(['name', 'slug']);
    foreach ($departments as $dept) {
        echo "  - {$dept->name} (slug: {$dept->slug})\n";
    }
    exit(1);
}

echo "✅ Departamento encontrado: {$department->name}\n";
echo "   Slug: {$department->slug}\n";
echo "   ID: {$department->id}\n\n";

echo "=== DATOS DEL DEPARTAMENTO ===\n";
echo "Nombre: " . ($department->name ?? 'NULL') . "\n";
echo "Capital: " . ($department->capital ?? 'NULL') . "\n";
echo "Descripción: " . (substr($department->description ?? 'NULL', 0, 100)) . "...\n";
echo "Descripción corta: " . ($department->short_description ?? 'NULL') . "\n";
echo "Latitud: " . ($department->latitude ?? 'NULL') . "\n";
echo "Longitud: " . ($department->longitude ?? 'NULL') . "\n";
echo "Población: " . ($department->population ?? 'NULL') . "\n";
echo "Activo: " . ($department->is_active ? 'Sí' : 'No') . "\n";
echo "Orden: " . ($department->sort_order ?? 'NULL') . "\n\n";

echo "=== DATOS EN FORMATO JSON (como los recibe Vue) ===\n";
echo json_encode($department->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== VERIFICAR COORDENADAS ===\n";
echo "Latitud (atributo): " . $department->latitude . "\n";
echo "Longitud (atributo): " . $department->longitude . "\n";
echo "Latitud (campo raw): " . $department->getAttributes()['latitude'] ?? 'NULL' . "\n";
echo "Longitud (campo raw): " . $department->getAttributes()['longitude'] ?? 'NULL' . "\n";

echo "\n=== FIN DEPURACIÓN ===\n";