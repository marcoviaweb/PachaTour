# Dockerfile para Pacha Tour
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm

# Limpiar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario para la aplicación
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copiar archivos de la aplicación
COPY . /var/www
COPY --chown=www:www . /var/www

# Cambiar al directorio de trabajo
WORKDIR /var/www

# Cambiar al usuario www
USER www

# Exponer puerto 9000 para PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]