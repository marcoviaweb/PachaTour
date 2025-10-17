// Test de inicialización correcta del formulario
// Simulando exactamente lo que Vue debería hacer

// Datos que llegan del backend (confirmados)
const department = {
    "id": 3,
    "name": "Cochabamba", 
    "slug": "cochabamba",
    "capital": "Cochabamba",
    "description": "Conocido como la \"Ciudad de la Eterna Primavera\" por su clima privilegiado. Cochabamba es el corazón gastronómico de Bolivia, hogar del Cristo de la Concordia y punto de encuentro de diversas culturas andinas.",
    "short_description": "Ciudad de la Eterna Primavera, corazón gastronómico de Bolivia.",
    "latitude": -17.3895,
    "longitude": -66.1568,
    "image_path": null,
    "gallery": ["cristo-concordia.jpg", "valle-cochabamba.jpg", "mercado-cancha.jpg", "tunari.jpg"],
    "population": 1758143,
    "area_km2": "55631.00",
    "climate": "Templado de valle", 
    "languages": ["Español", "Quechua"],
    "is_active": true,
    "sort_order": 3,
    "created_at": "2025-10-11T20:20:41.000000Z",
    "updated_at": "2025-10-11T20:20:41.000000Z",
    "media": []
};

// Simulación de la inicialización correcta
const formData = {
    name: department?.name || '',
    capital: department?.capital || '',
    description: department?.description || '',
    short_description: department?.short_description || '',
    latitude: department?.latitude ?? 0,
    longitude: department?.longitude ?? 0,
    population: department?.population ?? 0,
    area_km2: parseFloat(department?.area_km2) || 0,
    climate: department?.climate || '',
    languages: department?.languages || [],
    is_active: department?.is_active ?? true,
    sort_order: department?.sort_order ?? 0,
    images: [],
};

console.log('=== INICIALIZACIÓN CORRECTA DEL FORMULARIO ===');
console.log('Datos del formulario:');
console.log(JSON.stringify(formData, null, 2));

console.log('\n=== VERIFICACIÓN CAMPO POR CAMPO ===');
Object.entries(formData).forEach(([key, value]) => {
    const type = typeof value;
    const isArray = Array.isArray(value);
    console.log(`${key}: ${JSON.stringify(value)} (${isArray ? 'array' : type})`);
});

console.log('\n=== CORRECCIÓN PROPUESTA ===');
console.log('Esta es la inicialización que debería funcionar perfectamente.');