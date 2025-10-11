<?php

namespace Tests\Feature\Search;

use Tests\TestCase;
use App\Models\Attraction;
use App\Models\Department;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear datos de prueba
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Crear departamentos
        $lapaz = Department::factory()->create([
            'name' => 'La Paz',
            'slug' => 'la-paz'
        ]);
        
        $cochabamba = Department::factory()->create([
            'name' => 'Cochabamba', 
            'slug' => 'cochabamba'
        ]);

        // Crear atractivos con diferentes características
        $salar = Attraction::factory()->create([
            'name' => 'Salar de Uyuni',
            'department_id' => $lapaz->id,
            'type' => 'natural',
            'rating' => 4.8,
            'difficulty_level' => 'Fácil',
            'estimated_duration' => 480, // 8 horas
            'amenities' => ['Guía turístico', 'Transporte', 'Comida'],
            'is_featured' => true,
            'is_active' => true
        ]);

        $cristo = Attraction::factory()->create([
            'name' => 'Cristo de la Concordia',
            'department_id' => $cochabamba->id,
            'type' => 'cultural',
            'rating' => 4.2,
            'difficulty_level' => 'Moderado',
            'estimated_duration' => 120, // 2 horas
            'amenities' => ['Mirador', 'Estacionamiento'],
            'is_featured' => false,
            'is_active' => true
        ]);

        // Crear tours con precios
        $tour1 = Tour::factory()->create([
            'price_per_person' => 150.00,
            'is_active' => true
        ]);

        $tour2 = Tour::factory()->create([
            'price_per_person' => 50.00,
            'is_active' => true
        ]);

        // Crear relaciones entre tours y atractivos
        $salar->tours()->attach($tour1->id);
        $cristo->tours()->attach($tour2->id);
    }

    /** @test */
    public function can_filter_attractions_by_price_range()
    {
        $response = $this->getJson('/api/filters/price?min_price=100&max_price=200');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'total',
                    'price_range',
                    'message'
                ])
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1); // Solo el Salar
    }

    /** @test */
    public function can_filter_attractions_by_rating()
    {
        $response = $this->getJson('/api/filters/rating?min_rating=4.5');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1); // Solo el Salar con rating 4.8
    }

    /** @test */
    public function can_filter_attractions_by_difficulty()
    {
        $response = $this->getJson('/api/filters/difficulty?difficulty_level=Fácil');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1); // Solo el Salar
    }

    /** @test */
    public function can_filter_attractions_by_amenities()
    {
        $response = $this->getJson('/api/filters/amenities?amenities[]=Guía turístico');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1); // Solo el Salar
    }

    /** @test */
    public function can_apply_multiple_filters()
    {
        $response = $this->getJson('/api/filters?type=natural&min_rating=4.0&is_featured=1');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'total',
                    'filters_applied',
                    'message'
                ])
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1); // Solo el Salar
    }

    /** @test */
    public function can_get_filter_options()
    {
        $response = $this->getJson('/api/filters/options');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'types',
                        'difficulty_levels',
                        'amenities',
                        'price_range',
                        'rating_range',
                        'duration_range'
                    ],
                    'message'
                ])
                ->assertJsonPath('success', true);
    }

    /** @test */
    public function can_use_dynamic_filters()
    {
        $response = $this->getJson('/api/filters/dynamic?q=salar&type=natural&sort=rating&order=desc');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'total',
                    'query_params',
                    'message'
                ])
                ->assertJsonPath('success', true);
    }

    /** @test */
    public function can_filter_by_distance()
    {
        $response = $this->getJson('/api/filters/distance?latitude=-16.5&longitude=-68.15&radius=100');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'total',
                    'location' => [
                        'latitude',
                        'longitude',
                        'radius_km'
                    ],
                    'message'
                ])
                ->assertJsonPath('success', true);
    }

    /** @test */
    public function validates_filter_parameters()
    {
        $response = $this->getJson('/api/filters/price?min_price=invalid');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['min_price']);
    }

    /** @test */
    public function validates_price_range_logic()
    {
        $response = $this->getJson('/api/filters/price?min_price=200&max_price=100');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['max_price']);
    }

    /** @test */
    public function validates_rating_range()
    {
        $response = $this->getJson('/api/filters/rating?min_rating=6');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['min_rating']);
    }

    /** @test */
    public function validates_coordinates_for_distance_filter()
    {
        $response = $this->getJson('/api/filters/distance?latitude=invalid&longitude=-68.15');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['latitude']);
    }

    /** @test */
    public function filters_return_empty_results_when_no_matches()
    {
        $response = $this->getJson('/api/filters?search=nonexistent');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 0)
                ->assertJsonPath('data', []);
    }

    /** @test */
    public function can_sort_filtered_results()
    {
        $response = $this->getJson('/api/filters?sort_by=rating&sort_order=desc');

        $response->assertStatus(200)
                ->assertJsonPath('success', true);
        
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual($data[1]['rating'], $data[0]['rating']);
    }

    /** @test */
    public function can_filter_by_department()
    {
        $department = Department::where('name', 'La Paz')->first();
        
        $response = $this->getJson("/api/filters?department_id={$department->id}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1); // Solo el Salar
    }

    /** @test */
    public function can_filter_by_duration_range()
    {
        $response = $this->getJson('/api/filters?min_duration=300&max_duration=600');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1); // Solo el Salar (480 min)
    }
}