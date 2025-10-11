# 🗄️ Estructura de Base de Datos - Pacha Tour

## Resumen de Tablas Creadas

La base de datos de Pacha Tour está diseñada para manejar un sistema completo de turismo con las siguientes entidades principales:

### 📊 Tablas Principales

| Tabla | Propósito | Campos Clave |
|-------|-----------|--------------|
| **departments** | 9 departamentos de Bolivia | name, slug, capital, coordinates |
| **attractions** | Atractivos turísticos | name, type, location, rating |
| **tours** | Tours y paquetes turísticos | name, duration, price, difficulty |
| **tour_schedules** | Horarios específicos de tours | date, time, available_spots |
| **users** | Usuarios del sistema | name, role, preferences |
| **bookings** | Reservas de tours | booking_number, status, payment |
| **reviews** | Reseñas y calificaciones | rating, comment, moderation |
| **media** | Archivos multimedia | images, videos, documents |
| **tour_attraction** | Relación tours-atractivos | visit_order, duration |

## 🏗️ Estructura Detallada

### 1. Departments (Departamentos)
```sql
- id (PK)
- name (unique) - Nombre del departamento
- slug (unique) - URL amigable
- capital - Ciudad capital
- description - Descripción completa
- latitude/longitude - Coordenadas GPS
- image_path - Imagen principal
- population - Población aproximada
- area_km2 - Área en kilómetros cuadrados
- climate - Tipo de clima
- languages (JSON) - Idiomas hablados
- is_active - Estado activo/inactivo
```

### 2. Attractions (Atractivos Turísticos)
```sql
- id (PK)
- department_id (FK) - Departamento al que pertenece
- name - Nombre del atractivo
- slug (unique) - URL amigable
- type - Tipo: natural, cultural, historical, etc.
- latitude/longitude - Coordenadas GPS
- entry_price - Precio de entrada
- opening_hours (JSON) - Horarios de atención
- difficulty_level - Nivel de dificultad
- rating - Calificación promedio
- is_featured - Atractivo destacado
```

### 3. Tours (Tours y Paquetes)
```sql
- id (PK)
- name - Nombre del tour
- type - Tipo: day_trip, multi_day, adventure, etc.
- duration_days/hours - Duración
- price_per_person - Precio por persona
- min/max_participants - Límites de participantes
- difficulty_level - Nivel de dificultad
- included/excluded_services (JSON) - Servicios
- itinerary (JSON) - Itinerario detallado
- guide_language - Idioma del guía
```

### 4. Tour Schedules (Horarios de Tours)
```sql
- id (PK)
- tour_id (FK) - Tour relacionado
- date - Fecha del tour
- start_time/end_time - Horarios
- available_spots - Cupos disponibles
- booked_spots - Cupos reservados
- status - Estado: available, full, cancelled
- guide_name - Guía asignado
```

### 5. Users (Usuarios)
```sql
- id (PK)
- name/last_name - Nombres
- email (unique) - Correo electrónico
- role - Rol: visitor, tourist, admin
- phone - Teléfono
- nationality/country - Nacionalidad y país
- preferred_language - Idioma preferido
- interests (JSON) - Intereses turísticos
- is_active - Estado activo
```

### 6. Bookings (Reservas)
```sql
- id (PK)
- booking_number (unique) - Número de reserva
- user_id (FK) - Usuario que reserva
- tour_schedule_id (FK) - Horario específico
- participants_count - Número de participantes
- total_amount - Monto total
- commission_rate/amount - Comisión
- status - Estado: pending, confirmed, paid, etc.
- payment_status - Estado del pago
- participant_details (JSON) - Detalles de participantes
```

### 7. Reviews (Reseñas)
```sql
- id (PK)
- user_id (FK) - Usuario que reseña
- reviewable_type/id - Entidad reseñada (polimórfico)
- booking_id (FK) - Reserva relacionada
- rating - Calificación (1-5)
- comment - Comentario
- status - Estado: pending, approved, rejected
- is_verified - Reseña verificada
```

### 8. Media (Archivos Multimedia)
```sql
- id (PK)
- mediable_type/id - Entidad relacionada (polimórfico)
- name - Nombre del archivo
- type - Tipo: image, video, audio, document
- path/url - Ubicación del archivo
- size - Tamaño en bytes
- metadata (JSON) - Metadatos (dimensiones, etc.)
- is_featured - Archivo destacado
```

### 9. Tour Attraction (Tabla Pivote)
```sql
- id (PK)
- tour_id (FK) - Tour
- attraction_id (FK) - Atractivo
- visit_order - Orden de visita
- duration_minutes - Tiempo en el atractivo
- arrival_time/departure_time - Horarios
- is_optional - Visita opcional
```

## 🔗 Relaciones Principales

### Uno a Muchos (1:N)
- **Department → Attractions**: Un departamento tiene muchos atractivos
- **Tour → TourSchedules**: Un tour tiene muchos horarios
- **User → Bookings**: Un usuario puede tener muchas reservas
- **TourSchedule → Bookings**: Un horario puede tener muchas reservas
- **User → Reviews**: Un usuario puede escribir muchas reseñas

### Muchos a Muchos (N:M)
- **Tours ↔ Attractions**: Un tour puede visitar múltiples atractivos, un atractivo puede estar en múltiples tours

### Polimórficas (1:N)
- **Reviews**: Pueden ser para tours, atractivos, etc.
- **Media**: Pueden pertenecer a departamentos, atractivos, tours, etc.

## 📈 Índices y Optimizaciones

### Índices Principales
- **Búsquedas geográficas**: `(latitude, longitude)` en attractions
- **Filtros por tipo**: `(type, is_active)` en attractions y tours
- **Búsquedas por fecha**: `(date, status)` en tour_schedules
- **Filtros por rol**: `(role, is_active)` en users
- **Estado de reservas**: `(status, payment_status)` en bookings

### Constraints de Integridad
- **Foreign Keys**: Todas las relaciones tienen constraints
- **Unique Constraints**: Slugs únicos, números de reserva únicos
- **Check Constraints**: Validaciones de enums y rangos

## 🚀 Comandos Útiles

### Verificar Estructura
```bash
# Ver todas las tablas
php artisan db:show

# Ver estado de migraciones
php artisan migrate:status

# Ver estructura de una tabla específica
php artisan db:table departments
```

### Consultas de Prueba
```sql
-- Contar registros por tabla
SELECT 'departments' as tabla, COUNT(*) FROM departments
UNION ALL
SELECT 'attractions', COUNT(*) FROM attractions
UNION ALL
SELECT 'tours', COUNT(*) FROM tours;

-- Ver relaciones
SELECT 
    t.name as tour_name,
    a.name as attraction_name,
    ta.visit_order
FROM tours t
JOIN tour_attraction ta ON t.id = ta.tour_id
JOIN attractions a ON ta.attraction_id = a.id
ORDER BY t.name, ta.visit_order;
```

## 📝 Próximos Pasos

1. **Crear Modelos Eloquent** con relaciones
2. **Implementar Seeders** con datos de Bolivia
3. **Crear Factories** para testing
4. **Implementar Validaciones** en Form Requests
5. **Optimizar Consultas** con eager loading

---

**Estructura creada exitosamente el**: 2025-10-10  
**Total de tablas**: 12  
**Total de campos**: ~200  
**Relaciones implementadas**: ✅  
**Índices optimizados**: ✅