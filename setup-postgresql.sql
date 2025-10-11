-- Script para configurar PostgreSQL para Pacha Tour
-- Ejecutar como usuario postgres

-- Crear la base de datos
CREATE DATABASE pacha_tour_db
    WITH 
    OWNER = postgres
    ENCODING = 'UTF8'
    LC_COLLATE = 'Spanish_Spain.1252'
    LC_CTYPE = 'Spanish_Spain.1252'
    TABLESPACE = pg_default
    CONNECTION LIMIT = -1;

-- Crear usuario específico para la aplicación (opcional)
-- CREATE USER pacha_tour_user WITH PASSWORD 'tu_password_seguro';
-- GRANT ALL PRIVILEGES ON DATABASE pacha_tour_db TO pacha_tour_user;

-- Conectar a la base de datos
\c pacha_tour_db;

-- Verificar que estamos en la base de datos correcta
SELECT current_database(), current_user, version();