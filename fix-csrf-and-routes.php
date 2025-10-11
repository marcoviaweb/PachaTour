<?php
echo "=== DIAGNÓSTICO DE ERRORES CSRF Y RUTAS ===\n\n";

// 1. Verificar rutas registradas
echo "1. RUTAS REGISTRADAS:\n";
echo "Ejecuta: php artisan route:list\n\n";

// 2. Verificar configuración de sesiones
echo "2. CONFIGURACIÓN DE SESIONES:\n";
$sessionConfig = config('session');
echo "Driver: " . $sessionConfig['driver'] . "\n";
echo "Lifetime: " . $sessionConfig['lifetime'] . " minutos\n";
echo "Domain: " . ($sessionConfig['domain'] ?? 'null') . "\n";
echo "Secure: " . ($sessionConfig['secure'] ? 'true' : 'false') . "\n\n";

// 3. Verificar middleware CSRF
echo "3. MIDDLEWARE CSRF:\n";
$middleware = app('router')->getMiddleware();
if (isset($middleware['web'])) {
    echo "Middleware web encontrado\n";
} else {
    echo "⚠️ Middleware web NO encontrado\n";
}

// 4. Verificar token CSRF actual
echo "\n4. TOKEN CSRF ACTUAL:\n";
echo "Token: " . csrf_token() . "\n";
echo "Session ID: " . session()->getId() . "\n\n";

// 5. Limpiar cache y sesiones
echo "5. COMANDOS DE LIMPIEZA RECOMENDADOS:\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n";
echo "php artisan session:flush\n";
echo "php artisan route:clear\n";
echo "php artisan view:clear\n\n";

// 6. Verificar archivos de configuración críticos
echo "6. VERIFICACIÓN DE ARCHIVOS:\n";
$files = [
    'config/session.php',
    'config/app.php',
    'routes/web.php',
    'app/Http/Kernel.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file existe\n";
    } else {
        echo "✗ $file NO existe\n";
    }
}

echo "\n=== SOLUCIONES RECOMENDADAS ===\n";
echo "1. Ejecutar comandos de limpieza\n";
echo "2. Verificar que las rutas estén correctamente definidas\n";
echo "3. Asegurar que el middleware web esté aplicado\n";
echo "4. Verificar configuración de dominio en session.php\n";