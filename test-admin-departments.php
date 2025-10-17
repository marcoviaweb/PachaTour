<?php
// test-admin-departments.php
// Prueba rápida de la URL /admin/departments

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

echo "=== PRUEBA URL ADMIN DEPARTMENTS ===\n\n";

// Verificar la ruta
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$adminDepartmentsRoute = null;

foreach ($routes as $route) {
    if ($route->getName() === 'admin.departments.index') {
        $adminDepartmentsRoute = $route;
        break;
    }
}

if ($adminDepartmentsRoute) {
    echo "✅ Ruta admin.departments.index encontrada:\n";
    echo "   URI: " . $adminDepartmentsRoute->uri() . "\n";
    echo "   Métodos: " . implode(', ', $adminDepartmentsRoute->methods()) . "\n";
    echo "   Controlador: " . $adminDepartmentsRoute->getActionName() . "\n\n";
} else {
    echo "❌ Ruta admin.departments.index NO encontrada\n\n";
}

// Verificar el controlador existe
$controllerClass = 'App\Features\Admin\Controllers\DepartmentController';
if (class_exists($controllerClass)) {
    echo "✅ Controlador $controllerClass existe\n";
    
    $controller = new $controllerClass();
    if (method_exists($controller, 'index')) {
        echo "✅ Método index() existe en el controlador\n";
    } else {
        echo "❌ Método index() NO existe en el controlador\n";
    }
} else {
    echo "❌ Controlador $controllerClass NO existe\n";
}

echo "\n=== FIN PRUEBA ===\n";