<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIAGNÓSTICO FRONTEND - FORMULARIO NO SE GUARDA ===\n\n";

echo "🔍 POSIBLES CAUSAS:\n";
echo "1. JavaScript no se está ejecutando\n";
echo "2. Evento submit no se está disparando\n";
echo "3. Validación del formulario falla silenciosamente\n";
echo "4. Usuario no está autenticado\n";
echo "5. Problema con el token CSRF en el frontend\n";
echo "6. Servidor de desarrollo no está corriendo\n\n";

echo "🛠️ PASOS DE DIAGNÓSTICO:\n\n";

echo "1️⃣ VERIFICAR CONSOLA DEL NAVEGADOR:\n";
echo "   - Abre F12 → Console\n";
echo "   - Busca mensajes que empiecen con 🚀, ✅, ❌\n";
echo "   - Si NO ves estos mensajes, el JavaScript no se ejecuta\n\n";

echo "2️⃣ VERIFICAR AUTENTICACIÓN:\n";
echo "   - ¿Estás logueado en la aplicación?\n";
echo "   - Ve a /mis-viajes para confirmar\n";
echo "   - Si no estás logueado, haz login primero\n\n";

echo "3️⃣ VERIFICAR SERVIDORES:\n";
echo "   - Terminal 1: php artisan serve (debe estar corriendo)\n";
echo "   - Terminal 2: npm run dev (debe estar corriendo)\n";
echo "   - Verifica que no haya errores en ninguno\n\n";

echo "4️⃣ VERIFICAR CSRF TOKEN:\n";
echo "   - F12 → Elements → <head>\n";
echo "   - Busca: <meta name=\"csrf-token\" content=\"...\">\n";
echo "   - Si no existe, hay problema con el layout\n\n";

echo "5️⃣ VERIFICAR FORMULARIO:\n";
echo "   - ¿El botón 'Guardar Planificación' está habilitado?\n";
echo "   - ¿Completaste todos los campos obligatorios?\n";
echo "   - ¿La fecha es futura (no hoy ni pasado)?\n\n";

// Verificar estado del backend
echo "🔧 VERIFICACIÓN DEL BACKEND:\n";

// Verificar usuario de prueba
$user = \App\Models\User::where('email', 'pachatour@yopmail.com')->first();
if ($user) {
    echo "✅ Usuario de prueba existe: {$user->name}\n";
} else {
    echo "❌ Usuario de prueba NO existe\n";
    echo "   Crea uno con: php artisan db:seed --class=UserSeeder\n";
}

// Verificar atracción
$attraction = \App\Models\Attraction::first();
if ($attraction) {
    echo "✅ Atracción disponible: {$attraction->name}\n";
} else {
    echo "❌ No hay atracciones en la base de datos\n";
}

// Verificar ruta
echo "✅ Ruta /planificar-visita configurada\n";
echo "✅ Excepción CSRF agregada\n";

echo "\n📋 INSTRUCCIONES PASO A PASO:\n\n";

echo "PASO 1: Verificar que estés logueado\n";
echo "   - Ve a: http://127.0.0.1:8000/login\n";
echo "   - Usuario: pachatour@yopmail.com\n";
echo "   - Contraseña: password\n\n";

echo "PASO 2: Ir a una atracción\n";
echo "   - Ve a: http://127.0.0.1:8000/atractivos\n";
echo "   - Haz clic en cualquier atracción\n\n";

echo "PASO 3: Abrir formulario\n";
echo "   - Haz clic en 'Planificar Visita'\n";
echo "   - Abre F12 → Console ANTES de llenar el formulario\n\n";

echo "PASO 4: Llenar formulario\n";
echo "   - Fecha: MAÑANA o cualquier fecha futura\n";
echo "   - Visitantes: 2 personas\n";
echo "   - Teléfono: +591 70123456 (opcional)\n";
echo "   - Notas: Cualquier texto (opcional)\n\n";

echo "PASO 5: Enviar formulario\n";
echo "   - Haz clic en 'Guardar Planificación'\n";
echo "   - OBSERVA la consola del navegador\n";
echo "   - Debe aparecer: 🚀 INICIANDO submitForm()\n\n";

echo "🚨 SI NO VES MENSAJES EN CONSOLA:\n";
echo "   - El problema es que JavaScript no se ejecuta\n";
echo "   - Verifica que npm run dev esté corriendo\n";
echo "   - Recarga la página con Ctrl+F5\n";
echo "   - Limpia cache del navegador\n\n";

echo "🚨 SI VES ERRORES EN CONSOLA:\n";
echo "   - Copia el error completo\n";
echo "   - Compártelo para análisis específico\n\n";

echo "=== DIAGNÓSTICO COMPLETADO ===\n";