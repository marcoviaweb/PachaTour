<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🗄️ Prueba de Conexión a PostgreSQL - Pacha Tour</h2>";

// Cargar variables de entorno
require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "✅ Variables de entorno cargadas<br><br>";
} else {
    echo "❌ Archivo .env no encontrado<br><br>";
    exit;
}

// Obtener configuración de BD
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '5432';
$dbname = $_ENV['DB_DATABASE'] ?? 'pacha_tour_db';
$username = $_ENV['DB_USERNAME'] ?? 'postgres';
$password = $_ENV['DB_PASSWORD'] ?? '';

echo "<h3>📋 Configuración de Base de Datos:</h3>";
echo "Host: <strong>{$host}</strong><br>";
echo "Puerto: <strong>{$port}</strong><br>";
echo "Base de Datos: <strong>{$dbname}</strong><br>";
echo "Usuario: <strong>{$username}</strong><br>";
echo "Contraseña: <strong>" . (empty($password) ? 'No definida' : 'Definida') . "</strong><br><br>";

// Prueba 1: Conexión básica con PDO
echo "<h3>🔌 Prueba 1: Conexión PDO Básica</h3>";
try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    echo "DSN: {$dsn}<br>";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ <strong>Conexión PDO exitosa!</strong><br>";
    
    // Obtener información del servidor
    $version = $pdo->query('SELECT version()')->fetchColumn();
    echo "Versión PostgreSQL: {$version}<br>";
    
    $pdo = null; // Cerrar conexión
    
} catch (PDOException $e) {
    echo "❌ <strong>Error de conexión PDO:</strong> " . $e->getMessage() . "<br>";
    echo "Código de error: " . $e->getCode() . "<br>";
}

echo "<br>";

// Prueba 2: Conexión con Laravel
echo "<h3>🚀 Prueba 2: Conexión Laravel</h3>";
try {
    // Cargar aplicación Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    echo "✅ Laravel cargado<br>";
    
    // Probar conexión usando Laravel
    $connection = $app->make('db');
    $pdo = $connection->getPdo();
    
    echo "✅ <strong>Conexión Laravel exitosa!</strong><br>";
    
    // Probar una consulta simple
    $result = $connection->select('SELECT NOW() as current_time, current_database() as database_name');
    $current = $result[0];
    
    echo "Tiempo actual: <strong>{$current->current_time}</strong><br>";
    echo "Base de datos actual: <strong>{$current->database_name}</strong><br>";
    
} catch (Exception $e) {
    echo "❌ <strong>Error de conexión Laravel:</strong> " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")<br>";
}

echo "<br>";

// Prueba 3: Verificar tablas existentes
echo "<h3>📊 Prueba 3: Verificar Estructura de BD</h3>";
try {
    if (isset($connection)) {
        // Listar tablas
        $tables = $connection->select("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            ORDER BY table_name
        ");
        
        if (empty($tables)) {
            echo "ℹ️ No hay tablas creadas aún (normal para instalación nueva)<br>";
            echo "💡 Ejecuta <code>php artisan migrate</code> para crear las tablas<br>";
        } else {
            echo "📋 <strong>Tablas encontradas:</strong><br>";
            foreach ($tables as $table) {
                echo "- {$table->table_name}<br>";
            }
        }
        
        // Verificar si existe la tabla de migraciones
        $migrationTable = $connection->select("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'migrations'
        ");
        
        if (empty($migrationTable)) {
            echo "<br>⚠️ Tabla 'migrations' no existe<br>";
            echo "💡 Ejecuta <code>php artisan migrate:install</code> para crearla<br>";
        } else {
            echo "<br>✅ Tabla 'migrations' existe<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error verificando estructura:</strong> " . $e->getMessage() . "<br>";
}

echo "<br>";

// Prueba 4: Comandos Artisan de BD
echo "<h3>🛠️ Comandos Artisan Recomendados:</h3>";
echo "<code>php artisan migrate:status</code> - Ver estado de migraciones<br>";
echo "<code>php artisan migrate:install</code> - Instalar tabla de migraciones<br>";
echo "<code>php artisan migrate</code> - Ejecutar migraciones<br>";
echo "<code>php artisan db:show</code> - Mostrar información de la BD<br>";
echo "<code>php artisan tinker</code> - Consola interactiva<br>";

echo "<br><hr>";
echo "<h3>🎯 Próximos Pasos:</h3>";
echo "1. Si la conexión es exitosa, ejecuta las migraciones<br>";
echo "2. Si hay errores, verifica la configuración en .env<br>";
echo "3. Asegúrate de que PostgreSQL esté ejecutándose<br>";
echo "4. Verifica que la base de datos 'pacha_tour_db' exista<br>";
?>