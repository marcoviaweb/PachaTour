@echo off
REM 🚀 Script de Instalación Automática - Pacha Tour
REM Para sistemas Windows

echo 🏔️ Instalando Pacha Tour - Plataforma de Turismo de Bolivia
echo ============================================================

REM Verificar si estamos en el directorio correcto
if not exist "composer.json" (
    echo ❌ No se encontró composer.json. Asegúrate de estar en el directorio del proyecto.
    pause
    exit /b 1
)

echo ℹ️ Iniciando instalación...

REM Paso 1: Verificar PHP
echo.
echo 📋 Verificando requisitos del sistema...
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP no está instalado. Por favor instala PHP >= 8.1
    pause
    exit /b 1
) else (
    echo ✅ PHP encontrado
)

REM Paso 2: Verificar Composer
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    if exist "composer.phar" (
        echo ✅ Composer local encontrado
        set COMPOSER_CMD=php composer.phar
    ) else (
        echo ⚠️ Composer no encontrado. Descargando...
        curl -sS https://getcomposer.org/installer | php
        set COMPOSER_CMD=php composer.phar
        echo ✅ Composer descargado
    )
) else (
    echo ✅ Composer encontrado
    set COMPOSER_CMD=composer
)

REM Paso 3: Verificar Node.js
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Node.js no está instalado. Por favor instala Node.js >= 16
    pause
    exit /b 1
) else (
    echo ✅ Node.js encontrado
)

REM Paso 4: Instalar dependencias PHP
echo.
echo 📦 Instalando dependencias PHP...
%COMPOSER_CMD% install
if %errorlevel% neq 0 (
    echo ❌ Error instalando dependencias PHP
    pause
    exit /b 1
) else (
    echo ✅ Dependencias PHP instaladas
)

REM Paso 5: Instalar dependencias JavaScript
echo.
echo 📦 Instalando dependencias JavaScript...
npm install
if %errorlevel% neq 0 (
    echo ❌ Error instalando dependencias JavaScript
    pause
    exit /b 1
) else (
    echo ✅ Dependencias JavaScript instaladas
)

REM Paso 6: Configurar archivo .env
echo.
echo ⚙️ Configurando variables de entorno...
if not exist ".env" (
    copy .env.example .env
    echo ✅ Archivo .env creado desde .env.example
) else (
    echo ⚠️ Archivo .env ya existe, no se sobrescribirá
)

REM Paso 7: Generar clave de aplicación
echo.
echo 🔑 Generando clave de aplicación...
php artisan key:generate
echo ✅ Clave de aplicación generada

REM Paso 8: Crear directorios necesarios
echo.
echo 📁 Creando directorios necesarios...
if not exist "storage\framework\cache\data" mkdir storage\framework\cache\data
if not exist "storage\framework\sessions" mkdir storage\framework\sessions
if not exist "storage\framework\views" mkdir storage\framework\views
if not exist "storage\logs" mkdir storage\logs
if not exist "bootstrap\cache" mkdir bootstrap\cache
echo ✅ Directorios creados

REM Paso 9: Verificar PostgreSQL
echo.
echo 🗄️ Verificando PostgreSQL...
psql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️ PostgreSQL no encontrado. Instálalo antes de continuar.
) else (
    echo ✅ PostgreSQL encontrado
    echo ℹ️ Recuerda configurar la base de datos en .env
    echo ℹ️ Ejecuta: php artisan migrate después de configurar la BD
)

REM Paso 10: Compilar assets
echo.
echo 🎨 Compilando assets frontend...
npm run dev
if %errorlevel% neq 0 (
    echo ⚠️ Error compilando assets (puedes continuar sin esto)
) else (
    echo ✅ Assets compilados para desarrollo
)

REM Resumen final
echo.
echo 🎉 ¡Instalación completada!
echo ==========================
echo ℹ️ Próximos pasos:
echo 1. Configura PostgreSQL y actualiza .env con tus credenciales
echo 2. Ejecuta: php artisan migrate:install
echo 3. Ejecuta: php artisan migrate
echo 4. Inicia el servidor: php artisan serve
echo 5. Visita: http://localhost:8000
echo.
echo ℹ️ URLs de diagnóstico:
echo - http://localhost:8000/test-database.php (diagnóstico de BD)
echo - http://localhost:8000/setup-database.php (configuración automática)
echo.
echo ✅ ¡Pacha Tour está listo para usar! 🏔️

pause