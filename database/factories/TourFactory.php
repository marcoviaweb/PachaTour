<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tour;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tourTypes = [
            'Aventura', 'Expedición', 'Ruta', 'Trekking', 'Tour', 'Navegación',
            'Circuito', 'Safari', 'Escalada', 'Caminata', 'Viaje', 'Excursión'
        ];
        
        $destinations = [
            'Salar de Uyuni', 'Cerro Rico', 'Misiones Jesuíticas', 'Parque Amboró',
            'Valle Tarijeño', 'Río Mamoré', 'Tiwanaku', 'Oruro', 'Amazonía',
            'Sucre Colonial', 'Cordillera Real', 'Beni', 'Viñedos', 'Lomas de Casarabe',
            'La Paz', 'Potosí', 'Cochabamba', 'Santa Cruz', 'Tarija'
        ];

        $name = $this->faker->randomElement($tourTypes) . ' ' . $this->faker->randomElement($destinations) . ' ' . $this->faker->numberBetween(1, 999);
        $types = array_keys(Tour::TYPES);
        $difficulties = array_keys(Tour::DIFFICULTIES);
        $duration = $this->faker->numberBetween(1, 7);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(15),
            'type' => $this->faker->randomElement($types),
            'duration_days' => $duration,
            'duration_hours' => $duration == 1 ? $this->faker->numberBetween(4, 12) : null,
            'price_per_person' => $this->faker->randomFloat(2, 50, 2000),
            'currency' => 'BOB',
            'min_participants' => $this->faker->numberBetween(1, 4),
            'max_participants' => $this->faker->numberBetween(8, 25),
            'difficulty_level' => $this->faker->randomElement($difficulties),
            'included_services' => $this->faker->randomElements([
                'Transporte', 'Guía especializado', 'Alimentación completa',
                'Hospedaje', 'Equipo especializado', 'Seguro de viaje',
                'Entrada a atractivos', 'Actividades programadas'
            ], $this->faker->numberBetween(3, 6)),
            'excluded_services' => $this->faker->randomElements([
                'Vuelos internacionales', 'Bebidas alcohólicas', 'Propinas',
                'Gastos personales', 'Seguro médico adicional', 'Actividades opcionales'
            ], $this->faker->numberBetween(2, 4)),
            'requirements' => $this->faker->randomElements([
                'Pasaporte vigente', 'Condición física buena', 'Experiencia previa',
                'Certificado médico', 'Seguro de viaje', 'Vacunas al día'
            ], $this->faker->numberBetween(1, 3)),
            'what_to_bring' => $this->faker->randomElements([
                'Ropa abrigada', 'Protector solar', 'Sombrero', 'Zapatos de trekking',
                'Cámara fotográfica', 'Medicamentos personales', 'Linterna',
                'Botella de agua', 'Snacks energéticos'
            ], $this->faker->numberBetween(4, 7)),
            'meeting_point' => $this->faker->randomElement([
                'Plaza Murillo, La Paz',
                'Terminal de Buses, Santa Cruz',
                'Plaza 14 de Septiembre, Cochabamba',
                'Plaza 10 de Noviembre, Potosí',
                'Plaza del Folklore, Oruro',
                'Plaza 25 de Mayo, Sucre',
                'Plaza Luis de Fuentes, Tarija',
                'Plaza Ballivián, Trinidad',
                'Plaza Germán Busch, Cobija'
            ]),
            'departure_time' => $this->faker->time('H:i', '10:00'),
            'return_time' => $this->faker->optional()->time('H:i', '18:00'),
            'itinerary' => [
                'day_1' => [
                    'title' => 'Día 1: Llegada y orientación',
                    'activities' => ['Recepción en punto de encuentro', 'Briefing del tour', 'Inicio de actividades'],
                    'meals' => ['Almuerzo', 'Cena'],
                    'accommodation' => $duration > 1 ? 'Hotel 3 estrellas' : null
                ],
                'day_2' => $duration > 1 ? [
                    'title' => 'Día 2: Actividad principal',
                    'activities' => ['Actividad principal del tour', 'Visita a atractivos', 'Tiempo libre'],
                    'meals' => ['Desayuno', 'Almuerzo', 'Cena'],
                    'accommodation' => $duration > 2 ? 'Hotel 3 estrellas' : null
                ] : null
            ],
            'guide_language' => 'es',
            'available_languages' => $this->faker->randomElements(['es', 'en', 'pt', 'fr'], $this->faker->numberBetween(1, 3)),
            'rating' => $this->faker->randomFloat(2, 3.5, 5.0),
            'reviews_count' => $this->faker->numberBetween(0, 200),
            'bookings_count' => $this->faker->numberBetween(0, 500),
            'is_featured' => $this->faker->boolean(25), // 25% de probabilidad
            'is_active' => $this->faker->boolean(90), // 90% activos
            'available_from' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'available_until' => $this->faker->optional()->dateTimeBetween('+2 months', '+1 year'),
        ];
    }

    /**
     * Indicate that the tour is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'rating' => $this->faker->randomFloat(2, 4.2, 5.0),
            'reviews_count' => $this->faker->numberBetween(50, 300),
            'bookings_count' => $this->faker->numberBetween(100, 800),
        ]);
    }

    /**
     * Indicate that the tour is a day trip.
     */
    public function dayTrip(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'day_trip',
            'duration_days' => 1,
            'duration_hours' => $this->faker->numberBetween(6, 12),
            'price_per_person' => $this->faker->randomFloat(2, 50, 300),
        ]);
    }

    /**
     * Indicate that the tour is multi-day.
     */
    public function multiDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'multi_day',
            'duration_days' => $this->faker->numberBetween(3, 10),
            'duration_hours' => null,
            'price_per_person' => $this->faker->randomFloat(2, 300, 2000),
        ]);
    }

    /**
     * Indicate that the tour is adventure type.
     */
    public function adventure(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'adventure',
            'difficulty_level' => $this->faker->randomElement(['moderate', 'difficult', 'extreme']),
            'requirements' => ['Condición física excelente', 'Experiencia previa', 'Certificado médico'],
        ]);
    }

    /**
     * Indicate that the tour is cultural type.
     */
    public function cultural(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cultural',
            'difficulty_level' => 'easy',
            'included_services' => ['Guía especializado', 'Entrada a museos', 'Transporte', 'Almuerzo'],
        ]);
    }

    /**
     * Indicate that the tour is expensive.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_per_person' => $this->faker->randomFloat(2, 1000, 3000),
            'included_services' => [
                'Transporte privado', 'Guía especializado bilingüe', 'Alimentación gourmet',
                'Hospedaje 4-5 estrellas', 'Seguro premium', 'Actividades exclusivas'
            ],
        ]);
    }

    /**
     * Indicate that the tour is budget-friendly.
     */
    public function budget(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_per_person' => $this->faker->randomFloat(2, 30, 150),
            'included_services' => ['Guía local', 'Transporte compartido'],
            'excluded_services' => ['Alimentación', 'Hospedaje', 'Entrada a atractivos'],
        ]);
    }
}