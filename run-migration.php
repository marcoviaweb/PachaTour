<?php

echo "🔧 Ejecutando migración para campos de planificación...\n\n";

echo "Comando a ejecutar:\n";
echo "php artisan migrate\n\n";

echo "Esta migración agregará:\n";
echo "✅ Campo planning_data (JSON) a la tabla bookings\n";
echo "✅ Hará tour_schedule_id nullable\n";
echo "✅ Agregará estado 'planned' al enum de status\n\n";

echo "💡 Después de ejecutar la migración, el formulario debería funcionar correctamente.\n";
echo "💡 Si hay errores de sintaxis SQL, puede ser por diferencias entre MySQL y PostgreSQL.\n\n";

echo "🚀 Ejecuta este comando en tu terminal:\n";
echo "php artisan migrate\n";