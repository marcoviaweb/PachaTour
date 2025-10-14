# Plan de Reorganización por Features - Pacha Tour

## 🎯 Objetivos
Completar la migración hacia una arquitectura 100% organizada por features, moviendo todos los componentes de cada funcionalidad a su feature correspondiente.

## 📋 Estado Actual vs Deseado

### Modelos - REORGANIZAR

#### Estado Actual (MVC Tradicional)
```
app/Models/
├── Department.php      ❌ Mover a Departments feature
├── Attraction.php      ❌ Mover a Attractions feature  
├── Tour.php           ❌ Mover a Tours feature
├── TourSchedule.php   ❌ Mover a Tours feature
├── Booking.php        ❌ Mover a Tours feature
├── Review.php         ❌ Mover a Reviews feature
├── Media.php          ❌ Mover a Shared (usado por múltiples)
├── User.php           ✅ Puede quedarse (modelo central)
├── UserActivity.php   ❌ Mover a Users feature
└── UserFavorite.php   ❌ Mover a Users feature
```

#### Estado Deseado (Por Features)
```
app/Features/
├── Departments/
│   ├── Controllers/    ✅ Ya implementado
│   ├── Models/         ❌ CREAR - Department.php
│   └── Services/       ✅ Ya implementado
├── Attractions/
│   ├── Controllers/    ✅ Ya implementado  
│   ├── Models/         ❌ CREAR - Attraction.php
│   └── Services/       ✅ Ya implementado
├── Tours/
│   ├── Controllers/    ✅ Ya implementado
│   ├── Models/         ❌ CREAR - Tour.php, TourSchedule.php, Booking.php
│   ├── Services/       ✅ Ya implementado
│   └── Requests/       ✅ Ya implementado
├── Users/
│   ├── Controllers/    ✅ Ya implementado
│   ├── Models/         ❌ CREAR - UserActivity.php, UserFavorite.php
│   └── Services/       ✅ Ya implementado
├── Reviews/
│   ├── Controllers/    ✅ Ya implementado
│   ├── Models/         ❌ CREAR - Review.php
│   └── Services/       ✅ Ya implementado
├── Payments/
│   ├── Controllers/    ✅ Ya implementado
│   ├── Models/         ✅ Ya implementado (Payment, Commission)
│   └── Services/       ✅ Ya implementado
└── Shared/
    ├── Models/         ❌ CREAR - Media.php (usado por múltiples features)
    └── Services/       ❌ CREAR - SharedServices
```

### Frontend - REORGANIZAR

#### Estado Actual
```
resources/js/
├── Pages/              ❌ Mezcla features sin consistencia
│   ├── Attractions/    ✅ Bien organizado
│   ├── Departments/    ✅ Bien organizado
│   ├── Tours/          ✅ Bien organizado
│   ├── User/           ✅ Bien organizado
│   ├── Auth/           ✅ Bien organizado
│   ├── Search.vue      ❌ Debería estar en Pages/Search/
│   └── Welcome.vue     ✅ Página principal OK
└── Components/         ❌ Sin organización por features
```

#### Estado Deseado
```
resources/js/
├── Pages/
│   ├── Attractions/    ✅ Mantener
│   ├── Departments/    ✅ Mantener  
│   ├── Tours/          ✅ Mantener
│   ├── Users/          ✅ Mantener (renombrar de User/)
│   ├── Auth/           ✅ Mantener
│   ├── Search/         ❌ CREAR - mover Search.vue aquí
│   ├── Admin/          ❌ CREAR - páginas administrativas
│   └── Welcome.vue     ✅ Mantener
├── Components/
│   ├── Shared/         ❌ CREAR - componentes reutilizables
│   ├── Attractions/    ❌ CREAR - componentes específicos
│   ├── Tours/          ❌ CREAR - componentes específicos
│   ├── Users/          ❌ CREAR - componentes específicos
│   └── Admin/          ❌ CREAR - componentes administrativos
└── Services/
    ├── api.js          ✅ Mantener como servicio central
    └── features/       ❌ CREAR - servicios por feature
        ├── attractionsApi.js
        ├── toursApi.js
        └── usersApi.js
```

## 🚀 Plan de Implementación

### Fase 1: Reorganizar Modelos (2-3 horas)

1. **Crear directorios Models en cada feature**
```bash
mkdir app/Features/Departments/Models
mkdir app/Features/Attractions/Models  
mkdir app/Features/Tours/Models
mkdir app/Features/Users/Models
mkdir app/Features/Reviews/Models
mkdir app/Shared/Models
```

2. **Mover modelos a sus features**
```bash
# Departments
mv app/Models/Department.php app/Features/Departments/Models/

# Attractions  
mv app/Models/Attraction.php app/Features/Attractions/Models/

# Tours
mv app/Models/Tour.php app/Features/Tours/Models/
mv app/Models/TourSchedule.php app/Features/Tours/Models/
mv app/Models/Booking.php app/Features/Tours/Models/

# Users
mv app/Models/UserActivity.php app/Features/Users/Models/
mv app/Models/UserFavorite.php app/Features/Users/Models/

# Reviews
mv app/Models/Review.php app/Features/Reviews/Models/

# Shared (usado por múltiples features)
mv app/Models/Media.php app/Shared/Models/
```

3. **Actualizar namespaces en modelos movidos**
```php
// Ejemplo: app/Features/Departments/Models/Department.php
<?php
namespace App\Features\Departments\Models;  // ❌ Cambiar namespace

// app/Features/Attractions/Models/Attraction.php  
<?php
namespace App\Features\Attractions\Models;  // ❌ Cambiar namespace
```

4. **Actualizar composer.json autoload**
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "App\\Features\\Departments\\": "app/Features/Departments/",
      "App\\Features\\Attractions\\": "app/Features/Attractions/",
      "App\\Features\\Tours\\": "app/Features/Tours/",
      "App\\Features\\Users\\": "app/Features/Users/",
      "App\\Features\\Reviews\\": "app/Features/Reviews/",
      "App\\Features\\Payments\\": "app/Features/Payments/",
      "App\\Features\\Admin\\": "app/Features/Admin/",
      "App\\Features\\Search\\": "app/Features/Search/",
      "App\\Shared\\": "app/Shared/"
    }
  }
}
```

5. **Actualizar todas las importaciones**
```php
// En controladores, actualizar imports
use App\Features\Departments\Models\Department;
use App\Features\Attractions\Models\Attraction;
use App\Features\Tours\Models\Tour;
// etc.
```

### Fase 2: Reorganizar Frontend (3-4 horas)

1. **Reorganizar Pages**
```bash
# Crear estructura Search
mkdir resources/js/Pages/Search
mv resources/js/Pages/Search.vue resources/js/Pages/Search/Index.vue

# Renombrar User a Users para consistencia
mv resources/js/Pages/User resources/js/Pages/Users
```

2. **Crear estructura Components por feature**
```bash
mkdir -p resources/js/Components/{Shared,Attractions,Tours,Users,Admin,Search}
```

3. **Reorganizar servicios API**
```bash
mkdir resources/js/services/features
# Dividir api.js por features
```

### Fase 3: Testing y Validación (1-2 horas)

1. **Ejecutar tests**
```bash
composer dump-autoload
php artisan test
```

2. **Verificar rutas funcionan**
```bash
php artisan route:list
```

3. **Comprobar frontend compila**
```bash
npm run build
```

## 📦 Beneficios Esperados

### ✅ Ventajas de la Reorganización Completa

1. **Cohesión Máxima**: Todo el código de una feature en un solo lugar
2. **Desarrollo en Equipo**: Cada desarrollador puede trabajar en una feature completa
3. **Mantenimiento**: Cambios en una feature no afectan otras
4. **Testing**: Tests organizados por funcionalidad
5. **Documentación**: Cada feature es auto-contenida
6. **Deployment**: Posibilidad de deployment modular en el futuro

### ⚠️ Riesgos y Mitigaciones

1. **Imports Masivos**: Se requiere actualizar todos los use statements
   - **Mitigación**: Usar find & replace en IDE
   
2. **Tests Rotos**: Los tests pueden fallar por cambios de namespace
   - **Mitigación**: Ejecutar tests después de cada cambio

3. **Tiempo de Desarrollo**: La reorganización toma tiempo
   - **Mitigación**: Hacer en fases, feature por feature

## 🎯 Resultado Final Esperado

```
app/Features/
├── Departments/
│   ├── Controllers/ ✅
│   ├── Models/ ✅     # Department.php
│   └── Services/ ✅
├── Attractions/
│   ├── Controllers/ ✅  
│   ├── Models/ ✅     # Attraction.php
│   └── Services/ ✅
├── Tours/
│   ├── Controllers/ ✅
│   ├── Models/ ✅     # Tour.php, TourSchedule.php, Booking.php
│   ├── Services/ ✅
│   └── Requests/ ✅
├── Users/
│   ├── Controllers/ ✅
│   ├── Models/ ✅     # UserActivity.php, UserFavorite.php  
│   └── Services/ ✅
├── Reviews/
│   ├── Controllers/ ✅
│   ├── Models/ ✅     # Review.php
│   └── Services/ ✅
├── Payments/
│   ├── Controllers/ ✅
│   ├── Models/ ✅     # Payment.php, Commission.php (ya están)
│   └── Services/ ✅
├── Admin/
│   ├── Controllers/ ✅
│   └── Services/ ✅
└── Search/
    ├── Controllers/ ✅
    └── Services/ ✅

app/Shared/
└── Models/            # Media.php (usado por múltiples features)

app/Models/            
└── User.php          # Solo modelo central compartido

resources/js/
├── Pages/
│   ├── Attractions/ ✅
│   ├── Departments/ ✅
│   ├── Tours/ ✅
│   ├── Users/ ✅
│   ├── Auth/ ✅
│   ├── Search/ ✅
│   └── Admin/ ✅
├── Components/
│   ├── Shared/ ✅     # Componentes reutilizables
│   ├── Attractions/ ✅
│   ├── Tours/ ✅
│   └── Admin/ ✅
└── Services/
    ├── api.js ✅      # Cliente HTTP base
    └── features/ ✅   # APIs por feature
```