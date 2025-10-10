# Configuración de Base de Datos PostgreSQL

## Instalación de PostgreSQL

### Windows
1. Descargar PostgreSQL desde https://www.postgresql.org/download/windows/
2. Ejecutar el instalador y seguir las instrucciones
3. Recordar la contraseña del usuario `postgres`

### macOS
```bash
brew install postgresql
brew services start postgresql
```

### Linux (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install postgresql postgresql-contrib
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

## Configuración de la Base de Datos

### 1. Conectar a PostgreSQL
```bash
# Windows/Linux
psql -U postgres

# macOS
psql postgres
```

### 2. Crear la base de datos
```sql
CREATE DATABASE pacha_tour_db;
CREATE USER pacha_tour_user WITH PASSWORD 'tu_password_seguro';
GRANT ALL PRIVILEGES ON DATABASE pacha_tour_db TO pacha_tour_user;
\q
```

### 3. Actualizar archivo .env
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pacha_tour_db
DB_USERNAME=pacha_tour_user
DB_PASSWORD=tu_password_seguro
```

### 4. Probar la conexión
```bash
php artisan migrate:status
```

Si la conexión es exitosa, verás la lista de migraciones disponibles.

## Comandos Útiles

### Verificar estado de PostgreSQL
```bash
# Windows
pg_ctl status

# Linux/macOS
sudo systemctl status postgresql
```

### Conectar a la base de datos específica
```bash
psql -U pacha_tour_user -d pacha_tour_db -h localhost
```

### Backup de la base de datos
```bash
pg_dump -U pacha_tour_user -h localhost pacha_tour_db > backup.sql
```

### Restaurar backup
```bash
psql -U pacha_tour_user -h localhost pacha_tour_db < backup.sql
```