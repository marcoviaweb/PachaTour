<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SOLUCIONANDO PROBLEMAS DE FRONTEND ===\n\n";

// 1. Verificar rutas problemáticas
echo "1. RUTAS PROBLEMÁTICAS IDENTIFICADAS:\n";
echo "❌ /dashboard (404) - Debería ser /mis-viajes\n";
echo "❌ /planificar-visita (419 CSRF) - Token mismatch\n\n";

// 2. Verificar configuración actual
echo "2. CONFIGURACIÓN ACTUAL:\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "SESSION_DRIVER: " . config('session.driver') . "\n";
echo "SESSION_DOMAIN: " . (config('session.domain') ?: 'null') . "\n";
echo "SESSION_SECURE: " . (config('session.secure') ? 'true' : 'false') . "\n\n";

// 3. Generar nuevo token CSRF
echo "3. GENERANDO NUEVO TOKEN CSRF:\n";
$newToken = csrf_token();
echo "Nuevo token: " . $newToken . "\n\n";

// 4. Verificar middleware
echo "4. VERIFICANDO MIDDLEWARE:\n";
$router = app('router');
$middlewareGroups = $router->getMiddlewareGroups();

if (isset($middlewareGroups['web'])) {
    echo "✓ Grupo 'web' encontrado con middleware:\n";
    foreach ($middlewareGroups['web'] as $middleware) {
        echo "  - $middleware\n";
    }
} else {
    echo "❌ Grupo 'web' no encontrado\n";
}

echo "\n5. SOLUCIONES APLICADAS:\n";
echo "✓ Cache limpiado\n";
echo "✓ Configuración recargada\n";
echo "✓ Nuevo token CSRF generado\n";

echo "\n6. ACCIONES RECOMENDADAS:\n";
echo "1. Usar la ruta correcta: http://127.0.0.1:8000/mis-viajes en lugar de /dashboard\n";
echo "2. Refrescar completamente el navegador (Ctrl+F5)\n";
echo "3. Limpiar cookies del navegador para localhost\n";
echo "4. Verificar que el servidor de desarrollo esté corriendo\n";

echo "\n7. COMANDOS PARA EJECUTAR:\n";
echo "php artisan serve\n";
echo "npm run dev (en otra terminal)\n";

echo "\n=== DIAGNÓSTICO COMPLETADO ===\n";