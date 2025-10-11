<?php

/**
 * Script para generar imágenes SVG placeholder para atractivos sin imagen
 */

require_once 'vendor/autoload.php';

use App\Models\Attraction;
use Illuminate\Support\Str;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Colores base para diferentes tipos de atractivos
$typeColors = [
    'natural' => ['#4CAF50', '#8BC34A', '#2E7D32'],
    'cultural' => ['#FF9800', '#FFB74D', '#E65100'],
    'historical' => ['#795548', '#A1887F', '#5D4037'],
    'religious' => ['#9C27B0', '#BA68C8', '#6A1B9A'],
    'archaeological' => ['#607D8B', '#90A4AE', '#37474F'],
    'gastronomic' => ['#F44336', '#EF5350', '#C62828'],
    'adventure' => ['#FF5722', '#FF8A65', '#D84315'],
    'urban' => ['#2196F3', '#64B5F6', '#1565C0'],
];

// Elementos decorativos por tipo
$typeElements = [
    'natural' => ['mountains', 'trees', 'river'],
    'cultural' => ['buildings', 'art', 'people'],
    'historical' => ['monuments', 'ruins', 'artifacts'],
    'religious' => ['temple', 'symbols', 'light'],
    'archaeological' => ['ruins', 'stones', 'symbols'],
    'gastronomic' => ['food', 'plants', 'market'],
    'adventure' => ['mountains', 'equipment', 'trails'],
    'urban' => ['buildings', 'streets', 'modern'],
];

function generateSVG($attraction) {
    global $typeColors, $typeElements;
    
    $type = $attraction->type ?? 'natural';
    $colors = $typeColors[$type] ?? $typeColors['natural'];
    $elements = $typeElements[$type] ?? $typeElements['natural'];
    
    $primaryColor = $colors[0];
    $secondaryColor = $colors[1];
    $accentColor = $colors[2];
    
    $name = $attraction->name;
    $typeLabel = ucfirst($type);
    
    $svg = <<<SVG
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" style="stop-color:{$secondaryColor};stop-opacity:0.3" />
      <stop offset="100%" style="stop-color:{$primaryColor};stop-opacity:0.1" />
    </linearGradient>
    <linearGradient id="mainGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" style="stop-color:{$secondaryColor};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$accentColor};stop-opacity:1" />
    </linearGradient>
  </defs>
  
  <!-- Background -->
  <rect width="400" height="300" fill="url(#bgGrad)"/>
  
  <!-- Main shape based on type -->
SVG;

    // Agregar elementos específicos por tipo
    switch ($type) {
        case 'natural':
            $svg .= generateNaturalElements($colors);
            break;
        case 'cultural':
            $svg .= generateCulturalElements($colors);
            break;
        case 'historical':
            $svg .= generateHistoricalElements($colors);
            break;
        case 'religious':
            $svg .= generateReligiousElements($colors);
            break;
        case 'archaeological':
            $svg .= generateArchaeologicalElements($colors);
            break;
        case 'urban':
            $svg .= generateUrbanElements($colors);
            break;
        default:
            $svg .= generateNaturalElements($colors);
    }

    $svg .= <<<SVG
  
  <!-- Title -->
  <text x="200" y="40" text-anchor="middle" font-family="Arial, sans-serif" font-size="20" font-weight="bold" fill="{$accentColor}">{$name}</text>
  <text x="200" y="60" text-anchor="middle" font-family="Arial, sans-serif" font-size="12" fill="{$primaryColor}">{$typeLabel}</text>
</svg>
SVG;

    return $svg;
}

function generateNaturalElements($colors) {
    return <<<SVG
  <!-- Mountains -->
  <path d="M0,150 L100,80 L200,120 L300,70 L400,100 L400,300 L0,300 Z" fill="{$colors[1]}" opacity="0.7"/>
  
  <!-- Trees -->
  <circle cx="80" cy="140" r="20" fill="{$colors[0]}"/>
  <rect x="75" y="150" width="10" height="15" fill="{$colors[2]}"/>
  
  <circle cx="320" cy="130" r="25" fill="{$colors[0]}"/>
  <rect x="315" y="145" width="10" height="20" fill="{$colors[2]}"/>
  
  <!-- River -->
  <path d="M50,200 Q150,180 250,190 T380,200" stroke="#2196F3" stroke-width="8" fill="none" opacity="0.6"/>
SVG;
}

function generateCulturalElements($colors) {
    return <<<SVG
  <!-- Building -->
  <rect x="150" y="120" width="100" height="80" fill="{$colors[1]}"/>
  <polygon points="140,120 200,80 260,120" fill="{$colors[2]}"/>
  
  <!-- Windows -->
  <rect x="170" y="140" width="15" height="20" fill="{$colors[0]}"/>
  <rect x="215" y="140" width="15" height="20" fill="{$colors[0]}"/>
  
  <!-- Door -->
  <rect x="190" y="170" width="20" height="30" fill="{$colors[2]}"/>
  
  <!-- Decorative elements -->
  <circle cx="120" cy="180" r="8" fill="{$colors[0]}" opacity="0.7"/>
  <circle cx="280" cy="170" r="10" fill="{$colors[0]}" opacity="0.7"/>
SVG;
}

function generateHistoricalElements($colors) {
    return <<<SVG
  <!-- Monument -->
  <rect x="180" y="100" width="40" height="100" fill="{$colors[1]}"/>
  <polygon points="175,100 200,80 225,100" fill="{$colors[2]}"/>
  
  <!-- Base -->
  <rect x="170" y="200" width="60" height="20" fill="{$colors[2]}"/>
  
  <!-- Historical symbols -->
  <circle cx="200" cy="150" r="8" fill="{$colors[0]}"/>
  <path d="M200,142 L204,150 L200,158 L196,150 Z" fill="{$colors[0]}"/>
  
  <!-- Ruins -->
  <rect x="100" y="180" width="20" height="30" fill="{$colors[1]}" opacity="0.6"/>
  <rect x="280" y="170" width="25" height="40" fill="{$colors[1]}" opacity="0.6"/>
SVG;
}

function generateReligiousElements($colors) {
    return <<<SVG
  <!-- Temple -->
  <rect x="160" y="120" width="80" height="80" fill="{$colors[1]}"/>
  <polygon points="150,120 200,90 250,120" fill="{$colors[2]}"/>
  
  <!-- Cross or religious symbol -->
  <line x1="200" y1="100" x2="200" y2="130" stroke="{$colors[0]}" stroke-width="4"/>
  <line x1="185" y1="115" x2="215" y2="115" stroke="{$colors[0]}" stroke-width="4"/>
  
  <!-- Sacred light -->
  <circle cx="200" cy="115" r="20" fill="{$colors[0]}" opacity="0.3"/>
  <circle cx="200" cy="115" r="30" fill="none" stroke="{$colors[0]}" stroke-width="1" opacity="0.5"/>
  
  <!-- Steps -->
  <rect x="170" y="200" width="60" height="5" fill="{$colors[2]}"/>
  <rect x="175" y="205" width="50" height="5" fill="{$colors[2]}"/>
SVG;
}

function generateArchaeologicalElements($colors) {
    return <<<SVG
  <!-- Ancient structure -->
  <path d="M150,200 L200,120 L250,200 Z" fill="{$colors[1]}"/>
  
  <!-- Stone blocks -->
  <rect x="160" y="180" width="20" height="15" fill="{$colors[2]}"/>
  <rect x="185" y="185" width="18" height="12" fill="{$colors[2]}"/>
  <rect x="210" y="180" width="22" height="15" fill="{$colors[2]}"/>
  
  <!-- Ancient symbols -->
  <circle cx="200" cy="160" r="5" fill="{$colors[0]}"/>
  <path d="M190,150 L195,155 L190,160 L185,155 Z" fill="{$colors[0]}"/>
  <path d="M210,150 L215,155 L210,160 L205,155 Z" fill="{$colors[0]}"/>
  
  <!-- Scattered stones -->
  <ellipse cx="120" cy="220" rx="8" ry="5" fill="{$colors[1]}"/>
  <ellipse cx="280" cy="210" rx="10" ry="6" fill="{$colors[1]}"/>
SVG;
}

function generateUrbanElements($colors) {
    return <<<SVG
  <!-- Buildings -->
  <rect x="100" y="100" width="40" height="100" fill="{$colors[1]}"/>
  <rect x="160" y="80" width="50" height="120" fill="{$colors[2]}"/>
  <rect x="230" y="110" width="35" height="90" fill="{$colors[1]}"/>
  <rect x="280" y="90" width="45" height="110" fill="{$colors[2]}"/>
  
  <!-- Windows -->
  <rect x="110" y="120" width="8" height="10" fill="{$colors[0]}"/>
  <rect x="125" y="120" width="8" height="10" fill="{$colors[0]}"/>
  <rect x="110" y="140" width="8" height="10" fill="{$colors[0]}"/>
  <rect x="125" y="140" width="8" height="10" fill="{$colors[0]}"/>
  
  <rect x="170" y="100" width="10" height="12" fill="{$colors[0]}"/>
  <rect x="190" y="100" width="10" height="12" fill="{$colors[0]}"/>
  <rect x="170" y="125" width="10" height="12" fill="{$colors[0]}"/>
  <rect x="190" y="125" width="10" height="12" fill="{$colors[0]}"/>
  
  <!-- Street -->
  <rect x="0" y="220" width="400" height="20" fill="{$colors[2]}" opacity="0.3"/>
SVG;
}

// Obtener todos los atractivos
echo "Generando imágenes para atractivos...\n";

$attractions = Attraction::all();
$generated = 0;

foreach ($attractions as $attraction) {
    $slug = $attraction->slug;
    $imagePath = "public/images/attractions/{$slug}.svg";
    
    // Solo generar si no existe la imagen
    if (!file_exists($imagePath)) {
        $svg = generateSVG($attraction);
        file_put_contents($imagePath, $svg);
        echo "✅ Generada imagen para: {$attraction->name} ({$slug}.svg)\n";
        $generated++;
    } else {
        echo "⏭️  Ya existe imagen para: {$attraction->name}\n";
    }
}

echo "\n🎉 Proceso completado. Se generaron {$generated} imágenes nuevas.\n";