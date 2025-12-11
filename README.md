# Pacha Tour

Plataforma digital integral para promocionar y facilitar la experiencia turística en Bolivia.

## Requisitos del Sistema

- PHP >= 8.1
- Composer
# Pacha Tour — Pasos mínimos para instalar (rama SQA)

A continuación están los pasos concretos y mínimos para dejar la aplicación funcionando desde la rama `SQA`.

1. Instalar Requisitos del Sistema

- PHP >= 8.1
- Node.js >= 16
- PostgreSQL >= 13
- Composer
- Git

2. Clonar el Repositorio y cambiar a la rama `SQA`

```powershell
git clone https://github.com/marcoviaweb/PachaTour.git
cd PachaTour
git checkout SQA
```

3. Instalar Dependencias PHP

```powershell
composer install
```

4. Instalar Dependencias JavaScript

```powershell
npm install
```

5. Restaurar la Base de Datos

Restaurar el backup proporcionado en PostgreSQL para recrear la base de datos del proyecto.

6. Configurar Variables de Entorno

```powershell
# Copiar archivo de configuración
cp .env.example .env

# Editar `.env` y configurar la conexión a PostgreSQL
# Ejemplo de valores a editar:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=pacha_tour_db
# DB_USERNAME=postgres
# DB_PASSWORD=tu_password

# Generar clave de aplicación
php artisan key:generate
```

7. Compilar Assets (CSS/JS)

```powershell
npm run dev
```

8. Iniciar Servidor en otra terminal

```powershell
php artisan serve
```

La aplicación quedará accesible en `http://localhost:8000`.

Si quieres que también haga el commit de este cambio o lo suba a una rama, dime y lo hago.

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