---
inclusion: always
---

# Estructura del Proyecto - Pacha Tour

## Arquitectura
- **Tipo**: Monolítica
- **Organización**: Por Features/Funcionalidades
- **Convención**: PascalCase para nombres de clases y directorios principales

## Estructura de Directorios Laravel
```
/
├── app/
│   ├── Features/              # Organización por funcionalidades
│   │   ├── Departments/       # Gestión de departamentos
│   │   ├── Attractions/       # Atractivos turísticos
│   │   ├── Tours/            # Recorridos y reservas
│   │   ├── Users/            # Autenticación y usuarios
│   │   ├── Payments/         # Sistema de pagos
│   │   └── Admin/            # Backoffice/administración
│   ├── Models/               # Modelos Eloquent
│   ├── Http/
│   │   ├── Controllers/      # Controladores por feature
│   │   ├── Requests/         # Form requests
│   │   └── Resources/        # API resources
│   └── Services/             # Servicios de negocio
├── resources/
│   ├── js/                   # Componentes Vue.js
│   │   ├── components/       # Componentes reutilizables
│   │   ├── pages/           # Páginas/vistas principales
│   │   └── features/        # Componentes por funcionalidad
│   ├── views/               # Plantillas Blade
│   └── sass/                # Estilos SCSS
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   ├── attractions/ # Imágenes de atractivos
│   │   │   ├── departments/ # Imágenes de departamentos
│   │   │   └── tours/       # Imágenes de tours
│   │   └── uploads/         # Archivos temporales
├── database/
│   ├── migrations/          # Migraciones de BD
│   ├── seeders/            # Datos iniciales
│   └── factories/          # Factories para testing
├── tests/
│   ├── Feature/            # Tests de funcionalidades
│   └── Unit/               # Tests unitarios
└── .kiro/                  # Configuración Kiro
```

## Convenciones de Nomenclatura
- **Directorios**: PascalCase para features principales
- **Archivos PHP**: PascalCase para clases, camelCase para métodos
- **Archivos Vue**: PascalCase para componentes
- **Rutas**: kebab-case para URLs
- **Base de Datos**: snake_case para tablas y columnas

## Organización por Features
Cada feature contiene:
```
Features/Attractions/
├── Controllers/
├── Models/
├── Requests/
├── Resources/
├── Services/
└── Tests/
```

## Gestión de Archivos Multimedia
- **Ubicación**: `storage/app/public/` con subdirectorios por tipo
- **Acceso**: Mediante enlaces simbólicos (`php artisan storage:link`)
- **Organización**: Por categoría (attractions, departments, tours)
- **Formatos**: JPG/PNG para imágenes, MP4 para videos

## Roles y Permisos
- **Visitante**: Sin autenticación, solo lectura
- **Usuario Registrado**: CRUD en itinerarios personales
- **Administrador**: CRUD completo en backoffice