---
inclusion: always
---

# Control de Calidad (QA) - Pacha Tour

## Validación de Campos Obligatorios

### Verificación de Campos Vacíos
- **Mensajes de Error**: Todos los campos obligatorios deben mostrar mensajes de error claros cuando se dejen vacíos
- **Prevención de Envío**: No permitir el envío del formulario hasta que todos los campos obligatorios estén completos
- **Indicadores Visuales**: Usar asteriscos (*) o colores para identificar campos obligatorios
- **Validación en Tiempo Real**: Mostrar errores mientras el usuario completa el formulario

### Implementación en Vue.js
```javascript
// Ejemplo de validación reactiva
data() {
  return {
    form: {
      name: '',
      email: '',
      department_id: null
    },
    errors: {}
  }
},
computed: {
  isFormValid() {
    return this.form.name && 
           this.form.email && 
           this.form.department_id &&
           Object.keys(this.errors).length === 0;
  }
}
```

## Botones Inactivos con Formularios Incompletos

### Estados de Botones
- **Desactivación Automática**: Los botones de envío deben estar desactivados cuando falten campos obligatorios
- **Indicadores Visuales**: Botones desactivados deben tener apariencia diferente (gris, cursor no permitido)
- **Mensajes Informativos**: Mostrar tooltip o mensaje explicando por qué el botón está desactivado

### Casos de Prueba
- Formulario completamente vacío → Botón desactivado
- Formulario parcialmente completo → Botón desactivado
- Todos los campos obligatorios completos → Botón activado
- Campos con errores de formato → Botón desactivado

## Verificación de Formato - Campos Fecha

### Formato Requerido
- **Formato Estándar**: dd/mm/yyyy (ejemplo: 25/12/2024)
- **Validación de Rango**: Fechas válidas dentro de rangos lógicos
- **Fechas Futuras**: Para reservas, solo permitir fechas futuras
- **Calendarios Interactivos**: Usar date pickers para evitar errores de formato

### Validaciones Específicas
```javascript
// Validación de fecha en Vue.js
validateDate(date) {
  const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
  if (!regex.test(date)) return false;
  
  const [, day, month, year] = date.match(regex);
  const dateObj = new Date(year, month - 1, day);
  
  return dateObj.getDate() == day && 
         dateObj.getMonth() == month - 1 && 
         dateObj.getFullYear() == year;
}
```

## Verificación de Formato - Campos Numéricos

### Tipos de Campos Numéricos
- **Enteros**: Solo números enteros (capacidad de tours, cantidad de personas)
- **Decimales**: Números con separador decimal para precios (123.45)
- **Negativos**: Permitir valores negativos solo cuando sea lógico
- **Rangos**: Validar que estén dentro de rangos permitidos

### Implementación
```javascript
// Validación numérica
validateNumber(value, type = 'decimal') {
  if (type === 'integer') {
    return /^\d+$/.test(value);
  }
  if (type === 'decimal') {
    return /^\d+(\.\d{1,2})?$/.test(value);
  }
  if (type === 'price') {
    return /^\d+(\.\d{2})?$/.test(value) && parseFloat(value) >= 0;
  }
}
```

## Verificación de Formato - Campos Texto Especiales

### Correos Electrónicos
- **Formato RFC**: Validar según estándares de email
- **Dominios Válidos**: Verificar que el dominio sea válido
- **Confirmación**: Requerir confirmación en registro

```javascript
validateEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}
```

### Tarjetas de Crédito
- **Algoritmo de Luhn**: Validar número de tarjeta
- **Tipos Soportados**: Visa, MasterCard, American Express
- **Formato Visual**: Mostrar espacios cada 4 dígitos
- **CVV**: Validar código de seguridad (3-4 dígitos)

### Teléfonos
- **Formato Boliviano**: +591 seguido del número
- **Validación de Longitud**: 8 dígitos para números locales
- **Formato Internacional**: Permitir códigos de país

## Verificación de Longitud Mínima y Máxima

### Límites por Tipo de Campo
- **Nombres**: 2-100 caracteres
- **Descripciones**: 10-2000 caracteres
- **Comentarios**: 5-500 caracteres
- **Direcciones**: 10-200 caracteres
- **Contraseñas**: 8-128 caracteres

### Implementación de Validación
```javascript
validateLength(value, min, max, fieldName) {
  if (value.length < min) {
    return `${fieldName} debe tener al menos ${min} caracteres`;
  }
  if (value.length > max) {
    return `${fieldName} no puede exceder ${max} caracteres`;
  }
  return null;
}
```

### Indicadores Visuales
- **Contador de Caracteres**: Mostrar caracteres restantes
- **Barras de Progreso**: Para campos con límites altos
- **Colores de Advertencia**: Amarillo cerca del límite, rojo al exceder

## Casos de Prueba Específicos para Turismo

### Formularios de Reserva
- Verificar que fecha de reserva sea futura
- Validar número de personas (mínimo 1, máximo según capacidad)
- Confirmar que el precio calculado sea correcto

### Registro de Atractivos (Admin)
- Nombre del atractivo: 3-255 caracteres
- Descripción: mínimo 50 caracteres
- Coordenadas GPS: formato decimal válido
- Precio: formato monetario con 2 decimales

### Gestión de Usuarios
- Email único en el sistema
- Contraseña con complejidad mínima
- Confirmación de contraseña coincidente
- Campos de perfil opcionales con límites apropiados

## Herramientas de Testing

### Testing Automatizado
- **Unit Tests**: Para funciones de validación
- **Integration Tests**: Para flujos completos de formularios
- **E2E Tests**: Para experiencia de usuario completa

### Testing Manual
- **Checklist de QA**: Lista verificable para cada formulario
- **Casos Límite**: Probar valores en los límites exactos
- **Navegadores**: Verificar compatibilidad cross-browser
- **Dispositivos**: Testing responsive en móviles y tablets