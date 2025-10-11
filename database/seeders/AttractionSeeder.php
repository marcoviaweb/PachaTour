<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attraction;
use App\Models\Department;

class AttractionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener departamentos
        $departments = Department::all()->keyBy('slug');

        // Atractivos famosos de Bolivia por departamento
        $attractions = [
            // LA PAZ
            [
                'department_slug' => 'la-paz',
                'name' => 'Valle de la Luna',
                'slug' => 'valle-de-la-luna',
                'description' => 'Formación geológica única ubicada a 10 km de La Paz, con paisajes que parecen de otro planeta. Sus formaciones rocosas erosionadas por el viento y la lluvia crean un espectáculo natural impresionante.',
                'short_description' => 'Formaciones rocosas únicas que parecen paisajes lunares.',
                'type' => 'natural',
                'latitude' => -16.5667,
                'longitude' => -68.0833,
                'city' => 'La Paz',
                'entry_price' => 15.00,
                'difficulty_level' => 'Fácil',
                'estimated_duration' => 120,
                'best_season' => 'Todo el año',
                'is_featured' => true,
            ],
            [
                'department_slug' => 'la-paz',
                'name' => 'Tiwanaku',
                'slug' => 'tiwanaku',
                'description' => 'Sitio arqueológico precolombino, cuna de una de las civilizaciones más importantes de América. Patrimonio de la Humanidad por la UNESCO, con impresionantes monolitos y la famosa Puerta del Sol.',
                'short_description' => 'Sitio arqueológico precolombino, Patrimonio de la Humanidad.',
                'type' => 'archaeological',
                'latitude' => -16.5547,
                'longitude' => -68.6739,
                'city' => 'Tiwanaku',
                'entry_price' => 100.00,
                'difficulty_level' => 'Fácil',
                'estimated_duration' => 180,
                'best_season' => 'Todo el año',
                'is_featured' => true,
            ],
            [
                'department_slug' => 'la-paz',
                'name' => 'Teleférico La Paz',
                'slug' => 'teleferico-la-paz',
                'description' => 'Sistema de teleféricos más alto y extenso del mundo, conectando La Paz con El Alto. Ofrece vistas espectaculares de la ciudad y las montañas nevadas.',
                'short_description' => 'Sistema de teleféricos más alto del mundo con vistas espectaculares.',
                'type' => 'urban',
                'latitude' => -16.5000,
                'longitude' => -68.1193,
                'city' => 'La Paz',
                'entry_price' => 3.00,
                'difficulty_level' => 'Fácil',
                'estimated_duration' => 60,
                'best_season' => 'Todo el año',
                'is_featured' => true,
            ],

            // POTOSÍ
            [
                'department_slug' => 'potosi',
                'name' => 'Salar de Uyuni',
                'slug' => 'salar-de-uyuni',
                'description' => 'El desierto de sal más grande del mundo, con 10,582 km². Durante la época de lluvias se convierte en un espejo gigante que refleja el cielo. Uno de los destinos más fotografiados del planeta.',
                'short_description' => 'El desierto de sal más grande del mundo, espejo natural del cielo.',
                'type' => 'natural',
                'latitude' => -20.1338,
                'longitude' => -67.4891,
                'city' => 'Uyuni',
                'entry_price' => 30.00,
                'difficulty_level' => 'Fácil',
                'estimated_duration' => 480,
                'best_season' => 'Época seca (Mayo-Octubre)',
                'is_featured' => true,
            ],
            [
                'department_slug' => 'potosi',
                'name' => 'Cerro Rico',
                'slug' => 'cerro-rico',
                'description' => 'Montaña que hizo de Potosí la ciudad más rica del mundo colonial. Hoy se pueden visitar las minas y conocer las duras condiciones de trabajo de los mineros.',
                'short_description' => 'Montaña histórica con minas visitables y rica historia colonial.',
                'type' => 'historical',
                'latitude' => -19.6167,
                'longitude' => -65.7500,
                'city' => 'Potosí',
                'entry_price' => 80.00,
                'difficulty_level' => 'Difícil',
                'estimated_duration' => 240,
                'best_season' => 'Todo el año',
                'is_featured' => true,
            ],

            // SANTA CRUZ
            [
                'department_slug' => 'santa-cruz',
                'name' => 'Parque Nacional Amboró',
                'slug' => 'parque-nacional-amboro',
                'description' => 'Uno de los parques con mayor biodiversidad del mundo, hogar de más de 800 especies de aves. Ofrece desde selva amazónica hasta bosques nublados andinos.',
                'short_description' => 'Parque con la mayor biodiversidad del mundo, más de 800 especies de aves.',
                'type' => 'natural',
                'latitude' => -17.8000,
                'longitude' => -63.7000,
                'city' => 'Samaipata',
                'entry_price' => 50.00,
                'difficulty_level' => 'Moderado',
                'estimated_duration' => 360,
                'best_season' => 'Época seca (Mayo-Octubre)',
                'is_featured' => true,
            ],
            [
                'department_slug' => 'santa-cruz',
                'name' => 'El Fuerte de Samaipata',
                'slug' => 'el-fuerte-samaipata',
                'description' => 'Sitio arqueológico precolombino, la roca tallada más grande del mundo. Patrimonio de la Humanidad por la UNESCO, con misteriosas esculturas en piedra.',
                'short_description' => 'La roca tallada más grande del mundo, Patrimonio de la Humanidad.',
                'type' => 'archaeological',
                'latitude' => -18.1786,
                'longitude' => -63.8708,
                'city' => 'Samaipata',
                'entry_price' => 30.00,
                'difficulty_level' => 'Fácil',
                'estimated_duration' => 120,
                'best_season' => 'Todo el año',
                'is_featured' => true,
            ],

            // COCHABAMBA
            [
                'department_slug' => 'cochabamba',
                'name' => 'Cristo de la Concordia',
                'slug' => 'cristo-de-la-concordia',
                'description' => 'Estatua de Cristo más alta de América del Sur con 40.44 metros. Ubicada en el Cerro San Pedro, ofrece vistas panorámicas de todo el valle de Cochabamba.',
                'short_description' => 'Estatua de Cristo más alta de Sudamérica con vistas panorámicas.',
                'type' => 'religious',
                'latitude' => -17.3647,
                'longitude' => -66.1378,
                'city' => 'Cochabamba',
                'entry_price' => 10.00,
                'difficulty_level' => 'Fácil',
                'estimated_duration' => 90,
                'best_season' => 'Todo el año',
                'is_featured' => true,
            ],

            // ORURO
            [
                'department_slug' => 'oruro',
                'name' => 'Santuario del Socavón',
                'slug' => 'santuario-socavon',
                'description' => 'Santuario de la Virgen del Socavón, patrona de los mineros y centro del famoso Carnaval de Oruro. Incluye museo de minerales y arte folklórico.',
                'short_description' => 'Santuario de la Virgen del Socavón, centro del Carnaval de Oruro.',
                'type' => 'religious',
                'latitude' => -17.9667,
                'longitude' => -67.1167,
                'city' => 'Oruro',
                'entry_price' => 15.00,
                'difficulty_level' => 'Fácil',
                'estimated_duration' => 60,
                'best_season' => 'Todo el año',
                'is_featured' => true,
            ],

            // CHUQUISACA
            [
                'department_slug' => 'chuquisaca',
                'name' => 'Casa de la Libertad',
                'slug' => 'casa-de-la-libertad',
                'description' => 'Lugar donde se firmó la independencia de Bolivia en 1825. Museo que conserva documentos históricos y objetos de la época colonial y republicana.',
                'short_description' => 'Lugar histórico donde se firmó la independencia de Bolivia.',
                'type' => 'historical',
                'latitude' => -19.0333,
                'longitude' => -65.2627,
                'city' => 'Sucre',
                'entry_price' => 20.00,
                'difficulty_level' => 'Fácil',
                'estimated_duration' => 90,
                'best_season' => 'Todo el año',
                'is_featured' => true,
            ],
            [
                'department_slug' => 'chuquisaca',
                'name' => 'Cal Orck\'o',
                'slug' => 'cal-orcko',
                'description' => 'Yacimiento paleontológico con más de 5,000 huellas de dinosaurios de 68 millones de años. La pared con huellas de dinosaurios más grande del mundo.',
                'short_description' => 'Pared con huellas de dinosaurios más grande del mundo.',
                'type' => 'natural',
                'latitude' => -19.0500,
                'longitude' => -65.2000,
                'city' => 'Sucre',
                'entry_price' => 30.00,
                'difficulty_level' => 'Fácil',
                'estimated_duration' => 120,
                'best_season' => 'Todo el año',
                'is_featured' => true,
            ],

            // TARIJA
            [
                'department_slug' => 'tarija',
                'name' => 'Valle de la Concepción',
                'slug' => 'valle-concepcion',
                'description' => 'Región vinícola más importante de Bolivia, con bodegas que producen vinos y singanis de calidad internacional. Paisajes de viñedos y tradición vitivinícola.',
                'short_description' => 'Región vinícola con los mejores vinos y singanis de Bolivia.',
                'type' => 'gastronomic',
                'latitude' => -21.4500,
                'longitude' => -64.8000,
                'city' => 'Concepción',
                'entry_price' => 40.00,
                'difficulty_level' => 'Fácil',
                'estimated_duration' => 180,
                'best_season' => 'Todo el año',
                'is_featured' => true,
            ],

            // BENI
            [
                'department_slug' => 'beni',
                'name' => 'Lomas de Casarabe',
                'slug' => 'lomas-casarabe',
                'description' => 'Sitio arqueológico amazónico con estructuras precolombinas monumentales. Recientemente descubierto, revela una civilización amazónica avanzada.',
                'short_description' => 'Sitio arqueológico amazónico con estructuras monumentales precolombinas.',
                'type' => 'archaeological',
                'latitude' => -13.6000,
                'longitude' => -64.8000,
                'city' => 'Trinidad',
                'entry_price' => 25.00,
                'difficulty_level' => 'Moderado',
                'estimated_duration' => 240,
                'best_season' => 'Época seca (Mayo-Octubre)',
                'is_featured' => true,
            ],

            // PANDO
            [
                'department_slug' => 'pando',
                'name' => 'Reserva Nacional Manuripi',
                'slug' => 'reserva-manuripi',
                'description' => 'Área protegida amazónica con bosques de castaña y biodiversidad única. Hogar de jaguares, tapires y más de 400 especies de aves.',
                'short_description' => 'Reserva amazónica con bosques de castaña y fauna única.',
                'type' => 'natural',
                'latitude' => -11.5000,
                'longitude' => -67.5000,
                'city' => 'Cobija',
                'entry_price' => 35.00,
                'difficulty_level' => 'Difícil',
                'estimated_duration' => 480,
                'best_season' => 'Época seca (Mayo-Octubre)',
                'is_featured' => true,
            ],
        ];

        foreach ($attractions as $attractionData) {
            $departmentSlug = $attractionData['department_slug'];
            unset($attractionData['department_slug']);
            
            $department = $departments[$departmentSlug];
            $attractionData['department_id'] = $department->id;
            
            // Agregar datos comunes
            $attractionData['currency'] = 'BOB';
            $attractionData['opening_hours'] = [
                'monday' => ['open' => '08:00', 'close' => '17:00'],
                'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                'thursday' => ['open' => '08:00', 'close' => '17:00'],
                'friday' => ['open' => '08:00', 'close' => '17:00'],
                'saturday' => ['open' => '08:00', 'close' => '18:00'],
                'sunday' => ['open' => '09:00', 'close' => '16:00'],
            ];
            $attractionData['amenities'] = ['Guía turístico', 'Estacionamiento', 'Baños'];
            $attractionData['rating'] = fake()->randomFloat(2, 4.0, 5.0);
            $attractionData['reviews_count'] = fake()->numberBetween(50, 300);
            $attractionData['visits_count'] = fake()->numberBetween(500, 10000);
            $attractionData['is_active'] = true;

            Attraction::create($attractionData);
        }

        // Crear atractivos adicionales usando factory
        $this->command->info('Creando atractivos adicionales con factory...');
        
        foreach ($departments as $department) {
            // 3-5 atractivos adicionales por departamento
            Attraction::factory()
                ->count(fake()->numberBetween(3, 5))
                ->create(['department_id' => $department->id]);
        }

        $this->command->info('✅ Creados atractivos turísticos de Bolivia (famosos + generados)');
    }
}
