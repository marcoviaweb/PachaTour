<?php

/**
 * Script simple para crear imágenes SVG de atractivos
 * Ejecutar con: php create-attraction-images.php
 */

// Lista de imágenes comunes que podrían faltar
$commonAttractions = [
    'laguna-colorada-234' => ['name' => 'Laguna Colorada', 'type' => 'natural'],
    'geiser-del-tatio-567' => ['name' => 'Géiser del Tatio', 'type' => 'natural'],
    'sendero-del-inca-890' => ['name' => 'Sendero del Inca', 'type' => 'archaeological'],
    'parque-de-orquídeas-123' => ['name' => 'Parque de Orquídeas', 'type' => 'natural'],
    'montaña-dorada-456' => ['name' => 'Montaña Dorada', 'type' => 'natural'],
    'quebrada-perdida-789' => ['name' => 'Quebrada Perdida', 'type' => 'natural'],
    'santuario-de-las-estrellas-321' => ['name' => 'Santuario de las Estrellas', 'type' => 'religious'],
    'reserva-de-vicuñas-654' => ['name' => 'Reserva de Vicuñas', 'type' => 'natural'],
    'puente-del-arco-iris-987' => ['name' => 'Puente del Arco Iris', 'type' => 'natural'],
    'valle-misterioso-147' => ['name' => 'Valle Misterioso', 'type' => 'natural'],
];

// Colores por tipo
$typeColors = [
    'natural' => ['#4CAF50', '#8BC34A', '#2E7D32'],
    'cultural' => ['#FF9800', '#FFB74D', '#E65100'],
    'historical' => ['#795548', '#A1887F', '#5D4037'],
    'religious' => ['#9C27B0', '#BA68C8', '#6A1B9A'],
    'archaeological' => ['#607D8B', '#90A4AE', '#37474F'],
];

function createSVG($slug, $data) {
    global $typeColors;
    
    $type = $data['type'];
    $name = $data['name'];
    $colors = $typeColors[$type] ?? $typeColors['natural'];
    
    $svg = <<<SVG
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" style="stop-color:{$colors[1]};stop-opacity:0.3" />
      <stop offset="100%" style="stop-color:{$colors[0]};stop-opacity:0.1" />
    </linearGradient>
  </defs>
  
  <!-- Background -->
  <rect width="400" height="300" fill="url(#bgGrad)"/>
  
  <!-- Mountains -->
  <path d="M0,150 L100,80 L200,120 L300,70 L400,100 L400,300 L0,300 Z" fill="{$colors[1]}" opacity="0.7"/>
  
  <!-- Main feature -->
  <circle cx="200" cy="180" r="40" fill="{$colors[0]}" opacity="0.8"/>
  
  <!-- Decorative elements -->
  <circle cx="120" cy="160" r="15" fill="{$colors[2]}" opacity="0.6"/>
  <circle cx="280" cy="170" r="20" fill="{$colors[2]}" opacity="0.6"/>
  
  <!-- Title -->
  <text x="200" y="40" text-anchor="middle" font-family="Arial, sans-serif" font-size="20" font-weight="bold" fill="{$colors[2]}">{$name}</text>
  <text x="200" y="60" text-anchor="middle" font-family="Arial, sans-serif" font-size="12" fill="{$colors[0]}">Atractivo Turístico</text>
</svg>
SVG;

    return $svg;
}

echo "🎨 Creando imágenes SVG para atractivos...\n\n";

$created = 0;
$skipped = 0;

foreach ($commonAttractions as $slug => $data) {
    $filename = "public/images/attractions/{$slug}.svg";
    
    if (!file_exists($filename)) {
        $svg = createSVG($slug, $data);
        
        // Crear directorio si no existe
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($filename, $svg);
        echo "✅ Creada: {$data['name']} ({$slug}.svg)\n";
        $created++;
    } else {
        echo "⏭️  Ya existe: {$data['name']}\n";
        $skipped++;
    }
}

echo "\n🎉 Proceso completado:\n";
echo "   • Imágenes creadas: {$created}\n";
echo "   • Ya existían: {$skipped}\n";
echo "   • Total: " . count($commonAttractions) . "\n";

if ($created > 0) {
    echo "\n💡 Las imágenes están en public/images/attractions/\n";
    echo "💡 Para generar más imágenes basadas en tu base de datos, ejecuta:\n";
    echo "   php artisan attractions:generate-images\n";
}