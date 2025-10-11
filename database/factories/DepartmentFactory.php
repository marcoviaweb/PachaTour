<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Test La Paz',
            'Test Santa Cruz', 
            'Test Cochabamba',
            'Test Potosí',
            'Test Oruro',
            'Test Chuquisaca',
            'Test Tarija',
            'Test Beni',
            'Test Pando'
        ]);
        
        $slug = Str::slug($name);

        return [
            'name' => $name,
            'slug' => $slug,
            'capital' => $this->faker->city(),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(10),
            'latitude' => $this->faker->latitude(-22, -9), // Bolivia's latitude range
            'longitude' => $this->faker->longitude(-69, -57), // Bolivia's longitude range
            'population' => $this->faker->numberBetween(100000, 3000000),
            'area_km2' => $this->faker->randomFloat(2, 10000, 400000),
            'climate' => $this->faker->randomElement([
                'Tropical húmedo',
                'Templado de valle',
                'Altiplánico frío y seco',
                'Subtropical'
            ]),
            'languages' => $this->faker->randomElements([
                'Español', 'Quechua', 'Aymara', 'Guaraní'
            ], $this->faker->numberBetween(1, 3)),
            'gallery' => [
                'images/placeholder.jpg',
                'images/placeholder.jpg',
                'images/placeholder.jpg'
            ],
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 9),
        ];
    }

    /**
     * Indicate that the department is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the department is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}