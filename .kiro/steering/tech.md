---
inclusion: always
---

# Stack Tecnológico - Pacha Tour

## Tecnologías Principales
- **Backend**: Laravel (PHP)
- **Frontend**: Vue.js
- **Base de Datos**: PostgreSQL
- **Arquitectura**: Monolítica
- **Librerías UI**: Utilizar librerías que permitan diseño atractivo y moderno

## Comandos Comunes
```bash
# Desarrollo
php artisan serve          # Servidor de desarrollo Laravel
npm run dev               # Compilación de assets Vue.js

# Testing
php artisan test          # Tests de Laravel
npm run test             # Tests de Vue.js

# Building
npm run build            # Build de producción
php artisan optimize     # Optimización Laravel

# Base de Datos
php artisan migrate      # Ejecutar migraciones
php artisan db:seed      # Ejecutar seeders
```

## Convenciones de Código
- **Naming**: PascalCase para clases, camelCase para métodos y variables
- **Indentación**: 4 espacios para PHP, 2 espacios para Vue/JS
- **Organización**: Por features/funcionalidades
- **Imágenes**: Almacenar en carpetas del filesystem, NO en base de datos

## Estructura de Features
```
app/
├── Features/
│   ├── Departments/     # Gestión de departamentos
│   ├── Attractions/     # Atractivos turísticos
│   ├── Tours/          # Recorridos y reservas
│   ├── Users/          # Gestión de usuarios
│   └── Payments/       # Sistema de pagos
```

## Configuración de Entorno
- PHP >= 8.1
- Node.js >= 16
- PostgreSQL >= 13
- Composer para dependencias PHP
- NPM/Yarn para dependencias JS