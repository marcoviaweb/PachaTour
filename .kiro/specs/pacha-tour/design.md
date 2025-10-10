# Diseño - Pacha Tour

## Overview

Pacha Tour es una aplicación web monolítica construida con Laravel (backend) y Vue.js (frontend), utilizando PostgreSQL como base de datos. La arquitectura está organizada por features/funcionalidades para facilitar el mantenimiento y escalabilidad. El sistema maneja tres tipos principales de usuarios con diferentes niveles de acceso y funcionalidades específicas para cada rol.

La aplicación implementa un modelo de negocio basado en comisiones por reservas, integrando un sistema de pagos robusto y herramientas de gestión para operadores turísticos y administradores.

## Arquitectura

### Arquitectura General
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │    Backend      │    │   Database      │
│   (Vue.js)      │◄──►│   (Laravel)     │◄──►│  (PostgreSQL)   │
│                 │    │                 │    │                 │
│ - Components    │    │ - API Routes    │    │ - Tables        │
│ - Pages         │    │ - Controllers   │    │ - Relationships │
│ - State Mgmt    │    │ - Services      │    │ - Indexes       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Organización por Features
```
app/Features/
├── Departments/     # Gestión de departamentos bolivianos
├── Attractions/     # Atractivos turísticos y multimedia
├── Tours/          # Recorridos, horarios y reservas
├── Users/          # Autenticación y gestión de usuarios
├── Payments/       # Sistema de pagos y comisiones
├── Reviews/        # Valoraciones y comentarios
├── Admin/          # Backoffice y herramientas administrativas
└── Localization/   # Soporte multilingüe
```

## Components and Interfaces

### Frontend Components (Vue.js)

#### Core Components
```javascript
// Componentes principales de navegación
- AppHeader.vue          // Header con navegación y autenticación
- DepartmentGrid.vue     // Grid de los 9 departamentos
- AttractionCard.vue     // Tarjeta individual de atractivo
- SearchBar.vue          // Barra de búsqueda con autocompletado
- FilterPanel.vue        // Panel de filtros avanzados
- InteractiveMap.vue     // Mapa interactivo con marcadores

// Componentes de usuario
- AuthModal.vue          // Modal de login/registro
- UserDashboard.vue      // Panel "Mi Viaje"
- BookingForm.vue        // Formulario de reserva
- PaymentForm.vue        // Formulario de pago
- ReviewForm.vue         // Formulario de valoración

// Componentes de administración
- AdminDashboard.vue     // Panel principal de admin
- AttractionManager.vue  // CRUD de atractivos
- MediaUploader.vue      // Subida de imágenes/videos
- BookingManager.vue     // Gestión de reservas
- UserManager.vue        // Gestión de usuarios
```

#### State Management (Vuex/Pinia)
```javascript
// Stores principales
- authStore.js           // Estado de autenticación
- attractionsStore.js    // Catálogo de atractivos
- bookingsStore.js       // Reservas del usuario
- filtersStore.js        // Estado de filtros y búsqueda
- localizationStore.js   // Idioma y traducciones
```

### Backend Architecture (Laravel)

#### Controllers por Feature
```php
// Departments Feature
DepartmentController     // Listado y detalle de departamentos
DepartmentApiController  // API endpoints para departamentos

// Attractions Feature
AttractionController     // CRUD de atractivos
AttractionApiController  // API pública de atractivos
MediaController          // Gestión de multimedia

// Tours Feature
TourController           // Gestión de tours y horarios
BookingController        // Proceso de reservas
AvailabilityController   // Consulta de disponibilidad

// Users Feature
AuthController           // Autenticación y registro
UserController           // Gestión de perfil
SocialAuthController     // Login con redes sociales

// Payments Feature
PaymentController        // Procesamiento de pagos
CommissionController     // Cálculo de comisiones
InvoiceController        // Generación de comprobantes

// Reviews Feature
ReviewController         // CRUD de valoraciones
ModerationController     // Moderación de contenido

// Admin Feature
AdminController          // Dashboard administrativo
ReportController         // Reportes y estadísticas
```

#### Services Layer
```php
// Servicios de negocio
AttractionService        // Lógica de atractivos
BookingService          // Lógica de reservas
PaymentService          // Procesamiento de pagos
CommissionService       // Cálculo de comisiones
NotificationService     // Envío de notificaciones
MediaService            // Gestión de archivos multimedia
SearchService           // Motor de búsqueda
LocalizationService     // Gestión de traducciones
```

## Data Models

### Core Entities

#### Departments
```sql
departments
├── id (PK)
├── name (varchar)
├── slug (varchar, unique)
├── description (text)
├── image_path (varchar)
├── coordinates (point)
├── created_at
└── updated_at
```

#### Attractions
```sql
attractions
├── id (PK)
├── department_id (FK)
├── name (varchar)
├── slug (varchar, unique)
├── description (text)
├── practical_info (json)
├── tourism_type (enum)
├── coordinates (point)
├── altitude (integer)
├── climate_info (text)
├── how_to_get_there (text)
├── status (enum: active, inactive)
├── created_at
└── updated_at
```

#### Tours
```sql
tours
├── id (PK)
├── attraction_id (FK)
├── name (varchar)
├── description (text)
├── duration (integer) -- en horas
├── max_capacity (integer)
├── price (decimal)
├── currency (varchar)
├── status (enum: active, inactive)
├── created_at
└── updated_at
```

#### Tour Schedules
```sql
tour_schedules
├── id (PK)
├── tour_id (FK)
├── day_of_week (integer) -- 0=domingo, 6=sábado
├── start_time (time)
├── end_time (time)
├── available_spots (integer)
├── created_at
└── updated_at
```

#### Users
```sql
users
├── id (PK)
├── name (varchar)
├── email (varchar, unique)
├── email_verified_at (timestamp)
├── password (varchar)
├── role (enum: visitor, tourist, admin)
├── phone (varchar)
├── nationality (varchar)
├── preferred_language (varchar)
├── social_provider (varchar, nullable)
├── social_id (varchar, nullable)
├── created_at
└── updated_at
```

#### Bookings
```sql
bookings
├── id (PK)
├── user_id (FK)
├── tour_id (FK)
├── booking_date (date)
├── booking_time (time)
├── number_of_people (integer)
├── total_amount (decimal)
├── commission_amount (decimal)
├── status (enum: pending, confirmed, cancelled, completed)
├── payment_status (enum: pending, paid, refunded)
├── payment_method (varchar)
├── payment_reference (varchar)
├── special_requests (text)
├── created_at
└── updated_at
```

#### Reviews
```sql
reviews
├── id (PK)
├── user_id (FK)
├── attraction_id (FK)
├── booking_id (FK)
├── rating (integer) -- 1-5
├── title (varchar)
├── comment (text)
├── status (enum: pending, approved, rejected)
├── created_at
└── updated_at
```

#### Media
```sql
media
├── id (PK)
├── mediable_type (varchar) -- attractions, departments
├── mediable_id (integer)
├── type (enum: image, video)
├── file_path (varchar)
├── file_name (varchar)
├── file_size (integer)
├── mime_type (varchar)
├── alt_text (varchar)
├── sort_order (integer)
├── created_at
└── updated_at
```

### Relationships
```php
// Department Model
public function attractions() {
    return $this->hasMany(Attraction::class);
}

// Attraction Model
public function department() {
    return $this->belongsTo(Department::class);
}
public function tours() {
    return $this->hasMany(Tour::class);
}
public function media() {
    return $this->morphMany(Media::class, 'mediable');
}
public function reviews() {
    return $this->hasMany(Review::class);
}

// Tour Model
public function attraction() {
    return $this->belongsTo(Attraction::class);
}
public function schedules() {
    return $this->hasMany(TourSchedule::class);
}
public function bookings() {
    return $this->hasMany(Booking::class);
}

// User Model
public function bookings() {
    return $this->hasMany(Booking::class);
}
public function reviews() {
    return $this->hasMany(Review::class);
}

// Booking Model
public function user() {
    return $this->belongsTo(User::class);
}
public function tour() {
    return $this->belongsTo(Tour::class);
}
```

## Error Handling

### Frontend Error Handling
```javascript
// Interceptor global para errores HTTP
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response.status === 401) {
      // Redirigir a login
      router.push('/login');
    } else if (error.response.status === 422) {
      // Errores de validación
      showValidationErrors(error.response.data.errors);
    } else if (error.response.status >= 500) {
      // Errores del servidor
      showErrorNotification('Error interno del servidor');
    }
    return Promise.reject(error);
  }
);
```

### Backend Error Handling
```php
// Handler personalizado en Laravel
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $exception->errors()
                ], 422);
            }
            
            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'No autenticado'
                ], 401);
            }
            
            if ($exception instanceof AuthorizationException) {
                return response()->json([
                    'message' => 'No autorizado'
                ], 403);
            }
        }
        
        return parent::render($request, $exception);
    }
}
```

### Error Logging
```php
// Logging personalizado para diferentes tipos de errores
Log::channel('bookings')->error('Error en reserva', [
    'user_id' => $userId,
    'tour_id' => $tourId,
    'error' => $exception->getMessage()
]);

Log::channel('payments')->error('Error en pago', [
    'booking_id' => $bookingId,
    'amount' => $amount,
    'payment_method' => $paymentMethod,
    'error' => $exception->getMessage()
]);
```

## Testing Strategy

### Frontend Testing
```javascript
// Unit Tests (Jest + Vue Test Utils)
- Component rendering tests
- User interaction tests
- State management tests
- API integration tests

// E2E Tests (Cypress)
- Complete booking flow
- Authentication flow
- Admin management flow
- Search and filter functionality
```

### Backend Testing
```php
// Unit Tests (PHPUnit)
- Model relationships
- Service layer logic
- Validation rules
- Helper functions

// Feature Tests
- API endpoints
- Authentication middleware
- Payment processing
- Commission calculations

// Integration Tests
- Database transactions
- External API integrations
- Email notifications
- File upload handling
```

### Testing Data
```php
// Factories para datos de prueba
AttractionFactory::class
TourFactory::class
UserFactory::class
BookingFactory::class
ReviewFactory::class

// Seeders para datos iniciales
DepartmentSeeder::class
AttractionSeeder::class
TourSeeder::class
AdminUserSeeder::class
```

## Security Considerations

### Authentication & Authorization
- JWT tokens para API authentication
- Role-based access control (RBAC)
- Social login integration (Google, Facebook)
- Password hashing con bcrypt/argon2

### Data Protection
- Input validation en todos los endpoints
- CSRF protection en formularios
- XSS prevention con output escaping
- SQL injection prevention con Eloquent ORM

### File Upload Security
- Validación de tipos de archivo permitidos
- Límites de tamaño de archivo
- Almacenamiento fuera del document root
- Antivirus scanning para archivos subidos

### Payment Security
- PCI DSS compliance
- Tokenización de datos de tarjetas
- Integración con gateways certificados
- Logging de transacciones para auditoría

## Performance Optimization

### Database Optimization
- Indexes en campos de búsqueda frecuente
- Query optimization con eager loading
- Database connection pooling
- Read replicas para consultas pesadas

### Caching Strategy
```php
// Cache de datos frecuentemente consultados
Cache::remember('departments', 3600, function () {
    return Department::with('attractions')->get();
});

Cache::remember("attraction.{$id}", 1800, function () use ($id) {
    return Attraction::with(['media', 'tours', 'reviews'])->find($id);
});
```

### Frontend Optimization
- Lazy loading de componentes
- Image optimization y lazy loading
- Bundle splitting por rutas
- Service worker para caching offline

### CDN Integration
- Almacenamiento de media en CDN
- Distribución global de assets estáticos
- Optimización automática de imágenes
- Compresión gzip/brotli