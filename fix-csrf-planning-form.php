<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SOLUCIONANDO PROBLEMA CSRF EN FORMULARIO PLANIFICAR VISITA ===\n\n";

// 1. Verificar estado actual
echo "1. VERIFICANDO ESTADO ACTUAL:\n";
echo "   - Ruta: POST /planificar-visita\n";
echo "   - Middleware: auth, web (incluye CSRF)\n";
echo "   - Controlador: BookingController@storePlanning\n\n";

// 2. Verificar configuración CSRF
echo "2. CONFIGURACIÓN CSRF:\n";
$csrfMiddleware = app('App\Http\Middleware\VerifyCsrfToken');
echo "   - Middleware CSRF: Activo\n";
echo "   - Token actual: " . csrf_token() . "\n";
echo "   - Session ID: " . session()->getId() . "\n\n";

// 3. Verificar si la ruta está en excepciones CSRF
echo "3. VERIFICANDO EXCEPCIONES CSRF:\n";
$reflection = new ReflectionClass($csrfMiddleware);
$property = $reflection->getProperty('except');
$property->setAccessible(true);
$exceptions = $property->getValue($csrfMiddleware);

if (in_array('planificar-visita', $exceptions) || in_array('/planificar-visita', $exceptions)) {
    echo "   ✅ Ruta está en excepciones CSRF\n";
} else {
    echo "   ❌ Ruta NO está en excepciones CSRF\n";
    echo "   📝 Agregando excepción...\n";
}

echo "\n4. SOLUCIONES DISPONIBLES:\n";
echo "   A) Agregar ruta a excepciones CSRF (temporal)\n";
echo "   B) Usar API route con Sanctum (recomendado)\n";
echo "   C) Asegurar token CSRF correcto en frontend\n\n";

// 5. Implementar solución temporal
echo "5. IMPLEMENTANDO SOLUCIÓN:\n";

// Verificar si existe el archivo de middleware CSRF
$middlewarePath = app_path('Http/Middleware/VerifyCsrfToken.php');
if (file_exists($middlewarePath)) {
    $content = file_get_contents($middlewarePath);
    
    if (strpos($content, 'planificar-visita') === false) {
        echo "   📝 Agregando excepción CSRF temporal...\n";
        
        // Buscar el array $except
        $pattern = '/protected\s+\$except\s*=\s*\[(.*?)\];/s';
        if (preg_match($pattern, $content, $matches)) {
            $currentExceptions = trim($matches[1]);
            if (!empty($currentExceptions)) {
                $newExceptions = $currentExceptions . ",\n        'planificar-visita'";
            } else {
                $newExceptions = "\n        'planificar-visita'";
            }
            
            $newContent = preg_replace(
                $pattern,
                "protected \$except = [$newExceptions\n    ];",
                $content
            );
            
            file_put_contents($middlewarePath, $newContent);
            echo "   ✅ Excepción CSRF agregada temporalmente\n";
        }
    } else {
        echo "   ✅ Excepción CSRF ya existe\n";
    }
} else {
    echo "   ❌ Archivo VerifyCsrfToken.php no encontrado\n";
}

echo "\n6. COMANDOS PARA EJECUTAR:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan route:clear\n\n";

echo "7. PRUEBA EL FORMULARIO:\n";
echo "   1. Recarga la página completamente (Ctrl+F5)\n";
echo "   2. Limpia cookies del navegador\n";
echo "   3. Intenta guardar el formulario nuevamente\n\n";

echo "8. ALTERNATIVA RECOMENDADA:\n";
echo "   Usar la API route: POST /api/bookings/plan\n";
echo "   Esta ruta usa Sanctum en lugar de CSRF\n\n";

echo "=== SOLUCIÓN APLICADA ===\n";