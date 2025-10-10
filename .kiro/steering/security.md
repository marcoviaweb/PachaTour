---
inclusion: always
---

# Lineamientos de Seguridad - Pacha Tour

## Validación de Entradas

### Principios Fundamentales
- **Validación del Servidor**: Todos los datos deben ser validados obligatoriamente del lado del servidor
- **Rutinas Centralizadas**: Utilizar clases/servicios centralizados para validación
- **Listas Blancas**: Preferir listas de valores permitidos sobre listas negras
- **Rechazo por Defecto**: Rechazar cualquier entrada que no cumpla con los criterios establecidos

### Criterios de Validación
- **Longitud**: Establecer límites mínimos y máximos para campos de texto
- **Tipo de Datos**: Validar que el tipo corresponda al esperado (string, integer, email, etc.)
- **Rango**: Para valores numéricos, establecer rangos válidos
- **Formato**: Usar expresiones regulares para validar formatos específicos
- **Caracteres Especiales**: Filtrar y sanitizar caracteres potencialmente peligrosos

### Clasificación de Fuentes de Datos
1. **Confianza Alta**: Datos internos del sistema, configuraciones
2. **Confianza Media**: Datos de usuarios autenticados
3. **Confianza Baja**: Datos de visitantes no autenticados
4. **Sin Confianza**: Datos externos, APIs de terceros

### Implementación en Laravel
```php
// Usar Form Requests para validación centralizada
class CreateAttractionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'description' => 'required|string|max:2000',
            'department_id' => 'required|exists:departments,id',
            'price' => 'required|numeric|min:0|max:9999.99'
        ];
    }
}
```

## Autenticación y Gestión de Contraseñas

### Requisitos de Autenticación
- **Recursos Protegidos**: Toda funcionalidad que requiera identificación del usuario debe estar protegida
- **Middleware**: Utilizar middleware de Laravel para proteger rutas
- **Sesiones Seguras**: Configurar sesiones con flags seguros (httpOnly, secure, sameSite)

### Gestión de Contraseñas
- **Hash Seguro**: Utilizar `bcrypt` o `argon2` para almacenar contraseñas
- **Nunca Texto Plano**: Prohibido almacenar contraseñas sin hash
- **Salt Automático**: Laravel maneja automáticamente el salt en las funciones hash

### Políticas de Contraseñas
- **Longitud Mínima**: 8 caracteres
- **Complejidad**: Al menos una mayúscula, una minúscula y un número
- **Caracteres Especiales**: Recomendado pero no obligatorio
- **Historial**: Evitar reutilización de las últimas 3 contraseñas

### Implementación
```php
// En el modelo User
protected $hidden = ['password', 'remember_token'];

// Para hash de contraseñas
$user->password = Hash::make($request->password);

// Para verificación
if (Hash::check($request->password, $user->password)) {
    // Contraseña válida
}
```

## Medidas Adicionales de Seguridad

### Protección CSRF
- Utilizar tokens CSRF en todos los formularios
- Laravel incluye middleware CSRF por defecto

### Sanitización de Salida
- Escapar datos antes de mostrarlos en vistas
- Usar `{{ }}` en Blade para escape automático
- Para HTML confiable usar `{!! !!}` con extrema precaución

### Logging de Seguridad
- Registrar intentos de login fallidos
- Monitorear patrones de acceso sospechosos
- Logs de cambios en datos sensibles

### Headers de Seguridad
```php
// En middleware o configuración
'X-Frame-Options' => 'DENY',
'X-Content-Type-Options' => 'nosniff',
'X-XSS-Protection' => '1; mode=block',
'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
```

## Consideraciones Específicas para Turismo

### Datos de Pago
- **PCI DSS**: Cumplir con estándares de seguridad para datos de tarjetas
- **Tokenización**: No almacenar datos de tarjetas directamente
- **Proveedores Certificados**: Usar gateways de pago certificados

### Información Personal
- **GDPR/Protección de Datos**: Implementar políticas de privacidad
- **Consentimiento**: Obtener consentimiento explícito para uso de datos
- **Derecho al Olvido**: Permitir eliminación de datos personales

### Contenido Multimedia
- **Validación de Archivos**: Verificar tipo, tamaño y contenido de imágenes/videos
- **Almacenamiento Seguro**: Separar archivos públicos de privados
- **Prevención de Upload Malicioso**: Validar extensiones y contenido real