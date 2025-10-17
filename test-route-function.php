<?php
// test-route-function.php
// Verificar que las rutas están correctas en ziggy.js

$ziggyContent = file_get_contents(__DIR__ . '/resources/js/ziggy.js');

echo "=== VERIFICACIÓN RUTAS ZIGGY ===\n\n";

// Extraer el objeto Ziggy del contenido
preg_match('/const Ziggy = ({.*?});/s', $ziggyContent, $matches);
if (isset($matches[1])) {
    $ziggyData = json_decode($matches[1], true);
    
    echo "Rutas admin.departments encontradas:\n";
    foreach ($ziggyData['routes'] as $routeName => $routeConfig) {
        if (strpos($routeName, 'admin.departments') === 0) {
            echo "- $routeName: " . $routeConfig['uri'] . "\n";
        }
    }
} else {
    echo "❌ No se pudo extraer datos de Ziggy\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";