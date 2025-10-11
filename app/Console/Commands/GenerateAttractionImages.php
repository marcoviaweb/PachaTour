<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attraction;
use Illuminate\Support\Str;

class GenerateAttractionImages extends Command
{
    protected $signature = 'attractions:generate-images {--force : Regenerar todas las imágenes}';
    protected $description = 'Genera imágenes SVG placeholder para atractivos sin imagen';

    // Colores base para diferentes tipos de atractivos
    private $typeColors = [
        'natural' => ['#4CAF50', '#8BC34A', '#2E7D32'],
        'cultural' => ['#FF9800', '#FFB74D', '#E65100'],
        'historical' => ['#795548', '#A1887F', '#5D4037'],
        'religious' => ['#9C27B0', '#BA68C8', '#6A1B9A'],
        'archaeological' => ['#607D8B', '#90A4AE', '#37474F'],
        'gastronomic' => ['#F44336', '#EF5350', '#C62828'],
        'adventure' => ['#FF5722', '#FF8A65', '#D84315'],
        'urban' => ['#2196F3', '#64B5F6', '#1565C0'],
    ];

    public function handle()
    {
        $this->info('🎨 Generando imágenes SVG para atractivos...');
        
        $force = $this->option('force');
        $attractions = Attraction::all();
        $generated = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($attractions->count());
        $progressBar->start();

        foreach ($attractions as $attraction) {
            $slug = $attraction->slug;
            $imagePath = public_path("images/attractions/{$slug}.svg");
            
            // Solo generar si no existe la imagen o si se fuerza
            if (!file_exists($imagePath) || $force) {
                $svg = $this->generateSVG($attraction);
                
                // Crear directorio si no existe
                $directory = dirname($imagePath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                file_put_contents($imagePath, $svg);
                $generated++;
            } else {
                $skipped++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("✅ Proceso completado:");
        $this->line("   • Imágenes generadas: {$generated}");
        $this->line("   • Imágenes existentes: {$skipped}");
        $this->line("   • Total procesadas: " . $attractions->count());
        
        if ($generated > 0) {
            $this->info("\n🎉 Las nuevas imágenes están disponibles en public/images/attractions/");
        }

        return 0;
    }

    private function generateSVG($attraction)
    {
        $type = $attraction->type ?? 'natural';
        $colors = $this->typeColors[$type] ?? $this->typeColors['natural'];
        
        $primaryColor = $colors[0];
        $secondaryColor = $colors[1];
        $accentColor = $colors[2];
        
        $name = Str::limit($attraction->name, 25);
        $typeLabel = ucfirst(str_replace('_', ' ', $type));
        
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
  
SVG;

        // Agregar elementos específicos por tipo
        $svg .= $this->generateElementsByType($type, $colors);

        $svg .= <<<SVG
  
  <!-- Title -->
  <text x="200" y="40" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" font-weight="bold" fill="{$accentColor}">{$name}</text>
  <text x="200" y="60" text-anchor="middle" font-family="Arial, sans-serif" font-size="12" fill="{$primaryColor}">{$typeLabel}</text>
</svg>
SVG;

        return $svg;
    }

    private function generateElementsByType($type, $colors)
    {
        switch ($type) {
            case 'natural':
                return $this->generateNaturalElements($colors);
            case 'cultural':
                return $this->generateCulturalElements($colors);
            case 'historical':
                return $this->generateHistoricalElements($colors);
            case 'religious':
                return $this->generateReligiousElements($colors);
            case 'archaeological':
                return $this->generateArchaeologicalElements($colors);
            case 'urban':
                return $this->generateUrbanElements($colors);
            case 'gastronomic':
                return $this->generateGastronomicElements($colors);
            case 'adventure':
                return $this->generateAdventureElements($colors);
            default:
                return $this->generateNaturalElements($colors);
        }
    }

    private function generateNaturalElements($colors)
    {
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
  
  <!-- Clouds -->
  <ellipse cx="100" cy="90" rx="20" ry="12" fill="white" opacity="0.8"/>
  <ellipse cx="300" cy="85" rx="25" ry="15" fill="white" opacity="0.8"/>
SVG;
    }

    private function generateCulturalElements($colors)
    {
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
  
  <!-- People silhouettes -->
  <circle cx="100" cy="190" r="4" fill="{$colors[2]}"/>
  <rect x="98" y="194" width="4" height="8" fill="{$colors[2]}"/>
SVG;
    }

    private function generateHistoricalElements($colors)
    {
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
  
  <!-- Ancient columns -->
  <rect x="120" y="160" width="8" height="40" fill="{$colors[2]}" opacity="0.8"/>
  <rect x="270" y="150" width="8" height="50" fill="{$colors[2]}" opacity="0.8"/>
SVG;
    }

    private function generateReligiousElements($colors)
    {
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
  
  <!-- Bell tower -->
  <rect x="190" y="90" width="20" height="30" fill="{$colors[2]}"/>
  <circle cx="200" cy="95" r="3" fill="{$colors[0]}"/>
SVG;
    }

    private function generateArchaeologicalElements($colors)
    {
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
  
  <!-- Petroglyphs -->
  <circle cx="180" cy="140" r="2" fill="{$colors[0]}"/>
  <circle cx="220" cy="145" r="2" fill="{$colors[0]}"/>
SVG;
    }

    private function generateUrbanElements($colors)
    {
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
  
  <!-- Traffic lights -->
  <rect x="50" y="200" width="4" height="15" fill="{$colors[2]}"/>
  <circle cx="52" cy="205" r="2" fill="#4CAF50"/>
SVG;
    }

    private function generateGastronomicElements($colors)
    {
        return <<<SVG
  <!-- Market stall -->
  <rect x="150" y="140" width="100" height="60" fill="{$colors[1]}"/>
  <polygon points="140,140 200,120 260,140" fill="{$colors[2]}"/>
  
  <!-- Food items -->
  <circle cx="170" cy="160" r="8" fill="{$colors[0]}"/>
  <circle cx="190" cy="165" r="6" fill="#FF9800"/>
  <circle cx="210" cy="160" r="7" fill="#4CAF50"/>
  <circle cx="230" cy="165" r="5" fill="#F44336"/>
  
  <!-- Cooking fire -->
  <ellipse cx="200" cy="180" rx="4" ry="8" fill="#FF6F00"/>
  <ellipse cx="200" cy="178" rx="3" ry="6" fill="#FFD54F"/>
  
  <!-- Plants/ingredients -->
  <circle cx="100" cy="180" r="12" fill="#4CAF50"/>
  <circle cx="300" cy="170" r="10" fill="#8BC34A"/>
  
  <!-- Smoke -->
  <ellipse cx="200" cy="165" rx="3" ry="10" fill="white" opacity="0.6"/>
SVG;
    }

    private function generateAdventureElements($colors)
    {
        return <<<SVG
  <!-- Mountains -->
  <path d="M0,180 L80,100 L160,140 L240,80 L320,120 L400,90 L400,300 L0,300 Z" fill="{$colors[1]}"/>
  
  <!-- Climbing route -->
  <path d="M100,250 Q150,200 200,180 Q250,160 300,140" stroke="{$colors[0]}" stroke-width="3" fill="none" stroke-dasharray="5,5"/>
  
  <!-- Equipment -->
  <circle cx="180" cy="190" r="3" fill="{$colors[2]}"/>
  <circle cx="220" cy="170" r="3" fill="{$colors[2]}"/>
  <circle cx="260" cy="150" r="3" fill="{$colors[2]}"/>
  
  <!-- Rope -->
  <path d="M180,190 Q200,180 220,170 Q240,160 260,150" stroke="{$colors[2]}" stroke-width="2" fill="none"/>
  
  <!-- Adventure symbols -->
  <polygon points="80,200 85,190 90,200" fill="{$colors[0]}"/>
  <polygon points="320,160 325,150 330,160" fill="{$colors[0]}"/>
  
  <!-- Trail markers -->
  <rect x="120" y="220" width="4" height="12" fill="{$colors[2]}"/>
  <rect x="280" y="180" width="4" height="12" fill="{$colors[2]}"/>
SVG;
    }
}