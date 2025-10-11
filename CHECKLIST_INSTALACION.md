# ✅ Checklist de Instalación - Pacha Tour

Usa este checklist para verificar que la instalación sea exitosa en cualquier computadora.

## 📋 Pre-instalación

- [ ] **PHP >= 8.1** instalado (`php --version`)
- [ ] **Composer** instalado (`composer --version`)
- [ ] **Node.js >= 16** instalado (`node --version`)
- [ ] **PostgreSQL >= 13** instalado (`psql --version`)
- [ ] **Git** instalado (`git --version`)

## 📦 Instalación de Dependencias

- [ ] Dependencias PHP instaladas (`composer install`)
- [ ] Dependencias JavaScript instaladas (`npm install`)
- [ ] Archivo `.env` creado desde `.env.example`
- [ ] Clave de aplicación generada (`php artisan key:generate`)

## 🗄️ Configuración de Base de Datos

- [ ] PostgreSQL ejecutándose
- [ ] Base de datos `pacha_tour_db` creada
- [ ] Credenciales configuradas en `.env`
- [ ] Tabla de migraciones instalada (`php artisan migrate:install`)
- [ ] Migraciones ejecutadas (`php artisan migrate`)

## 🧪 Verificación de Funcionamiento

### URLs de Prueba (con servidor iniciado: `php artisan serve`)

- [ ] **Página principal**: http://localhost:8000
  - Debe mostrar "Pacha Tour" con estado verde
  
- [ ] **Test básico**: http://localhost:8000/test
  - Debe retornar JSON con información del sistema
  
- [ ] **Test de BD**: http://localhost:8000/test-db
  - Debe mostrar conexión exitosa y 3+ tablas
  
- [ ] **Diagnóstico completo**: http://localhost:8000/test-database.php
  - Todas las pruebas deben estar en verde

### Comandos de Verificación

- [ ] `php artisan --version` - Muestra versión de Laravel
- [ ] `php artisan migrate:status` - Muestra migraciones ejecutadas
- [ ] `php artisan db:show` - Muestra información de la BD
- [ ] `npm run dev` - Compila assets sin errores

## 🎯 Funcionalidades Básicas

- [ ] **Estructura por Features**: Servicios de departamentos funcionando
- [ ] **API Endpoints**: Rutas de departamentos respondiendo
- [ ] **Base de Datos**: Conexión y consultas funcionando
- [ ] **Frontend**: Assets compilando correctamente

## 🔧 Solución de Problemas

### Si algo no funciona:

1. **Revisar logs**: `storage/logs/laravel.log`
2. **Verificar .env**: Credenciales de BD correctas
3. **Limpiar cache**: 
   ```bash
   php artisan cache:clear
   php artisan config:clear
   composer dump-autoload
   ```
4. **Reinstalar dependencias**:
   ```bash
   rm -rf vendor node_modules
   composer install
   npm install
   ```

## 📊 Resultados Esperados

### Test de Base de Datos Exitoso:
```json
{
  "status": "success",
  "message": "Conexión a PostgreSQL exitosa",
  "tables_count": 3,
  "tables": ["migrations", "personal_access_tokens", "test_connection"]
}
```

### Comando `php artisan db:show`:
```
Database ................................. pacha_tour_db
Host ..................................... 127.0.0.1
Port ..................................... 5432
Username ................................. postgres
Tables ................................... 3+
```

## 🎉 Instalación Completada

Si todos los elementos están marcados, ¡la instalación es exitosa!

**Próximos pasos:**
1. Continuar con el desarrollo de features
2. Configurar autenticación (Laravel Breeze)
3. Implementar seeders con datos de Bolivia
4. Desarrollar frontend Vue.js

---

**Fecha de verificación**: ___________  
**Verificado por**: ___________  
**Notas adicionales**: ___________