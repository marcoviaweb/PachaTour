<?php
// Simular exactamente lo que hace el controlador de edición

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Features\Departments\Models\Department;

// Simular la llamada al método edit
$slug = 'cochabamba';
$department = Department::where('slug', $slug)->first();

if (!$department) {
    echo "Department not found: $slug\n";
    exit(1);
}

// Cargar media como en el controlador
$department->load('media');

// Mostrar exactamente lo que Inertia recibiría
echo "=== SIMULACIÓN EXACTA DEL CONTROLADOR ===\n";
echo "URL esperada: /admin/departments/{$slug}/edit\n";
echo "Método: edit(Department \$department)\n\n";

echo "Datos que se pasan a Inertia::render():\n";
$data = [
    'department' => $department
];

echo "Props para Vue:\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\n=== VERIFICACIÓN ESPECÍFICA ===\n";
echo "department.name: " . $department->name . "\n";
echo "department.capital: " . $department->capital . "\n";
echo "department.slug: " . $department->slug . "\n";
echo "department.latitude: " . $department->latitude . "\n";
echo "department.longitude: " . $department->longitude . "\n";
echo "is_active: " . ($department->is_active ? 'true' : 'false') . "\n";

echo "\n=== TEST MODEL BINDING ===\n";
// Verificar que el model binding funciona con el slug
$testDepartment = Department::where('slug', 'cochabamba')->first();
echo "Model binding test: " . ($testDepartment ? 'SUCCESS' : 'FAILED') . "\n";
if ($testDepartment) {
    echo "Found: {$testDepartment->name} (ID: {$testDepartment->id})\n";
}