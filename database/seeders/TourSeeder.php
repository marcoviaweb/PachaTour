<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tour;
use App\Models\Attraction;
use Illuminate\Support\Str;

class TourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tours famosos de Bolivia
        $tours = [
            [
                'name' => 'Salar de Uyuni - Tour de 3 días',
                'slug' => 'salar-uyuni-3-dias',
                'description' => 'Experiencia completa en el salar más grande del mundo. Incluye atardeceres mágicos, amanecer en el salar, visita a islas de cactus, lagunas de colores y géiseres. Una aventura única en paisajes surrealistas.',
                'short_description' => 'Tour completo de 3 días por el Salar de Uyuni con lagunas de colores.',
                'type' => 'nature',
                'duration_days' => 3,
                'price_per_person' => 1200.00,
                'min_participants' => 2,
                'max_participants' => 8,
                'difficulty_level' => 'moderate',
                'meeting_point' => 'Terminal de Buses, Uyuni',
                'is_featured' => true,
            ],
            [
                'name' => 'Tiwanaku y Valle de la Luna',
                'slug' => 'tiwanaku-valle-luna',
                'description' => 'Combinación perfecta de arqueología y naturaleza. Visita las ruinas precolombinas de Tiwanaku por la mañana y explora las formaciones rocosas del Valle de la Luna por la tarde.',
                'short_description' => 'Arqueología en Tiwanaku y paisajes lunares en un día completo.',
                'type' => 'cultural',
                'duration_days' => 1,
                'duration_hours' => 10,
                'price_per_person' => 280.00,
                'min_participants' => 4,
                'max_participants' => 15,
                'difficulty_level' => 'easy',
                'meeting_point' => 'Plaza Murillo, La Paz',
                'is_featured' => true,
            ],
            [
                'name' => 'Aventura en el Parque Amboró',
                'slug' => 'aventura-amboro',
                'description' => 'Trekking de 4 días en uno de los parques más biodiversos del mundo. Observación de aves, cascadas espectaculares, bosques nublados y vida silvestre única.',
                'short_description' => 'Trekking de 4 días en el parque más biodiverso de Bolivia.',
                'type' => 'adventure',
                'duration_days' => 4,
                'price_per_person' => 850.00,
                'min_participants' => 3,
                'max_participants' => 10,
                'difficulty_level' => 'difficult',
                'meeting_point' => 'Samaipata, Santa Cruz',
                'is_featured' => true,
            ],
            [
                'name' => 'Ruta del Vino Tarijeño',
                'slug' => 'ruta-vino-tarija',
                'description' => 'Tour gastronómico por las mejores bodegas de Tarija. Degustación de vinos premium, singanis artesanales y gastronomía local en paisajes de viñedos.',
                'short_description' => 'Tour gastronómico por bodegas con degustación de vinos y singanis.',
                'type' => 'gastronomic',
                'duration_days' => 2,
                'price_per_person' => 450.00,
                'min_participants' => 2,
                'max_participants' => 12,
                'difficulty_level' => 'easy',
                'meeting_point' => 'Plaza Luis de Fuentes, Tarija',
                'is_featured' => true,
            ],
            [
                'name' => 'Carnaval de Oruro Experience',
                'slug' => 'carnaval-oruro',
                'description' => 'Vive el Carnaval de Oruro Patrimonio de la Humanidad. Incluye palcos preferenciales, visita al Santuario del Socavón, talleres de danza folklórica y gastronomía típica.',
                'short_description' => 'Experiencia completa del Carnaval de Oruro Patrimonio de la Humanidad.',
                'type' => 'cultural',
                'duration_days' => 3,
                'price_per_person' => 650.00,
                'min_participants' => 1,
                'max_participants' => 20,
                'difficulty_level' => 'easy',
                'meeting_point' => 'Plaza del Folklore, Oruro',
                'is_featured' => true,
                'available_from' => '2025-02-01',
                'available_until' => '2025-03-15',
            ],
            [
                'name' => 'Expedición Amazónica Beni',
                'slug' => 'expedicion-amazonica-beni',
                'description' => 'Aventura de 5 días en la Amazonía boliviana. Navegación por ríos, avistamiento de delfines rosados, pesca de pirañas, visita a comunidades indígenas y observación de fauna.',
                'short_description' => 'Expedición amazónica con delfines rosados y comunidades indígenas.',
                'type' => 'nature',
                'duration_days' => 5,
                'price_per_person' => 980.00,
                'min_participants' => 4,
                'max_participants' => 8,
                'difficulty_level' => 'moderate',
                'meeting_point' => 'Aeropuerto Trinidad, Beni',
                'is_featured' => true,
            ],
            [
                'name' => 'Sucre Colonial y Cal Orck\'o',
                'slug' => 'sucre-colonial-cal-orcko',
                'description' => 'Descubre la Ciudad Blanca Patrimonio de la Humanidad y las huellas de dinosaurios más importantes de Sudamérica. Historia, arquitectura y paleontología en un tour único.',
                'short_description' => 'Ciudad Blanca colonial y huellas de dinosaurios en Sucre.',
                'type' => 'historical',
                'duration_days' => 2,
                'price_per_person' => 320.00,
                'min_participants' => 2,
                'max_participants' => 15,
                'difficulty_level' => 'easy',
                'meeting_point' => 'Plaza 25 de Mayo, Sucre',
                'is_featured' => false,
            ],
            [
                'name' => 'Minas del Cerro Rico',
                'slug' => 'minas-cerro-rico',
                'description' => 'Experiencia única visitando las minas activas del Cerro Rico de Potosí. Conoce la historia de la montaña que cambió el mundo y las condiciones actuales de los mineros.',
                'short_description' => 'Visita a las minas activas del histórico Cerro Rico de Potosí.',
                'type' => 'historical',
                'duration_days' => 1,
                'duration_hours' => 6,
                'price_per_person' => 180.00,
                'min_participants' => 3,
                'max_participants' => 10,
                'difficulty_level' => 'difficult',
                'meeting_point' => 'Plaza 10 de Noviembre, Potosí',
                'is_featured' => false,
            ],
        ];

        foreach ($tours as $tourData) {
            // Agregar campos por defecto
            $tourData['currency'] = 'BOB';
            $tourData['included_services'] = [
                'Transporte', 'Guía especializado', 'Alimentación', 'Hospedaje'
            ];
            $tourData['excluded_services'] = [
                'Vuelos', 'Bebidas alcohólicas', 'Propinas', 'Gastos personales'
            ];
            $tourData['requirements'] = ['Documento de identidad', 'Condición física apropiada'];
            $tourData['what_to_bring'] = [
                'Ropa cómoda', 'Protector solar', 'Cámara fotográfica', 'Medicamentos personales'
            ];
            $tourData['departure_time'] = '08:00';
            $tourData['return_time'] = '18:00';
            $tourData['itinerary'] = [
                'day_1' => [
                    'title' => 'Primer día de aventura',
                    'activities' => ['Recepción', 'Inicio del tour', 'Actividades principales'],
                    'meals' => ['Almuerzo', 'Cena']
                ]
            ];
            $tourData['guide_language'] = 'es';
            $tourData['available_languages'] = ['es', 'en'];
            $tourData['rating'] = fake()->randomFloat(2, 4.0, 5.0);
            $tourData['reviews_count'] = fake()->numberBetween(20, 150);
            $tourData['bookings_count'] = fake()->numberBetween(50, 500);
            $tourData['is_active'] = true;

            $tour = Tour::create($tourData);

            // Asociar atractivos relacionados al tour
            $this->associateAttractions($tour);
        }

        // Crear tours adicionales usando factory
        $this->command->info('Creando tours adicionales con factory...');
        
        // 15 tours de día
        Tour::factory()
            ->count(15)
            ->dayTrip()
            ->create();

        // 8 tours de varios días
        Tour::factory()
            ->count(8)
            ->multiDay()
            ->create();

        // 5 tours de aventura
        Tour::factory()
            ->count(5)
            ->adventure()
            ->create();

        // 7 tours culturales
        Tour::factory()
            ->count(7)
            ->cultural()
            ->create();

        $this->command->info('✅ Creados ' . Tour::count() . ' tours de Bolivia');
    }

    /**
     * Associate attractions to tours based on tour type and location
     */
    private function associateAttractions(Tour $tour): void
    {
        $attractions = collect();

        // Asociar atractivos según el nombre/tipo del tour
        if (str_contains($tour->slug, 'uyuni')) {
            $attractions = Attraction::where('name', 'LIKE', '%Uyuni%')->get();
        } elseif (str_contains($tour->slug, 'tiwanaku')) {
            $attractions = Attraction::where('name', 'LIKE', '%Tiwanaku%')
                ->orWhere('name', 'LIKE', '%Valle de la Luna%')->get();
        } elseif (str_contains($tour->slug, 'amboro')) {
            $attractions = Attraction::where('name', 'LIKE', '%Amboró%')->get();
        } elseif (str_contains($tour->slug, 'tarija')) {
            $attractions = Attraction::where('name', 'LIKE', '%Concepción%')->get();
        } elseif (str_contains($tour->slug, 'oruro')) {
            $attractions = Attraction::where('name', 'LIKE', '%Socavón%')->get();
        } elseif (str_contains($tour->slug, 'beni')) {
            $attractions = Attraction::where('name', 'LIKE', '%Mamoré%')
                ->orWhere('name', 'LIKE', '%Casarabe%')->get();
        } elseif (str_contains($tour->slug, 'sucre')) {
            $attractions = Attraction::where('name', 'LIKE', '%Libertad%')
                ->orWhere('name', 'LIKE', '%Cal Orck%')->get();
        } elseif (str_contains($tour->slug, 'cerro-rico')) {
            $attractions = Attraction::where('name', 'LIKE', '%Cerro Rico%')->get();
        }

        // Si no encontramos atractivos específicos, tomar algunos aleatorios
        if ($attractions->isEmpty()) {
            $attractions = Attraction::inRandomOrder()->limit(rand(2, 4))->get();
        }

        // Asociar atractivos con datos del pivot
        foreach ($attractions as $index => $attraction) {
            $tour->attractions()->attach($attraction->id, [
                'visit_order' => $index + 1,
                'duration_minutes' => rand(60, 240),
                'notes' => 'Visita incluida en el tour',
                'is_optional' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
