# Requerimientos del Sistema - Pacha Tour

## Estado Actual del Sistema
Este documento refleja el estado actual de implementación del sistema Pacha Tour, desarrollado con Laravel 11 + Vue.js 3.

---

## Historia del Usuario 1: Exploración de Destinos ✅ IMPLEMENTADO
Como turista interesado en Bolivia, quiero explorar información sobre diferentes destinos turísticos (departamentos y atractivos) para poder planificar mejor mi viaje.

**Criterios de Aceptación:**
- ✅ Ver listado de los 9 departamentos de Bolivia con información básica
- ✅ Acceder a página detallada de cada departamento
- ✅ Explorar atractivos turísticos por departamento
- ✅ Ver información detallada de cada atractivo (descripción, imágenes, ubicación)
- ✅ Visualizar multimedia (imágenes, videos) de alta calidad

**Funcionalidades Implementadas:**
- **Modelos**: `Department`, `Attraction`, `Media` con relaciones Eloquent completas
- **Controladores**: `DepartmentController`, `AttractionController`, `AttractionApiController`
- **Páginas Vue**: `Departments/Index.vue`, `Departments/Show.vue`, `Attractions/Index.vue`, `Attractions/Show.vue`
- **API Endpoints**: Rutas públicas para consulta de departamentos y atractivos
- **Base de Datos**: 9 departamentos bolivianos con atractivos georreferenciados

## Historia del Usuario 2: Búsqueda y Filtrado ✅ IMPLEMENTADO
Como turista, quiero buscar y filtrar destinos según mis preferencias para encontrar rápidamente lo que me interesa.

**Criterios de Aceptación:**
- ✅ Buscar destinos por nombre o palabras clave
- ✅ Filtrar por tipo de turismo (aventura, cultural, natural, gastronómico, etc.)
- ✅ Filtrar por departamento/región
- ✅ Filtrar por dificultad y duración estimada
- ✅ Ver resultados ordenados por relevancia o calificación

**Funcionalidades Implementadas:**
- **Servicios**: `SearchService`, `FilterService` con múltiples criterios de búsqueda
- **Controladores**: `SearchController`, `FilterController`
- **Páginas Vue**: `Search.vue` con interfaz de búsqueda avanzada
- **Filtros Avanzados**: Por precio, rating, distancia, amenidades, dificultad
- **Autocompletado**: Sugerencias dinámicas de búsqueda

## Historia del Usuario 3: Autenticación y Perfil ✅ IMPLEMENTADO
Como usuario, quiero crear una cuenta y gestionar mi perfil para acceder a funcionalidades personalizadas.

**Criterios de Aceptación:**
- ✅ Registrarse con email y contraseña
- ✅ Iniciar sesión con credenciales
- ✅ Cerrar sesión de forma segura
- ✅ Editar información del perfil
- ✅ Cambiar contraseña
- ✅ Recuperar contraseña olvidada

**Funcionalidades Implementadas:**
- **Autenticación**: Laravel Sanctum para API authentication
- **Modelo**: `User` con roles (admin, tourist, operator)
- **Controladores**: `AuthController`, `UserController`, `UserDashboardController`
- **Páginas Vue**: `Auth/Login.vue`, `Auth/Register.vue`, `User/Profile.vue`
- **Middleware**: Protección de rutas por roles y autenticación
- **Dashboard**: Panel de usuario con estadísticas personales

## Historia del Usuario 4: Sistema de Reservas ✅ IMPLEMENTADO
Como turista autenticado, quiero realizar reservas de tours para asegurar mi participación en actividades específicas.

**Criterios de Aceptación:**
- ✅ Ver tours disponibles para cada atractivo
- ✅ Consultar horarios y disponibilidad de tours
- ✅ Seleccionar fecha y número de participantes
- ✅ Completar proceso de reserva con información personal
- ✅ Recibir confirmación de reserva
- ✅ Ver mis reservas activas y historial

**Funcionalidades Implementadas:**
- **Modelos**: `Tour`, `TourSchedule`, `Booking` con relaciones completas
- **Controladores**: `BookingController`, `AvailabilityController`, `TourScheduleController`
- **Servicios**: `BookingService` con lógica de negocio y validaciones
- **Páginas Vue**: `Tours/Index.vue`, `Tours/Show.vue` con proceso de reserva
- **APIs**: Endpoints para verificar disponibilidad y gestionar reservas
- **Estados**: Sistema completo de estados de reserva (pending, confirmed, completed, cancelled)

## Historia del Usuario 5: Gestión de Itinerario Personal ✅ IMPLEMENTADO
Como turista, quiero organizar mis actividades en un itinerario personalizado para optimizar mi tiempo de viaje.

**Criterios de Aceptación:**
- ✅ Agregar atractivos a una lista de favoritos
- ✅ Crear itinerarios personalizados con múltiples destinos
- ✅ Organizar actividades por fechas
- ✅ Ver estimaciones de tiempo y distancia entre destinos
- ✅ Modificar y eliminar elementos del itinerario

**Funcionalidades Implementadas:**
- **Modelo**: `UserFavorite` para gestión de favoritos
- **Controlador**: `UserDashboardController` con gestión de favoritos e itinerarios
- **Dashboard Personal**: Vista unificada de reservas, favoritos y recomendaciones
- **Planificación**: Endpoint para planificar visitas con múltiples destinos
- **Recomendaciones**: Sistema de sugerencias basado en preferencias

## Historia del Usuario 6: Sistema de Valoraciones ✅ IMPLEMENTADO
Como turista, quiero leer y escribir valoraciones sobre atractivos y tours para compartir experiencias y tomar mejores decisiones.

**Criterios de Aceptación:**
- ✅ Leer valoraciones y calificaciones de otros usuarios
- ✅ Escribir valoraciones después de completar un tour
- ✅ Calificar tours con sistema de estrellas (1-5)
- ✅ Subir fotos relacionadas con la experiencia
- ✅ Ver valoraciones moderadas y aprobadas

**Funcionalidades Implementadas:**
- **Modelo**: `Review` con sistema polimórfico (atractivos y tours)
- **Controladores**: `ReviewController`, `ModerationController`
- **Estados**: Sistema de moderación (pending, approved, rejected)
- **API**: Endpoints para CRUD de reseñas con autenticación
- **Tests**: Cobertura completa de funcionalidad de reseñas

## Historia del Usuario 7: Panel Administrativo (Backoffice) ✅ IMPLEMENTADO
Como administrador, quiero gestionar el contenido y operaciones del sistema para mantener información actualizada y monitorear la actividad.

**Criterios de Aceptación:**
- ✅ Dashboard con métricas clave del sistema
- ✅ Gestión de departamentos y atractivos
- ✅ Administración de tours y horarios
- ✅ Gestión de usuarios y roles
- ✅ Moderación de valoraciones y contenido
- ✅ Reportes de reservas y estadísticas

**Funcionalidades Implementadas:**
- **Controladores Admin**: `AdminController`, `ReportController`
- **Dashboard**: Métricas en tiempo real (usuarios, reservas, ingresos)
- **CRUD Completo**: Gestión total de tours, atractivos, usuarios
- **Reportes**: Sistema de reportes por fechas y categorías
- **Estadísticas**: Análisis de rendimiento por atractivo y departamento
- **Moderación**: Panel de aprobación/rechazo de contenido

## Historia del Usuario 8: Soporte Multiidioma ⚠️ PARCIALMENTE IMPLEMENTADO
Como turista internacional, quiero acceder al sistema en mi idioma preferido para una mejor comprensión.

**Criterios de Aceptación:**
- ⚠️ Cambiar idioma de la interfaz (Español/Inglés mínimo)
- ⚠️ Contenido traducido dinámicamente
- ⚠️ Mantener preferencia de idioma en sesiones
- ⚠️ Soporte para múltiples monedas en precios

**Estado Actual:**
- Estructura preparada para internacionalización
- Interfaz principalmente en español
- Sistema de monedas implementado en modelos

## Historia del Usuario 9: Recursos de Viaje 📋 PENDIENTE
Como turista, quiero acceder a información práctica sobre Bolivia para planificar mejor mi viaje.

**Criterios de Aceptación:**
- ❌ Información sobre clima y mejor época para visitar
- ❌ Consejos de seguridad y salud
- ❌ Información sobre transporte y alojamiento
- ❌ Guías culturales y etiqueta local
- ❌ Mapas interactivos y rutas recomendadas

**Estado Actual:**
- Pendiente de implementación
- Estructura de base de datos preparada para contenido informativo

## Historia del Usuario 10: Sistema de Comisiones ✅ IMPLEMENTADO
Como operador turístico, quiero que el sistema gestione automáticamente las comisiones por reservas para facilitar la administración financiera.

**Criterios de Aceptación:**
- ✅ Cálculo automático de comisiones por reserva
- ✅ Dashboard de ganancias para operadores
- ✅ Reportes financieros detallados
- ✅ Sistema de pagos y liquidaciones
- ✅ Trazabilidad de transacciones financieras

**Funcionalidades Implementadas:**
- **Modelos**: `Commission`, `Payment` con relaciones a reservas
- **Servicios**: `PaymentService`, `CommissionService`
- **Controladores**: `PaymentController`, `CommissionController`
- **Reportes**: Análisis financiero completo por período
- **Integración**: Sistema preparado para pasarelas de pago

---

## Arquitectura Técnica Implementada

### Backend (Laravel 11)
- **Estructura por Features**: Organización modular (`app/Features/`)
- **Modelos Eloquent**: 12 modelos principales con relaciones optimizadas
- **API REST**: 85+ endpoints con autenticación Sanctum
- **Testing**: 45+ tests unitarios y de integración
- **Base de Datos**: PostgreSQL con migraciones completas

### Frontend (Vue.js 3 + Inertia.js)
- **Páginas**: 15+ componentes Vue con Composition API
- **Servicios API**: Cliente HTTP organizado por funcionalidad
- **Componentes Reutilizables**: TourCard, AttractionCard, etc.
- **Navegación SPA**: Routing sin recarga de página

### Funcionalidades Transversales
- **Autenticación**: Sistema completo con roles y permisos
- **Multimedia**: Gestión de imágenes con ordenamiento
- **Búsqueda**: Motor de búsqueda con filtros avanzados
- **Geolocalización**: Coordenadas GPS para todos los atractivos
- **Auditoría**: Logs de actividad y trazabilidad completa

---

## Métricas del Sistema
- **Cobertura de Funcionalidad**: ~85%
- **Endpoints API**: 85+ rutas implementadas
- **Tests Automatizados**: 45+ casos de prueba
- **Modelos de Datos**: 12 modelos principales
- **Páginas de Usuario**: 15+ vistas implementadas
- **Controladores**: 25+ controladores por feature