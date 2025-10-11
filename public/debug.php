<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug de Laravel - Pacha Tour</h2>";

try {
    echo "1. Cargando autoloader...<br>";
    require __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoloader cargado<br>";

    echo "2. Cargando variables de entorno...<br>";
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
        echo "✅ Variables de entorno cargadas<br>";
        echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'No definido') . "<br>";
        echo "APP_KEY: " . (isset($_ENV['APP_KEY']) ? 'Definido' : 'No definido') . "<br>";
    } else {
        echo "❌ Archivo .env no encontrado<br>";
    }

    echo "3. Cargando aplicación Laravel...<br>";
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "✅ Aplicación Laravel cargada<br>";

    echo "4. Creando kernel HTTP...<br>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "✅ Kernel HTTP creado<br>";

    echo "5. Simulando request...<br>";
    $request = Illuminate\Http\Request::create('/simple', 'GET');
    echo "✅ Request creado<br>";

    echo "6. Procesando request...<br>";
    $response = $kernel->handle($request);
    echo "✅ Response generado<br>";
    echo "Status: " . $response->getStatusCode() . "<br>";
    echo "Content: " . substr($response->getContent(), 0, 100) . "...<br>";

} catch (Exception $e) {
    echo "<br><strong>❌ Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<br><strong>❌ Fatal Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>