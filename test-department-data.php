<?php
// test-department-data.php
// Verificar qué datos están enviando al frontend

use App\Features\Admin\Controllers\DepartmentController;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICACIÓN DATOS DEPARTAMENTO ===\n\n";

// Simular request
$request = new Request();
$controller = new DepartmentController();

// Obtener datos como los ve el controlador
$departments = \App\Features\Departments\Models\Department::limit(1)->first();

echo "Datos del primer departamento:\n";
echo "ID: " . $departments->id . "\n";
echo "Slug: " . $departments->slug . "\n";
echo "Name: " . $departments->name . "\n";
echo "Binding key: " . $departments->getRouteKeyName() . "\n";
echo "Route key value: " . $departments->getRouteKey() . "\n\n";

// Verificar que el binding funcione
echo "Prueba de route binding:\n";
$foundBySlug = \App\Features\Departments\Models\Department::where('slug', $departments->slug)->first();
echo "Encontrado por slug: " . ($foundBySlug ? "SÍ" : "NO") . "\n";

echo "\n=== FIN VERIFICACIÓN ===\n";