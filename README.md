# Pacha Tour

Plataforma digital integral para promocionar y facilitar la experiencia turística en Bolivia.

## Requisitos del Sistema

- PHP >= 8.1
- Composer
- Node.js >= 16
- PostgreSQL >= 13
- Git

## Instalación

### 1. Instalar dependencias PHP
```bash
composer install
```

### 2. Configurar variables de entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configurar base de datos PostgreSQL
- Crear base de datos `pacha_tour_db`
- Actualizar credenciales en `.env`:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pacha_tour_db
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

### 4. Ejecutar migraciones
```bash
php artisan migrate
```

### 5. Instalar dependencias frontend (después de configurar Breeze)
```bash
npm install
npm run dev
```

## Desarrollo

### Servidor de desarrollo
```bash
php artisan serve
```

### Compilación de assets
```bash
npm run dev          # Desarrollo
npm run build        # Producción
```

### Testing
```bash
php artisan test     # Tests Laravel
npm run test         # Tests Vue.js
```

## Estructura del Proyecto

El proyecto está organizado por features/funcionalidades para facilitar el mantenimiento y escalabilidad:

```
app/
├── Features/
│   ├── Departments/     # Gestión de departamentos bolivianos
│   ├── Attractions/     # Atractivos turísticos y multimedia
│   ├── Tours/          # Recorridos, horarios y reservas
│   ├── Users/          # Autenticación y gestión de usuarios
│   ├── Payments/       # Sistema de pagos y comisiones
│   ├── Reviews/        # Valoraciones y comentarios
│   ├── Admin/          # Backoffice y herramientas administrativas
│   └── Localization/   # Soporte multilingüe
├── Models/             # Modelos Eloquent compartidos
└── Http/Controllers/   # Controladores base
```

Cada feature contiene:
- **Controllers/**: Controladores específicos de la funcionalidad
- **Models/**: Modelos Eloquent relacionados
- **Services/**: Lógica de negocio y servicios

Ver [FEATURE_STRUCTURE.md](FEATURE_STRUCTURE.md) para más detalles.

## Stack Tecnológico

- **Backend**: Laravel (PHP)
- **Frontend**: Vue.js
- **Base de Datos**: PostgreSQL
- **Build Tool**: Vite
- **Autenticación**: Laravel Sanctum + Breeze

## Contribución

1. Fork el proyecto
2. Crear rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request