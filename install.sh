#!/bin/bash

# 🚀 Script de Instalación Automática - Pacha Tour
# Para sistemas Unix/Linux/macOS

echo "🏔️ Instalando Pacha Tour - Plataforma de Turismo de Bolivia"
echo "============================================================"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar mensajes
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Verificar si estamos en el directorio correcto
if [ ! -f "composer.json" ]; then
    print_error "No se encontró composer.json. Asegúrate de estar en el directorio del proyecto."
    exit 1
fi

print_info "Iniciando instalación..."

# Paso 1: Verificar PHP
echo ""
echo "📋 Verificando requisitos del sistema..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    print_status "PHP $PHP_VERSION encontrado"
else
    print_error "PHP no está instalado. Por favor instala PHP >= 8.1"
    exit 1
fi

# Paso 2: Verificar Composer
if command -v composer &> /dev/null; then
    print_status "Composer encontrado"
    COMPOSER_CMD="composer"
elif [ -f "composer.phar" ]; then
    print_status "Composer local encontrado"
    COMPOSER_CMD="php composer.phar"
else
    print_warning "Composer no encontrado. Descargando..."
    curl -sS https://getcomposer.org/installer | php
    COMPOSER_CMD="php composer.phar"
    print_status "Composer descargado"
fi

# Paso 3: Verificar Node.js
if command -v node &> /dev/null; then
    NODE_VERSION=$(node -v)
    print_status "Node.js $NODE_VERSION encontrado"
else
    print_error "Node.js no está instalado. Por favor instala Node.js >= 16"
    exit 1
fi

# Paso 4: Instalar dependencias PHP
echo ""
echo "📦 Instalando dependencias PHP..."
$COMPOSER_CMD install
if [ $? -eq 0 ]; then
    print_status "Dependencias PHP instaladas"
else
    print_error "Error instalando dependencias PHP"
    exit 1
fi

# Paso 5: Instalar dependencias JavaScript
echo ""
echo "📦 Instalando dependencias JavaScript..."
npm install
if [ $? -eq 0 ]; then
    print_status "Dependencias JavaScript instaladas"
else
    print_error "Error instalando dependencias JavaScript"
    exit 1
fi

# Paso 6: Configurar archivo .env
echo ""
echo "⚙️ Configurando variables de entorno..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    print_status "Archivo .env creado desde .env.example"
else
    print_warning "Archivo .env ya existe, no se sobrescribirá"
fi

# Paso 7: Generar clave de aplicación
echo ""
echo "🔑 Generando clave de aplicación..."
php artisan key:generate
print_status "Clave de aplicación generada"

# Paso 8: Crear directorios necesarios
echo ""
echo "📁 Creando directorios necesarios..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Dar permisos (solo en sistemas Unix)
if [[ "$OSTYPE" != "msys" && "$OSTYPE" != "win32" ]]; then
    chmod -R 775 storage bootstrap/cache
    print_status "Permisos configurados"
fi

# Paso 9: Verificar PostgreSQL
echo ""
echo "🗄️ Verificando PostgreSQL..."
if command -v psql &> /dev/null; then
    print_status "PostgreSQL encontrado"
    print_info "Recuerda configurar la base de datos en .env"
    print_info "Ejecuta: php artisan migrate después de configurar la BD"
else
    print_warning "PostgreSQL no encontrado. Instálalo antes de continuar."
fi

# Paso 10: Compilar assets
echo ""
echo "🎨 Compilando assets frontend..."
npm run dev
if [ $? -eq 0 ]; then
    print_status "Assets compilados para desarrollo"
else
    print_warning "Error compilando assets (puedes continuar sin esto)"
fi

# Resumen final
echo ""
echo "🎉 ¡Instalación completada!"
echo "=========================="
print_info "Próximos pasos:"
echo "1. Configura PostgreSQL y actualiza .env con tus credenciales"
echo "2. Ejecuta: php artisan migrate:install"
echo "3. Ejecuta: php artisan migrate"
echo "4. Inicia el servidor: php artisan serve"
echo "5. Visita: http://localhost:8000"
echo ""
print_info "URLs de diagnóstico:"
echo "- http://localhost:8000/test-database.php (diagnóstico de BD)"
echo "- http://localhost:8000/setup-database.php (configuración automática)"
echo ""
print_status "¡Pacha Tour está listo para usar! 🏔️"