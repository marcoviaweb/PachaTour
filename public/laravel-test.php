<?php
echo "<h2>Prueba de Laravel - Pacha Tour</h2>";

try {
    echo "1. Cargando autoloader...<br>";
    require __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoloader cargado<br>";

    echo "2. Cargando variables de entorno...<br>";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "✅ Variables de entorno cargadas<br>";

    echo "3. Cargando aplicación Laravel...<br>";
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "✅ Aplicación Laravel cargada<br>";

    echo "4. Creando kernel HTTP...<br>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "✅ Kernel HTTP creado<br>";

    echo "5. Probando configuración...<br>";
    echo "APP_NAME: " . $_ENV['APP_NAME'] ?? 'No definido' . "<br>";
    echo "APP_ENV: " . $_ENV['APP_ENV'] ?? 'No definido' . "<br>";
    echo "✅ Variables de entorno accesibles<br>";

    echo "6. Probando base de datos...<br>";
    try {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '5432';
        $dbname = $_ENV['DB_DATABASE'] ?? 'pacha_tour_db';
        $username = $_ENV['DB_USERNAME'] ?? 'postgres';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        
        $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$dbname}", $username, $password);
        echo "✅ Conexión a PostgreSQL exitosa<br>";
    } catch (Exception $e) {
        echo "⚠️ Error de conexión a BD (normal si no está configurada): " . $e->getMessage() . "<br>";
    }

    echo "7. Probando estructura por features...<br>";
    $departmentService = new \App\Features\Departments\Services\DepartmentService();
    $departments = $departmentService->getAllDepartments();
    echo "✅ Estructura por features funcionando - " . count($departments) . " departamentos<br>";

    echo "<br><strong>🎉 Laravel está funcionando correctamente!</strong>";

} catch (Exception $e) {
    echo "<br><strong>❌ Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>