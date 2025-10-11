<?php
echo "PHP está funcionando correctamente!<br>";
echo "Fecha: " . date('Y-m-d H:i:s') . "<br>";
echo "Versión PHP: " . PHP_VERSION . "<br>";
echo "Directorio actual: " . __DIR__ . "<br>";

// Verificar si Laravel está disponible
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "✅ Composer autoload encontrado<br>";
} else {
    echo "❌ Composer autoload NO encontrado<br>";
}

if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
    echo "✅ Bootstrap de Laravel encontrado<br>";
} else {
    echo "❌ Bootstrap de Laravel NO encontrado<br>";
}

if (file_exists(__DIR__ . '/../.env')) {
    echo "✅ Archivo .env encontrado<br>";
} else {
    echo "❌ Archivo .env NO encontrado<br>";
}
?>