# Script para instalar Composer en Windows
Write-Host "Descargando Composer..." -ForegroundColor Green

# Descargar el instalador de Composer
Invoke-WebRequest -Uri "https://getcomposer.org/installer" -OutFile "composer-setup.php"

# Verificar el hash del instalador (opcional pero recomendado)
Write-Host "Instalando Composer..." -ForegroundColor Green

# Instalar Composer
php composer-setup.php --install-dir=. --filename=composer.phar

# Limpiar el instalador
Remove-Item composer-setup.php

Write-Host "Composer instalado como composer.phar" -ForegroundColor Green
Write-Host "Puedes usarlo con: php composer.phar [comando]" -ForegroundColor Yellow