<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attraction;
use App\Models\Department;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attraction>
 */
class AttractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseNames = [
            'Laguna', 'Mirador', 'Cascada', 'Cueva', 'Termas', 'Bosque',
            'Cerro', 'Río', 'Valle', 'Puente', 'Géiser', 'Santuario',
            'Sendero', 'Parque', 'Reserva', 'Montaña', 'Quebrada'
        ];
        
        $descriptors = [
            'Colorada', 'del Cóndor', 'del Arco Iris', 'de los Murciélagos', 'de Aguas Calientes',
            'Encantado', 'de los Siete Colores', 'Cristalino', 'de las Vicuñas', 'Natural',
            'del Amanecer', 'de Orquídeas', 'de las Estrellas', 'de los Incas', 'Esmeralda',
            'Sagrado', 'Misterioso', 'Dorado', 'de la Luna', 'del Sol', 'Perdido', 'Secreto'
        ];
        
        $name = $this->faker->randomElement($baseNames) . ' ' . $this->faker->randomElement($descriptors) . ' ' . $this->faker->numberBetween(1, 999);

        $types = array_keys(Attraction::TYPES);
        $difficulties = ['Fácil', 'Moderado', 'Difícil'];
        $seasons = ['Todo el año', 'Época seca (Mayo-Octubre)', 'Época húmeda (Noviembre-Abril)'];

        return [
            'department_id' => Department::inRandomOrder()->first()?->id ?? 1,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(12),
            'type' => $this->faker->randomElement($types),
            'latitude' => $this->faker->latitude(-22, -9), // Rango de Bolivia
            'longitude' => $this->faker->longitude(-69, -57), // Rango de Bolivia
            'address' => $this->faker->optional()->streetAddress(),
            'city' => $this->faker->city(),
            'entry_price' => $this->faker->optional(0.7)->randomFloat(2, 0, 200),
            'currency' => 'BOB',
            'opening_hours' => [
                'monday' => ['open' => '08:00', 'close' => '17:00'],
                'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                'thursday' => ['open' => '08:00', 'close' => '17:00'],
                'friday' => ['open' => '08:00', 'close' => '17:00'],
                'saturday' => ['open' => '08:00', 'close' => '18:00'],
                'sunday' => ['open' => '09:00', 'close' => '16:00'],
            ],
            'contact_info' => [
                'phone' => $this->faker->optional()->phoneNumber(),
                'email' => $this->faker->optional()->email(),
                'website' => $this->faker->optional()->url(),
            ],
            'difficulty_level' => $this->faker->randomElement($difficulties),
            'estimated_duration' => $this->faker->numberBetween(30, 480), // 30 min a 8 horas
            'amenities' => $this->faker->randomElements([
                'Estacionamiento', 'Baños', 'Restaurante', 'Tienda de souvenirs',
                'Guía turístico', 'Senderos señalizados', 'Área de picnic',
                'Mirador', 'Centro de interpretación', 'WiFi'
            ], $this->faker->numberBetween(2, 6)),
            'restrictions' => $this->faker->optional()->randomElements([
                'No permitido para menores de 12 años',
                'Requiere condición física buena',
                'No accesible para personas con movilidad reducida',
                'Prohibido fumar',
                'No se permiten mascotas'
            ], $this->faker->numberBetween(0, 3)),
            'best_season' => $this->faker->randomElement($seasons),
            'rating' => $this->faker->randomFloat(2, 3.0, 5.0),
            'reviews_count' => $this->faker->numberBetween(0, 150),
            'visits_count' => $this->faker->numberBetween(0, 5000),
            'is_featured' => $this->faker->boolean(20), // 20% de probabilidad
            'is_active' => $this->faker->boolean(95), // 95% activos
        ];
    }

    /**
     * Indicate that the attraction is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'rating' => $this->faker->randomFloat(2, 4.0, 5.0),
            'reviews_count' => $this->faker->numberBetween(50, 300),
        ]);
    }

    /**
     * Indicate that the attraction is natural type.
     */
    public function natural(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'natural',
            'amenities' => ['Senderos señalizados', 'Mirador', 'Área de picnic'],
        ]);
    }

    /**
     * Indicate that the attraction is cultural type.
     */
    public function cultural(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cultural',
            'amenities' => ['Guía turístico', 'Centro de interpretación', 'Tienda de souvenirs'],
        ]);
    }

    /**
     * Indicate that the attraction is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'entry_price' => null,
        ]);
    }

    /**
     * Indicate that the attraction is expensive.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'entry_price' => $this->faker->randomFloat(2, 100, 500),
        ]);
    }
}
