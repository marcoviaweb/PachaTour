# Guía de Pruebas - Pacha Tour

## 🚀 Cómo Probar las Funcionalidas las funcionalidades implementadas en el sistema de autenticación y dashboard de usuario.

## 🚀 Preparación del Entorno

### 1. Verificar que el servidor esté funcionando
```bash
php artisan serve
```

### 2. Compilar assets de Vue.js
```bash
npm run dev
```

### 3. Verificar base de datos
```bash
php artisan migrate:fresh --seed
```

## 🔧 Rutas de Prueba Disponibles

### Verificación del Sistema
- **GET** `/simple` - Verificar que Laravel funciona
- **GET** `/test` - Test básico con información del sistema
- **GET** `/test-db` - Verificar conexión a base de datos
- **GET** `/test-models` - Verificar modelos Eloquent
- **GET** `/test-departments` - Verificar API de departamentos
- **GET** `/test-attractions` - Verificar API de atractivos

### Páginas Principales
- **GET** `/` - Página principal (Welcome)
- **GET** `/departamentos` - Lista de departamentos
- **GET** `/atractivos` - Lista de atractivos
- **GET** `/buscar` - Página de búsqueda

### Páginas de Usuario (requieren autenticación)
- **GET** `/mis-viajes` - Dashboard de usuario
- **GET** `/perfil` - Perfil de usuario

## 🧪 Pruebas de Funcionalidad

### 1. Sistema de Autenticación

#### A. Registro de Usuario
1. Ve a la página principal (`/`)
2. Haz clic en "Registrarse" o busca el botón de autenticación
3. Completa el formulario con:
   - **Nombre**: Juan Pérez
   - **Email**: juan@example.com
   - **Contraseña**: MiPassword123
   - **Confirmar Contraseña**: MiPassword123
   - ✅ Acepto términos y condiciones
4. Verifica que:
   - La validación funciona en tiempo real
   - Los indicadores de fortaleza de contraseña aparecen
   - El botón se activa solo cuando todo es válido

#### B. Inicio de Sesión
1. Usa las credenciales:
   - **Email**: juan@example.com
   - **Contraseña**: MiPassword123
2. Verifica:
   - Opción "Recordarme" funciona
   - Redirección después del login
   - Manejo de errores con credenciales incorrectas

#### C. Autenticación Social
1. Prueba los botones de Google y Facebook
2. Verifica que redirigen correctamente (aunque no estén configurados completamente)

#### D. Recuperación de Contraseña
1. Haz clic en "¿Olvidaste tu contraseña?"
2. Ingresa un email válido
3. Verifica la validación del formulario

### 2. Dashboard de Usuario

#### A. Acceso al Dashboard
1. Inicia sesión con un usuario
2. Ve a `/mis-viajes`
3. Verifica que aparecen:
   - Estadísticas del usuario (reservas activas, completadas, etc.)
   - Tabs de navegación
   - Interfaz responsive

#### B. Gestión de Reservas
1. **Próximas Reservas**:
   - Verifica que se muestran las reservas futuras
   - Prueba los botones "Ver Detalles", "Modificar", "Cancelar"
   - Verifica alertas de pago pendiente

2. **Historial de Reservas**:
   - Revisa reservas pasadas
   - Prueba el botón "Escribir Reseña" para tours completados
   - Verifica el botón "Cargar más"

#### C. Sistema de Reseñas
1. **Escribir Reseña**:
   - Selecciona calificación (1-5 estrellas)
   - Escribe título y comentario
   - Verifica validación de longitud
   - Prueba el envío

2. **Gestionar Reseñas**:
   - Ve al tab "Mis Reseñas"
   - Prueba editar reseñas existentes
   - Verifica el estado de moderación

#### D. Sistema de Favoritos
1. **Agregar Favoritos**:
   - Ve a una página de atractivo
   - Haz clic en el botón de favoritos
   - Verifica que se agrega a la lista

2. **Gestionar Favoritos**:
   - Ve al tab "Favoritos" en el dashboard
   - Prueba eliminar favoritos
   - Verifica recomendaciones basadas en favoritos

### 3. Perfil de Usuario

#### A. Actualizar Información Personal
1. Ve a `/perfil`
2. Actualiza campos como:
   - Nombre y apellido
   - Teléfono
   - Fecha de nacimiento
   - Nacionalidad y país
   - Biografía
3. Verifica validación y guardado

#### B. Cambiar Contraseña
1. En la sección "Cambiar Contraseña"
2. Ingresa:
   - Contraseña actual
   - Nueva contraseña
   - Confirmación
3. Verifica validación de seguridad

### 4. API Endpoints

#### A. Endpoints de Dashboard (requieren autenticación)
```bash
# Obtener estadísticas
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/user/dashboard/stats

# Próximas reservas
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/user/bookings/upcoming

# Historial de reservas
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/user/bookings/history

# Reseñas del usuario
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/user/reviews

# Favoritos del usuario
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/user/favorites
```

#### B. Endpoints de Perfil
```bash
# Obtener perfil
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/user/profile

# Actualizar perfil
curl -X PUT -H "Authorization: Bearer TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"name":"Juan Updated","phone":"+591 12345678"}' \
     http://localhost:8000/api/user/profile

# Cambiar contraseña
curl -X POST -H "Authorization: Bearer TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"current_password":"old","new_password":"new123","new_password_confirmation":"new123"}' \
     http://localhost:8000/api/user/change-password
```

## 🧪 Ejecutar Tests Automatizados

### Tests PHP (Backend)
```bash
# Tests de autenticación
php artisan test --filter="AuthModalTest"

# Tests de dashboard de usuario
php artisan test --filter="UserDashboardTest"

# Todos los tests
php artisan test
```

### Tests JavaScript (Frontend)
```bash
# Tests de componentes Vue
npm test

# Tests específicos
npm test -- AuthModal.test.js
```

## 🐛 Solución de Problemas Comunes

### Error 404 en rutas
- Verifica que `php artisan serve` esté ejecutándose
- Confirma que las rutas estén definidas en `routes/web.php`

### Error de autenticación en API
- Verifica que el usuario esté autenticado
- Confirma que el token de Sanctum sea válido
- Revisa middleware de autenticación

### Componentes Vue no cargan
- Ejecuta `npm run dev` para compilar assets
- Verifica que no haya errores de JavaScript en consola
- Confirma que Inertia.js esté configurado correctamente

### Base de datos vacía
```bash
# Recrear base de datos con datos de prueba
php artisan migrate:fresh --seed
```

## 📊 Datos de Prueba

### Usuarios de Ejemplo
- **Admin**: admin@pachatur.com / password
- **Turista**: turista@example.com / password
- **Usuario**: usuario@example.com / password

### Departamentos Disponibles
- La Paz, Santa Cruz, Cochabamba, Potosí, Chuquisaca, Oruro, Tarija, Beni, Pando

### Atractivos de Ejemplo
- Salar de Uyuni, Lago Titicaca, Cristo de la Concordia, etc.

## 🎯 Checklist de Funcionalidades

### ✅ Autenticación
- [ ] Registro de usuario con validación
- [ ] Inicio de sesión con remember me
- [ ] Recuperación de contraseña
- [ ] Autenticación social (Google/Facebook)
- [ ] Logout seguro

### ✅ Dashboard de Usuario
- [ ] Estadísticas personalizadas
- [ ] Gestión de reservas próximas
- [ ] Historial de reservas
- [ ] Sistema de reseñas
- [ ] Gestión de favoritos

### ✅ Perfil de Usuario
- [ ] Actualización de información personal
- [ ] Cambio de contraseña
- [ ] Preferencias de notificaciones
- [ ] Validación de formularios

### ✅ API y Backend
- [ ] Endpoints de autenticación
- [ ] Endpoints de dashboard
- [ ] Middleware de seguridad
- [ ] Validación de datos
- [ ] Manejo de errores

## 📝 Notas Adicionales

- Todas las contraseñas de prueba son: `password`
- Los emails de recuperación requieren configuración SMTP
- Las notificaciones push requieren configuración adicional
- Los pagos requieren integración con gateway de pago

## 🔗 Enlaces Útiles

- **Documentación Laravel**: https://laravel.com/docs
- **Documentación Vue.js**: https://vuejs.org/guide/
- **Documentación Inertia.js**: https://inertiajs.com/
- **Documentación Tailwind CSS**: https://tailwindcss.com/docs