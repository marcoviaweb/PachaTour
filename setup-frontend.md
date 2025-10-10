# Configuración Frontend - Vue.js con Vite

## Instalación de Dependencias

### 1. Instalar Node.js
- Descargar Node.js >= 16 desde https://nodejs.org/
- Verificar instalación: `node --version` y `npm --version`

### 2. Instalar dependencias del proyecto
```bash
npm install
```

### 3. Configurar Laravel Breeze con Vue (si no está instalado)
```bash
composer require laravel/breeze --dev
php artisan breeze:install vue
```

## Comandos de Desarrollo

### Servidor de desarrollo con hot reload
```bash
npm run dev
```

### Build para producción
```bash
npm run build
```

### Tests frontend
```bash
npm run test
```

## Verificación de Instalación

1. Ejecutar `npm run dev`
2. Abrir http://localhost:5173 (Vite dev server)
3. Verificar que la página de bienvenida se carga correctamente
4. Verificar hot reload modificando el archivo Welcome.vue

## Estructura Frontend

```
resources/
├── js/
│   ├── app.js              # Punto de entrada principal
│   ├── bootstrap.js        # Configuración de Axios
│   └── Pages/
│       └── Welcome.vue     # Página de bienvenida
├── css/
│   └── app.css            # Estilos Tailwind CSS
└── views/
    └── app.blade.php      # Template principal Inertia
```

## Tecnologías Configuradas

- **Vue.js 3**: Framework frontend reactivo
- **Inertia.js**: SPA sin API, conecta Laravel con Vue
- **Tailwind CSS**: Framework de utilidades CSS
- **Vite**: Build tool rápido con hot reload
- **Vitest**: Framework de testing para Vue