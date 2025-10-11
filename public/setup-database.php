<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🛠️ Configuración Automática de PostgreSQL - Pacha Tour</h2>";

// Cargar variables de entorno
require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} else {
    echo "❌ Archivo .env no encontrado<br>";
    exit;
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '5432';
$dbname = $_ENV['DB_DATABASE'] ?? 'pacha_tour_db';
$username = $_ENV['DB_USERNAME'] ?? 'postgres';
$password = $_ENV['DB_PASSWORD'] ?? '';

echo "<h3>Paso 1: Verificar conexión a PostgreSQL (sin base de datos específica)</h3>";

try {
    // Conectar a PostgreSQL sin especificar base de datos
    $dsn = "pgsql:host={$host};port={$port};dbname=postgres";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Conexión a PostgreSQL exitosa<br>";
    
    // Verificar si la base de datos existe
    $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
    $stmt->execute([$dbname]);
    $exists = $stmt->fetchColumn();
    
    if ($exists) {
        echo "✅ La base de datos '{$dbname}' ya existe<br>";
    } else {
        echo "ℹ️ La base de datos '{$dbname}' no existe. Creándola...<br>";
        
        // Crear la base de datos
        $pdo->exec("CREATE DATABASE {$dbname} WITH ENCODING 'UTF8'");
        echo "✅ Base de datos '{$dbname}' creada exitosamente<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    
    if (strpos($e->getMessage(), 'no password supplied') !== false) {
        echo "<br><strong>🔧 Solución:</strong><br>";
        echo "1. Configura una contraseña para el usuario postgres<br>";
        echo "2. O actualiza DB_PASSWORD en el archivo .env<br>";
        echo "3. O configura PostgreSQL para permitir conexiones sin contraseña localmente<br>";
    }
    
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<br><strong>🔧 Solución:</strong><br>";
        echo "1. Asegúrate de que PostgreSQL esté ejecutándose<br>";
        echo "2. Verifica que esté escuchando en el puerto {$port}<br>";
    }
    
    exit;
}

echo "<br><h3>Paso 2: Probar conexión a la base de datos específica</h3>";

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Conexión a '{$dbname}' exitosa<br>";
    
    // Obtener información
    $info = $pdo->query("SELECT current_database(), current_user, version()")->fetch();
    echo "Base de datos actual: <strong>{$info[0]}</strong><br>";
    echo "Usuario actual: <strong>{$info[1]}</strong><br>";
    echo "Versión PostgreSQL: <strong>" . substr($info[2], 0, 50) . "...</strong><br>";
    
} catch (PDOException $e) {
    echo "❌ Error conectando a '{$dbname}': " . $e->getMessage() . "<br>";
    exit;
}

echo "<br><h3>Paso 3: Configurar Laravel</h3>";

try {
    // Cargar aplicación Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "✅ Laravel cargado<br>";
    
    // Verificar configuración de base de datos
    $config = $app->make('config');
    $dbConfig = $config->get('database.connections.pgsql');
    
    echo "Configuración Laravel:<br>";
    echo "- Host: {$dbConfig['host']}<br>";
    echo "- Puerto: {$dbConfig['port']}<br>";
    echo "- Base de datos: {$dbConfig['database']}<br>";
    echo "- Usuario: {$dbConfig['username']}<br>";
    
} catch (Exception $e) {
    echo "❌ Error cargando Laravel: " . $e->getMessage() . "<br>";
}

echo "<br><h3>✅ Configuración Completada</h3>";
echo "<p><strong>Próximos pasos:</strong></p>";
echo "<ol>";
echo "<li>Ejecuta: <code>php artisan migrate:install</code></li>";
echo "<li>Ejecuta: <code>php artisan migrate</code></li>";
echo "<li>Verifica: <code>php artisan migrate:status</code></li>";
echo "</ol>";

echo "<br><p><a href='/test-database.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Probar Conexión Nuevamente</a></p>";
?>