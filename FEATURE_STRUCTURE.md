# Estructura por Features - Pacha Tour

## Organización del Código

El proyecto Pacha Tour está organizado por **features/funcionalidades** en lugar de por tipo de archivo (controllers, models, etc.). Esta estructura facilita el mantenimiento y escalabilidad del código.

## Estructura de Directorios

```
app/Features/
├── Departments/        # Gestión de departamentos bolivianos
│   ├── Controllers/    # DepartmentController, DepartmentApiController
│   ├── Models/         # Department model
│   └── Services/       # DepartmentService
├── Attractions/        # Atractivos turísticos y multimedia
│   ├── Controllers/    # AttractionController, MediaController
│   ├── Models/         # Attraction, Media models
│   └── Services/       # AttractionService, MediaService
├── Tours/             # Recorridos, horarios y reservas
│   ├── Controllers/    # TourController, BookingController
│   ├── Models/         # Tour, TourSchedule, Booking models
│   └── Services/       # TourService, BookingService
├── Users/             # Autenticación y gestión de usuarios
│   ├── Controllers/    # AuthController, UserController
│   ├── Models/         # User model (ya en app/Models/)
│   └── Services/       # UserService, AuthService
├── Payments/          # Sistema de pagos y comisiones
│   ├── Controllers/    # PaymentController, CommissionController
│   ├── Models/         # Payment, Commission models
│   └── Services/       # PaymentService, CommissionService
├── Reviews/           # Valoraciones y comentarios
│   ├── Controllers/    # ReviewController, ModerationController
│   ├── Models/         # Review model
│   └── Services/       # ReviewService, ModerationService
├── Admin/             # Backoffice y herramientas administrativas
│   ├── Controllers/    # AdminController, ReportController
│   ├── Models/         # Admin-specific models
│   └── Services/       # AdminService, ReportService
└── Localization/      # Soporte multilingüe
    ├── Controllers/    # LocalizationController
    └── Services/       # LocalizationService
```

## Ventajas de esta Estructura

1. **Cohesión**: Todo el código relacionado con una funcionalidad está junto
2. **Mantenibilidad**: Fácil encontrar y modificar código específico
3. **Escalabilidad**: Agregar nuevas features sin afectar las existentes
4. **Colaboración**: Equipos pueden trabajar en features independientes
5. **Testing**: Tests organizados por funcionalidad

## Convenciones de Nomenclatura

### Namespaces
```php
// Controllers
App\Features\Departments\Controllers\DepartmentController
App\Features\Attractions\Controllers\AttractionController

// Services
App\Features\Departments\Services\DepartmentService
App\Features\Tours\Services\BookingService

// Models (pueden estar en Features o en app/Models/)
App\Features\Departments\Models\Department
App\Models\User // Modelos compartidos
```

### Nombres de Archivos
- **Controllers**: `{Feature}Controller.php`, `{Feature}ApiController.php`
- **Services**: `{Feature}Service.php`
- **Models**: `{ModelName}.php`

## Autoloading

El archivo `composer.json` está configurado para autocargar las features:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "App\\Features\\Departments\\": "app/Features/Departments/",
        "App\\Features\\Attractions\\": "app/Features/Attractions/",
        // ... otras features
    }
}
```

## Ejemplo de Uso

### Controller
```php
<?php
namespace App\Features\Departments\Controllers;

use App\Features\Departments\Services\DepartmentService;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    public function __construct(
        private DepartmentService $departmentService
    ) {}

    public function index()
    {
        $departments = $this->departmentService->getAllDepartments();
        return response()->json($departments);
    }
}
```

### Service
```php
<?php
namespace App\Features\Departments\Services;

class DepartmentService
{
    public function getAllDepartments(): array
    {
        // Lógica de negocio para obtener departamentos
        return Department::all()->toArray();
    }
}
```

## Testing de la Estructura

Para verificar que la estructura funciona correctamente:

1. **API Test**: `GET /api/departments` - Debe retornar lista de departamentos
2. **Health Check**: `GET /api/health` - Debe retornar status OK
3. **Autoloading**: Las clases deben cargarse sin errores

## Próximos Pasos

1. Implementar modelos Eloquent en cada feature
2. Crear migraciones para las tablas
3. Desarrollar servicios de negocio específicos
4. Implementar tests unitarios por feature
5. Crear documentación API para cada endpoint