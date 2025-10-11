# 🚀 Guía de Instalación - Pacha Tour

Esta guía te permitirá instalar y ejecutar Pacha Tour en cualquier computadora.

## 📋 Requisitos del Sistema

### Obligatorios:
- **PHP >= 8.1** (recomendado 8.2+)
- **Composer** (gestor de dependencias PHP)
- **Node.js >= 16** (recomendado 18+)
- **NPM** (incluido con Node.js)
- **PostgreSQL >= 13** (recomendado 15+)
- **Git** (para clonar el repositorio)

### Opcionales pero recomendados:
- **Visual Studio Code** con extensiones PHP y Vue.js
- **pgAdmin** para administrar PostgreSQL visualmente

## 🛠️ Instalación Paso a Paso

### Paso 1: Instalar Requisitos del Sistema

#### Windows:
```bash
# Descargar e instalar desde las páginas oficiales:
# - PHP: https://windows.php.net/download/
# - Composer: https://getcomposer.org/download/
# - Node.js: https://nodejs.org/
# - PostgreSQL: https://www.postgresql.org/download/windows/
# - Git: https://git-scm.com/download/win

# O usar Chocolatey (si lo tienes instalado):
choco install php composer nodejs postgresql git
```

#### macOS:
```bash
# Usar Homebrew:
brew install php composer node postgresql git
brew services start postgresql
```

#### Linux (Ubuntu/Debian):
```bash
# Actualizar paquetes
sudo apt update

# Instalar PHP y extensiones
sudo apt install php8.2 php8.2-cli php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Instalar PostgreSQL
sudo apt install postgresql postgresql-contrib

# Instalar Git
sudo apt install git
```

### Paso 2: Clonar el Repositorio

```bash
# Clonar el proyecto
git clone [URL_DEL_REPOSITORIO] pacha-tour
cd pacha-tour

# O si ya tienes los archivos, simplemente navega al directorio
cd pacha-tour
```

### Paso 3: Instalar Dependencias PHP

```bash
# Instalar dependencias de Composer
composer install

# Si no tienes composer globalmente, usa:
php composer.phar install
```

### Paso 4: Instalar Dependencias JavaScript

```bash
# Instalar dependencias de NPM
npm install

# Si hay vulnerabilidades, puedes ejecutar:
npm audit fix
```

### Paso 5: Configurar Variables de Entorno

```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

**Editar el archivo `.env`** con tu configuración:

```env
APP_NAME="Pacha Tour"
APP_ENV=local
APP_KEY=[generada automáticamente]
APP_DEBUG=true
APP_URL=http://localhost:8000

# Configuración de Base de Datos
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pacha_tour_db
DB_USERNAME=postgres
DB_PASSWORD=tu_password_postgresql

# Resto de configuraciones (mantener como están)
```

### Paso 6: Configurar PostgreSQL

#### Crear Base de Datos:

**Opción A - Línea de comandos:**
```bash
# Conectar a PostgreSQL
psql -U postgres

# Crear base de datos
CREATE DATABASE pacha_tour_db WITH ENCODING 'UTF8';

# Salir
\q
```

**Opción B - Usar el script automático:**
```bash
# Ejecutar servidor Laravel temporalmente
php artisan serve

# Visitar en navegador:
# http://localhost:8000/setup-database.php
```

### Paso 7: Ejecutar Migraciones

```bash
# Instalar tabla de migraciones
php artisan migrate:install

# Ejecutar migraciones
php artisan migrate

# Verificar estado
php artisan migrate:status
```

### Paso 8: Configurar Storage

```bash
# Crear enlace simbólico para archivos públicos
php artisan storage:link

# Dar permisos (Linux/macOS)
chmod -R 775 storage bootstrap/cache
```

### Paso 9: Compilar Assets Frontend

```bash
# Para desarrollo
npm run dev

# Para producción
npm run build
```

### Paso 10: Iniciar la Aplicación

```bash
# Iniciar servidor Laravel
php artisan serve

# La aplicación estará disponible en:
# http://localhost:8000
```

## 🧪 Verificar Instalación

### URLs de Prueba:
- **Página principal**: http://localhost:8000
- **Test Laravel**: http://localhost:8000/test
- **Test Base de Datos**: http://localhost:8000/test-db
- **Diagnóstico completo**: http://localhost:8000/test-database.php

### Comandos de Verificación:
```bash
# Verificar versiones
php --version
composer --version
node --version
npm --version

# Verificar Laravel
php artisan --version

# Verificar base de datos
php artisan db:show

# Verificar migraciones
php artisan migrate:status
```

## 🔧 Solución de Problemas Comunes

### Error: "composer: command not found"
```bash
# Instalar Composer localmente
curl -sS https://getcomposer.org/installer | php
# Usar: php composer.phar [comando]
```

### Error: "Connection refused" (PostgreSQL)
```bash
# Windows - Iniciar servicio
net start postgresql-x64-15

# macOS
brew services start postgresql

# Linux
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

### Error: "Permission denied" (Linux/macOS)
```bash
# Dar permisos correctos
sudo chown -R $USER:$USER .
chmod -R 775 storage bootstrap/cache
```

### Error: "Class not found"
```bash
# Regenerar autoload
composer dump-autoload

# Limpiar cache
php artisan cache:clear
php artisan config:clear
```

### Error: "npm install fails"
```bash
# Limpiar cache de npm
npm cache clean --force

# Eliminar node_modules y reinstalar
rm -rf node_modules package-lock.json
npm install
```

## 🚀 Configuración para Producción

### Variables de Entorno para Producción:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Base de datos de producción
DB_HOST=tu-servidor-bd
DB_DATABASE=pacha_tour_prod
DB_USERNAME=usuario_prod
DB_PASSWORD=password_seguro
```

### Comandos para Producción:
```bash
# Optimizar aplicación
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Compilar assets para producción
npm run build

# Optimizar Composer
composer install --optimize-autoloader --no-dev
```

## 📦 Estructura de Archivos Importantes

```
pacha-tour/
├── .env                    # Variables de entorno (NO subir a Git)
├── .env.example           # Plantilla de variables
├── composer.json          # Dependencias PHP
├── package.json           # Dependencias JavaScript
├── README.md              # Documentación principal
├── INSTALACION.md         # Esta guía
├── app/                   # Código de la aplicación
├── config/                # Archivos de configuración
├── database/              # Migraciones y seeders
├── public/                # Archivos públicos
├── resources/             # Vistas y assets
├── routes/                # Definición de rutas
└── storage/               # Archivos generados
```

## 🤝 Colaboración en Equipo

### Para desarrolladores que se unen al proyecto:

1. **Clonar repositorio**
2. **Seguir pasos 3-10** de esta guía
3. **Crear rama para su feature**: `git checkout -b feature/mi-feature`
4. **Hacer commits regulares**: `git commit -m "Descripción clara"`
5. **Hacer push**: `git push origin feature/mi-feature`
6. **Crear Pull Request**

### Archivos que NO se deben subir a Git:
- `.env` (contiene passwords)
- `vendor/` (dependencias PHP)
- `node_modules/` (dependencias JS)
- `storage/logs/` (logs de la aplicación)
- `public/storage/` (archivos subidos)

## 📞 Soporte

Si tienes problemas con la instalación:

1. **Revisa esta guía** paso a paso
2. **Verifica los requisitos** del sistema
3. **Consulta los logs** en `storage/logs/laravel.log`
4. **Usa las URLs de diagnóstico** mencionadas arriba
5. **Contacta al equipo** de desarrollo

---

**¡Listo! Tu instalación de Pacha Tour debería estar funcionando correctamente.** 🎉