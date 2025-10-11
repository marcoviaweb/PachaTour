<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "🔍 Diagnóstico rápido del error 500...\n\n";

// 1. Verificar si la migración se ejecutó
echo "1️⃣ Verificando migración:\n";
try {
    $columns = Schema::getColumnListing('bookings');
    $hasPlanning = in_array('planning_data', $columns);
    echo "   - planning_data existe: " . ($hasPlanning ? "✅ SÍ" : "❌ NO") . "\n";
    
    if (!$hasPlanning) {
        echo "\n❌ PROBLEMA: La migración no se ejecutó.\n";
        echo "   Ejecuta: php artisan migrate\n\n";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error verificando tabla: " . $e->getMessage() . "\n";
    exit;
}

// 2. Verificar estructura de enum status
echo "\n2️⃣ Verificando enum status:\n";
try {
    $result = DB::select("SELECT column_name, data_type, column_default 
                         FROM information_schema.columns 
                         WHERE table_name = 'bookings' AND column_name = 'status'");
    
    if (!empty($result)) {
        echo "   - Columna status encontrada\n";
    } else {
        echo "❌ Columna status no encontrada\n";
    }
} catch (Exception $e) {
    echo "❌ Error verificando enum: " . $e->getMessage() . "\n";
}

// 3. Probar consulta simple
echo "\n3️⃣ Probando consulta básica:\n";
try {
    $count = DB::table('bookings')->count();
    echo "   - Total bookings: {$count}\n";
} catch (Exception $e) {
    echo "❌ Error en consulta básica: " . $e->getMessage() . "\n";
}

// 4. Probar consulta con planning_data
echo "\n4️⃣ Probando consulta con planning_data:\n";
try {
    $count = DB::table('bookings')->whereNotNull('planning_data')->count();
    echo "   - Bookings con planning_data: {$count}\n";
} catch (Exception $e) {
    echo "❌ Error con planning_data: " . $e->getMessage() . "\n";
}

// 5. Probar la consulta problemática
echo "\n5️⃣ Probando consulta JSON:\n";
try {
    $today = now()->toDateString();
    $count = DB::table('bookings')
        ->whereRaw("planning_data->>'visit_date' >= ?", [$today])
        ->count();
    echo "   - Bookings con fecha futura: {$count}\n";
} catch (Exception $e) {
    echo "❌ Error en consulta JSON: " . $e->getMessage() . "\n";
    echo "   Esto podría ser el problema principal.\n";
}

echo "\n✅ Diagnóstico completado.\n";