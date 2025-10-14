# Script de Reorganización por Features

## 🚀 Comandos PowerShell para Reorganización

### Fase 1: Crear Estructura de Directorios

```powershell
# Crear directorios Models en features
New-Item -ItemType Directory -Path "app/Features/Departments/Models" -Force
New-Item -ItemType Directory -Path "app/Features/Attractions/Models" -Force
New-Item -ItemType Directory -Path "app/Features/Tours/Models" -Force
New-Item -ItemType Directory -Path "app/Features/Users/Models" -Force
New-Item -ItemType Directory -Path "app/Features/Reviews/Models" -Force
New-Item -ItemType Directory -Path "app/Shared" -Force
New-Item -ItemType Directory -Path "app/Shared/Models" -Force

# Frontend structure
New-Item -ItemType Directory -Path "resources/js/Pages/Search" -Force
New-Item -ItemType Directory -Path "resources/js/Pages/Admin" -Force
New-Item -ItemType Directory -Path "resources/js/Components/Shared" -Force
New-Item -ItemType Directory -Path "resources/js/Components/Attractions" -Force
New-Item -ItemType Directory -Path "resources/js/Components/Tours" -Force
New-Item -ItemType Directory -Path "resources/js/Components/Users" -Force
New-Item -ItemType Directory -Path "resources/js/Components/Admin" -Force
New-Item -ItemType Directory -Path "resources/js/services/features" -Force
```

### Fase 2: Mover Modelos (con backup)

```powershell
# Crear backup primero
New-Item -ItemType Directory -Path "backup/models" -Force
Copy-Item "app/Models/*" "backup/models/" -Recurse

# Mover Department
Move-Item "app/Models/Department.php" "app/Features/Departments/Models/" -Force

# Mover Attraction  
Move-Item "app/Models/Attraction.php" "app/Features/Attractions/Models/" -Force

# Mover Tours relacionados
Move-Item "app/Models/Tour.php" "app/Features/Tours/Models/" -Force
Move-Item "app/Models/TourSchedule.php" "app/Features/Tours/Models/" -Force
Move-Item "app/Models/Booking.php" "app/Features/Tours/Models/" -Force

# Mover Users relacionados
Move-Item "app/Models/UserActivity.php" "app/Features/Users/Models/" -Force
Move-Item "app/Models/UserFavorite.php" "app/Features/Users/Models/" -Force

# Mover Reviews
Move-Item "app/Models/Review.php" "app/Features/Reviews/Models/" -Force

# Mover Shared
Move-Item "app/Models/Media.php" "app/Shared/Models/" -Force

# User.php se queda en app/Models/ (modelo central)
```

### Fase 3: Actualizar Namespaces en Modelos

Los namespaces se actualizan automáticamente con find & replace en VS Code:
- Buscar: `namespace App\Models;`  
- Reemplazar: `namespace App\Features\{Feature}\Models;`

### Fase 4: Frontend Reorganization

```powershell
# Mover Search.vue
Move-Item "resources/js/Pages/Search.vue" "resources/js/Pages/Search/Index.vue" -Force

# Renombrar User a Users para consistencia
Move-Item "resources/js/Pages/User" "resources/js/Pages/Users" -Force
```

### Fase 5: Validation Commands

```powershell
# Update autoloader
composer dump-autoload

# Run tests
php artisan test

# Check routes
php artisan route:list | Select-String "api"

# Build frontend
npm run build

# Check for errors
php artisan config:clear
php artisan cache:clear
```

## 📋 Checklist de Validación

### Backend Validation
- [ ] `composer dump-autoload` ejecutado sin errores
- [ ] `php artisan test` todos los tests pasan
- [ ] `php artisan route:list` muestra todas las rutas
- [ ] API endpoints responden correctamente
- [ ] No hay errores en `storage/logs/laravel.log`

### Frontend Validation  
- [ ] `npm run build` compila sin errores
- [ ] Páginas cargan correctamente
- [ ] Componentes renderizan sin errores
- [ ] Navegación funciona entre páginas
- [ ] Console del navegador sin errores

### Database Validation
- [ ] Migraciones ejecutan correctamente
- [ ] Relaciones Eloquent funcionan
- [ ] Seeders populan datos correctamente
- [ ] Queries de la aplicación funcionan

## 🔄 Rollback Plan (si algo sale mal)

```powershell
# Restaurar modelos desde backup
Remove-Item "app/Features/*/Models/*" -Force
Remove-Item "app/Shared/Models/*" -Force  
Copy-Item "backup/models/*" "app/Models/" -Force

# Restaurar composer autoload
composer dump-autoload

# Limpiar caches
php artisan config:clear
php artisan cache:clear
```

## ⚡ Quick Implementation (30 minutos)

Si prefieres una implementación rápida, puedes empezar solo con un feature:

```powershell
# Solo reorganizar Departments como prueba
New-Item -ItemType Directory -Path "app/Features/Departments/Models" -Force
Copy-Item "app/Models/Department.php" "app/Features/Departments/Models/" -Force

# Actualizar namespace en el archivo copiado
# Cambiar: namespace App\Models;
# Por: namespace App\Features\Departments\Models;

# Actualizar un controlador como prueba
# En DepartmentController.php cambiar:
# use App\Models\Department;
# Por: use App\Features\Departments\Models\Department;

# Test que funciona
php artisan test --filter DepartmentTest
```

¿Te gustaría que implemente la reorganización completa o prefieres empezar con un feature específico como prueba?