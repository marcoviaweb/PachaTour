<?php

echo "🔍 INSTRUCCIONES DE DEBUGGING EN NAVEGADOR\n";
echo "==========================================\n\n";

echo "He agregado logging completo al componente BookingForm.vue.\n";
echo "Ahora podrás ver exactamente qué está pasando paso a paso.\n\n";

echo "📋 PASOS PARA DEBUGGING:\n\n";

echo "1️⃣ PREPARACIÓN:\n";
echo "   - Ir a: http://127.0.0.1:8000/login\n";
echo "   - Iniciar sesión con: pachatour@yopmail.com\n";
echo "   - Abrir DevTools (F12)\n";
echo "   - Ir a la pestaña 'Console'\n";
echo "   - Limpiar la consola (Ctrl+L)\n\n";

echo "2️⃣ PROBAR EL FORMULARIO:\n";
echo "   - Ir a cualquier atracción (ej: Valle de la Luna)\n";
echo "   - Hacer clic en 'Planificar Visita'\n";
echo "   - Llenar el formulario:\n";
echo "     * Fecha: cualquier fecha futura\n";
echo "     * Visitantes: 2 personas\n";
echo "     * Teléfono: +591 70123456\n";
echo "     * Notas: Prueba con debugging completo\n";
echo "   - Hacer clic en 'Guardar Planificación'\n\n";

echo "3️⃣ REVISAR LA CONSOLA:\n";
echo "   Deberías ver mensajes como estos:\n\n";

echo "   🚀 INICIANDO submitForm()\n";
echo "   canSubmit: true\n";
echo "   isAuthenticated: true\n";
echo "   user: {name: 'Juan Perez', email: '...'}\n";
echo "   📦 Datos de planificación preparados: {...}\n";
echo "   🔐 CSRF Token: PRESENTE\n";
echo "   🌐 Enviando petición a /planificar-visita...\n";
echo "   📡 Respuesta recibida:\n";
echo "      - Status: 201 (o el código que sea)\n";
echo "   📄 Contenido de la respuesta: {...}\n";
echo "   ✅ ¡ÉXITO! Planificación guardada (si funciona)\n\n";

echo "4️⃣ REVISAR NETWORK TAB:\n";
echo "   - Ir a la pestaña 'Network' en DevTools\n";
echo "   - Filtrar por 'Fetch/XHR'\n";
echo "   - Buscar la petición a 'planificar-visita'\n";
echo "   - Hacer clic en ella para ver:\n";
echo "     * Request Headers\n";
echo "     * Request Payload\n";
echo "     * Response Headers\n";
echo "     * Response Body\n\n";

echo "5️⃣ POSIBLES RESULTADOS:\n\n";

echo "   CASO A: No aparece nada en consola\n";
echo "   → El formulario no se está ejecutando\n";
echo "   → Problema: Vue.js no está funcionando\n\n";

echo "   CASO B: Aparece 'canSubmit: false'\n";
echo "   → El formulario está deshabilitado\n";
echo "   → Problema: Validación del frontend\n\n";

echo "   CASO C: Aparece 'Usuario no autenticado'\n";
echo "   → El usuario no está logueado correctamente\n";
echo "   → Problema: Autenticación\n\n";

echo "   CASO D: Aparece 'CSRF Token: AUSENTE'\n";
echo "   → No hay token CSRF\n";
echo "   → Problema: Configuración de Laravel\n\n";

echo "   CASO E: Status 401/403\n";
echo "   → Problema de autenticación en el servidor\n";
echo "   → Revisar middleware\n\n";

echo "   CASO F: Status 422\n";
echo "   → Error de validación\n";
echo "   → Revisar datos enviados\n\n";

echo "   CASO G: Status 500\n";
echo "   → Error interno del servidor\n";
echo "   → Revisar logs de Laravel\n\n";

echo "   CASO H: Status 201 + mensaje de éxito\n";
echo "   → ¡FUNCIONA! El problema estaba en el logging\n\n";

echo "6️⃣ CÓDIGO DE PRUEBA ALTERNATIVO:\n";
echo "   Si el formulario no funciona, ejecutar en consola:\n\n";

$jsCode = "
// CÓDIGO DE PRUEBA MANUAL
console.log('🧪 INICIANDO PRUEBA MANUAL');

// Verificar autenticación
fetch('/api/auth/me', {
    headers: { 'Accept': 'application/json' },
    credentials: 'include'
}).then(r => r.json()).then(data => {
    console.log('👤 Usuario autenticado:', data.user?.name || 'NO AUTENTICADO');
}).catch(e => console.log('❌ Error auth:', e));

// Probar ruta directamente
const testData = {
    attraction_id: 1,
    visit_date: '2025-10-25',
    visitors_count: 2,
    contact_name: 'Juan Perez',
    contact_email: 'pachatour@yopmail.com',
    contact_phone: '+591 70123456',
    notes: 'Prueba manual desde consola',
    estimated_total: 300.00
};

const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content');

fetch('/planificar-visita', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
    },
    credentials: 'include',
    body: JSON.stringify(testData)
}).then(async response => {
    console.log('📡 Status:', response.status);
    const result = await response.json();
    console.log('📄 Respuesta:', result);
    
    if (response.ok) {
        console.log('✅ ¡FUNCIONA! Booking ID:', result.data?.id);
    } else {
        console.log('❌ Error:', result.message);
    }
}).catch(error => {
    console.error('❌ Error de red:', error);
});
";

echo $jsCode;

echo "\n7️⃣ ANÁLISIS DE RESULTADOS:\n\n";

echo "   Si el código manual FUNCIONA pero el formulario NO:\n";
echo "   → Problema en el componente Vue.js\n";
echo "   → Revisar eventos, validaciones, etc.\n\n";

echo "   Si NADA funciona:\n";
echo "   → Problema de autenticación/CSRF\n";
echo "   → Usuario no está realmente logueado\n\n";

echo "   Si TODO funciona:\n";
echo "   → El problema era de logging/feedback\n";
echo "   → El formulario sí guardaba datos\n\n";

echo "8️⃣ VERIFICAR BASE DE DATOS:\n";
echo "   Después de cada prueba, ejecutar:\n\n";

echo "   php -r \"\n";
echo "   require 'vendor/autoload.php';\n";
echo "   \$app = require 'bootstrap/app.php';\n";
echo "   \$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();\n";
echo "   \$count = \\\\App\\\\Models\\\\Booking::where('user_id', 24)->count();\n";
echo "   echo 'Bookings de Juan: ' . \$count . \\\"\\\\n\\\";\n";
echo "   \"\n\n";

echo "🎯 OBJETIVO:\n";
echo "============\n";
echo "Con este debugging completo podremos identificar EXACTAMENTE\n";
echo "dónde está fallando el proceso y solucionarlo definitivamente.\n\n";

echo "💡 IMPORTANTE:\n";
echo "==============\n";
echo "- Mantén la consola abierta todo el tiempo\n";
echo "- Copia TODOS los mensajes que aparezcan\n";
echo "- Si hay errores, copia el stack trace completo\n";
echo "- Revisa tanto Console como Network tabs\n\n";

echo "==========================================\n";