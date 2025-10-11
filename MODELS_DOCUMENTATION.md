# 🏗️ Documentación de Modelos Eloquent - Pacha Tour

## Resumen de Modelos Implementados

Se han implementado **8 modelos Eloquent** con todas sus relaciones, scopes, accessors y métodos auxiliares para el sistema completo de turismo de Bolivia.

### 📊 Modelos Principales

| Modelo | Propósito | Relaciones | Características Especiales |
|--------|-----------|------------|---------------------------|
| **Department** | 9 departamentos de Bolivia | attractions, media, reviews | Geolocalización, multiidioma |
| **Attraction** | Atractivos turísticos | department, tours, media, reviews | Tipos, dificultad, precios |
| **Tour** | Paquetes turísticos | schedules, attractions, bookings, media | Duración, capacidad, itinerarios |
| **TourSchedule** | Horarios específicos | tour, bookings | Disponibilidad, guías, clima |
| **Booking** | Reservas de tours | user, tourSchedule, reviews | Estados, pagos, comisiones |
| **Review** | Reseñas y calificaciones | user, reviewable (polimórfico), booking | Moderación, verificación |
| **Media** | Archivos multimedia | mediable (polimórfico) | Imágenes, videos, documentos |
| **User** | Usuarios del sistema | bookings, reviews | Roles, preferencias, perfil |

## 🔗 Mapa de Relaciones

### Relaciones Uno a Muchos (1:N)
```
Department (1) → Attractions (N)
Tour (1) → TourSchedules (N)
TourSchedule (1) → Bookings (N)
User (1) → Bookings (N)
User (1) → Reviews (N)
```

### Relaciones Muchos a Muchos (N:M)
```
Tours (N) ↔ Attractions (N) [tour_attraction]
```

### Relaciones Polimórficas (1:N)
```
Reviews → reviewable (Department, Attraction, Tour, etc.)
Media → mediable (Department, Attraction, Tour, etc.)
```

## 📋 Características Implementadas por Modelo

### 1. Department Model
```php
// Campos principales
- name, slug, capital, description
- latitude, longitude (geolocalización)
- population, area_km2, climate
- languages (JSON), gallery (JSON)

// Relaciones
- attractions() → HasMany
- activeAttractions() → HasMany (filtrado)
- media() → MorphMany
- images() → MorphMany (filtrado)
- reviews() → MorphMany

// Scopes
- active() → solo activos
- ordered() → por sort_order
- search($term) → búsqueda

// Métodos auxiliares
- getCoordinates() → array lat/lng
- hasAttractions() → boolean
- getImageUrlAttribute() → URL imagen
```

### 2. Attraction Model
```php
// Campos principales
- name, slug, description, type
- latitude, longitude, address, city
- entry_price, currency, opening_hours (JSON)
- difficulty_level, estimated_duration
- rating, reviews_count, visits_count

// Constantes
- 8 tipos: natural, cultural, historical, etc.
- TYPES array con nombres en español

// Relaciones
- department() → BelongsTo
- tours() → BelongsToMany (con pivot)
- media(), images(), videos() → MorphMany
- reviews(), approvedReviews() → MorphMany

// Scopes
- active(), featured(), ofType($type)
- inDepartment($id), search($term)
- priceRange($min, $max), nearby($lat, $lng, $radius)

// Métodos auxiliares
- incrementVisits(), updateRating()
- isOpenNow() → boolean
- getFormattedPriceAttribute()
```

### 3. Tour Model
```php
// Campos principales
- name, slug, description, type
- duration_days, duration_hours
- price_per_person, min/max_participants
- difficulty_level, itinerary (JSON)
- included/excluded_services (JSON)

// Constantes
- 8 tipos: day_trip, multi_day, adventure, etc.
- 4 niveles de dificultad: easy, moderate, difficult, extreme

// Relaciones
- schedules(), availableSchedules() → HasMany
- attractions() → BelongsToMany (con pivot)
- bookings() → HasManyThrough
- media(), images(), reviews() → MorphMany

// Scopes
- active(), featured(), ofType($type)
- byDifficulty($level), byDuration($days)
- priceRange($min, $max), availableOn($date)

// Métodos auxiliares
- isAvailableOn($date) → boolean
- getAvailabilityForRange($start, $end) → array
- updateRating(), updateBookingsCount()
- hasAvailableSpots() → boolean
```

### 4. TourSchedule Model
```php
// Campos principales
- tour_id, date, start_time, end_time
- available_spots, booked_spots
- status, guide_name, guide_contact
- weather_forecast, weather_conditions

// Constantes
- 4 estados: available, full, cancelled, completed

// Relaciones
- tour() → BelongsTo
- bookings(), confirmedBookings() → HasMany

// Scopes
- available(), withSpots(), onDate($date)
- upcoming(), today(), thisWeek()

// Métodos auxiliares
- getRemainingSpots() → int
- getOccupancyPercentage() → float
- canBeBooked() → boolean
- reserveSpots($count), releaseSpots($count)
- cancel($reason), markAsCompleted()
```

### 5. Booking Model
```php
// Campos principales
- booking_number (único), user_id, tour_schedule_id
- participants_count, total_amount, currency
- commission_rate, commission_amount
- status, payment_status, payment_method
- participant_details (JSON), special_requests (JSON)

// Constantes
- 7 estados de reserva: pending, confirmed, paid, etc.
- 5 estados de pago: pending, partial, paid, etc.

// Relaciones
- user() → BelongsTo
- tourSchedule() → BelongsTo
- tour() → BelongsTo (through)
- reviews() → HasMany

// Scopes
- withStatus($status), confirmed(), active()
- pendingPayment(), forUser($userId)
- today(), upcoming(), betweenDates($start, $end)

// Métodos auxiliares
- generateBookingNumber() → string único
- confirm(), markAsPaid(), cancel(), refund()
- calculateCommission(), getTourInfo()
```

### 6. Review Model
```php
// Campos principales
- user_id, reviewable_type/id (polimórfico)
- booking_id, rating, title, comment
- detailed_ratings (JSON), pros/cons (JSON)
- status, moderation_notes, is_verified

// Constantes
- 4 estados: pending, approved, rejected, hidden
- 5 tipos de viaje: solo, couple, family, etc.

// Relaciones
- user() → BelongsTo
- reviewable() → MorphTo (polimórfico)
- booking() → BelongsTo
- moderator() → BelongsTo

// Scopes
- approved(), pending(), verified()
- minRating($rating), inLanguage($lang)
- byHelpfulness(), recent(), search($term)

// Métodos auxiliares
- approve($moderatorId), reject($reason)
- verify(), voteHelpful(), voteNotHelpful()
- canBeEditedBy($user) → boolean
- getDetailedRatingsFormatted() → array
```

### 7. Media Model
```php
// Campos principales
- mediable_type/id (polimórfico)
- name, file_name, mime_type, type
- path, url, size, metadata (JSON)
- alt_text, description, caption
- is_featured, is_public, sort_order

// Constantes
- 4 tipos: image, video, audio, document
- Arrays de tipos MIME permitidos

// Relaciones
- mediable() → MorphTo (polimórfico)

// Scopes
- public(), featured(), ofType($type)
- images(), videos(), ordered(), search($term)

// Métodos auxiliares
- getTypeFromMimeType($mime) → string
- isMimeTypeAllowed($mime) → boolean
- markAsFeatured(), deleteFile()
- createThumbnail(), getThumbnailUrl()
- getFileInfo() → array completo
```

### 8. User Model (Extendido)
```php
// Campos adicionales
- last_name, role, phone, birth_date
- nationality, country, city
- preferred_language, interests (JSON)
- newsletter_subscription, marketing_emails
- last_login_at, last_login_ip, is_active

// Constantes
- 3 roles: visitor, tourist, admin

// Relaciones nuevas
- bookings() → HasMany
- reviews() → HasMany
- moderatedReviews() → HasMany

// Scopes nuevos
- active(), withRole($role)
- admins(), tourists()

// Métodos auxiliares
- isAdmin(), isTourist() → boolean
```

## 🎯 Funcionalidades Especiales

### Geolocalización
- **Departments** y **Attractions** tienen coordenadas GPS
- Scope `nearby()` para búsquedas por proximidad
- Método `getCoordinates()` para obtener lat/lng

### Sistema de Calificaciones
- **Attractions** y **Tours** mantienen rating promedio
- Método `updateRating()` actualiza automáticamente
- Reviews con calificaciones detalladas (JSON)

### Gestión de Archivos
- **Media** polimórfico para cualquier entidad
- Soporte para imágenes, videos, audio, documentos
- Thumbnails y transformaciones
- Detección de duplicados por hash

### Sistema de Reservas
- **Booking** con estados y pagos
- Generación automática de números únicos
- Cálculo de comisiones
- Gestión de cupos en **TourSchedule**

### Moderación de Contenido
- **Reviews** con sistema de moderación
- Estados: pending, approved, rejected, hidden
- Tracking de moderadores y fechas

### Multiidioma
- **Departments** con idiomas en JSON
- **Reviews** con campo de idioma
- **Users** con idioma preferido

## 🧪 Testing y Verificación

### URLs de Prueba
- `GET /test-models` - Verificar modelos y relaciones
- `GET /test-db` - Estado de base de datos
- `GET /test-database.php` - Diagnóstico completo

### Comandos Artisan
```bash
# Verificar modelos en Tinker
php artisan tinker
>>> $dept = App\Models\Department::first()
>>> $dept->attractions

# Verificar estructura
php artisan db:show
php artisan migrate:status
```

## 📝 Próximos Pasos

1. **Crear Factories** para generar datos de prueba
2. **Implementar Seeders** con datos reales de Bolivia
3. **Escribir Tests Unitarios** para relaciones
4. **Crear Form Requests** para validación
5. **Implementar Observers** para eventos automáticos

---

**Modelos implementados**: ✅ 8/8  
**Relaciones definidas**: ✅ 25+  
**Constantes y enums**: ✅ 50+  
**Métodos auxiliares**: ✅ 100+  
**Scopes personalizados**: ✅ 40+  

**🎉 Sistema de modelos Eloquent completamente funcional para Pacha Tour!**