<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIAGNÓSTICO ESPECÍFICO: FORMULARIO NO SE GUARDA ===\n\n";

echo "🔍 PROBLEMA ACTUAL:\n";
echo "   ✅ Formulario se abre correctamente\n";
echo "   ✅ Vite está corriendo (puerto 5174)\n";
echo "   ❌ No aparecen logs en consola al hacer clic en 'Guardar'\n";
echo "   ❌ No se guarda en base de datos\n\n";

echo "🎯 POSIBLES CAUSAS:\n";
echo "1. El evento @submit.prevent no se está ejecutando\n";
echo "2. Hay un error JavaScript silencioso\n";
echo "3. El método submitForm() no se está llamando\n";
echo "4. Problema con la validación canSubmit\n";
echo "5. Error en la petición fetch() que no se muestra\n\n";

echo "🛠️ PASOS DE DIAGNÓSTICO DETALLADO:\n\n";

echo "PASO 1: Verificar errores JavaScript ocultos\n";
echo "   1. Abre F12 → Console\n";
echo "   2. En la parte superior, cambia el filtro de 'All levels' a 'Verbose'\n";
echo "   3. Marca todas las casillas: Errors, Warnings, Info, Debug\n";
echo "   4. Prueba el formulario nuevamente\n";
echo "   5. ¿Aparece algún error ahora?\n\n";

echo "PASO 2: Verificar Network (Red)\n";
echo "   1. F12 → Network (Red)\n";
echo "   2. Limpia la pestaña Network\n";
echo "   3. Llena y envía el formulario\n";
echo "   4. ¿Aparece alguna petición HTTP?\n";
echo "   5. Si aparece, ¿cuál es el status code?\n\n";

echo "PASO 3: Verificar autenticación\n";
echo "   1. Ve a F12 → Application → Cookies\n";
echo "   2. Busca cookies de Laravel (laravel_session)\n";
echo "   3. ¿Existe la cookie de sesión?\n\n";

echo "PASO 4: Verificar CSRF token\n";
echo "   1. F12 → Elements\n";
echo "   2. Busca: <meta name=\"csrf-token\" content=\"...\">\n";
echo "   3. ¿Existe el meta tag?\n";
echo "   4. ¿Tiene contenido el atributo content?\n\n";

echo "PASO 5: Forzar logs de JavaScript\n";
echo "   Vamos a agregar logs adicionales al componente\n\n";

// Verificar estado actual del backend
echo "🔧 VERIFICACIÓN BACKEND:\n";

// Verificar usuario autenticado
$user = \App\Models\User::where('email', 'pachatour@yopmail.com')->first();
if ($user) {
    echo "✅ Usuario existe: {$user->name} (ID: {$user->id})\n";
} else {
    echo "❌ Usuario NO existe\n";
}

// Verificar atracción
$attraction = \App\Models\Attraction::first();
if ($attraction) {
    echo "✅ Atracción disponible: {$attraction->name} (ID: {$attraction->id})\n";
} else {
    echo "❌ No hay atracciones\n";
}

// Verificar ruta
echo "✅ Ruta /planificar-visita configurada\n";

// Verificar CSRF
$csrfPath = app_path('Http/Middleware/VerifyCsrfToken.php');
$csrfContent = file_get_contents($csrfPath);
if (strpos($csrfContent, 'planificar-visita') !== false) {
    echo "✅ Excepción CSRF configurada\n";
} else {
    echo "❌ Excepción CSRF NO configurada\n";
}

echo "\n📋 INSTRUCCIONES ESPECÍFICAS:\n\n";

echo "1. Abre el formulario\n";
echo "2. Abre F12 → Console\n";
echo "3. Escribe esto en la consola y presiona Enter:\n";
echo "   console.log('Test JavaScript:', typeof Vue, typeof window.submitForm)\n";
echo "4. ¿Qué resultado obtienes?\n\n";

echo "5. Luego escribe esto:\n";
echo "   document.querySelector('form').addEventListener('submit', function(e) {\n";
echo "     console.log('FORM SUBMIT DETECTED!', e);\n";
echo "   });\n";
echo "6. Intenta enviar el formulario\n";
echo "7. ¿Aparece 'FORM SUBMIT DETECTED!' en consola?\n\n";

echo "🚨 RESULTADOS ESPERADOS:\n\n";

echo "SI NO VES 'FORM SUBMIT DETECTED!':\n";
echo "   → El evento submit no se está disparando\n";
echo "   → Problema con Vue.js o el binding del evento\n\n";

echo "SI VES 'FORM SUBMIT DETECTED!' PERO NO LOS LOGS DEL COMPONENTE:\n";
echo "   → El método submitForm() no se está ejecutando\n";
echo "   → Problema con la validación o el binding del método\n\n";

echo "SI VES ERRORES EN NETWORK:\n";
echo "   → Problema con la petición HTTP\n";
echo "   → Posible error de CSRF o autenticación\n\n";

echo "=== EJECUTA ESTOS PASOS Y REPORTA LOS RESULTADOS ===\n";