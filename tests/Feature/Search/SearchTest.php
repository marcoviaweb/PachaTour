<?php

namespace Tests\Feature\Search;

use Tests\TestCase;
use App\Models\Attraction;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchTest extends TestCase
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

        // Crear atractivos
        Attraction::factory()->create([
            'name' => 'Salar de Uyuni',
            'department_id' => $lapaz->id,
            'type' => 'natural',
            'description' => 'El salar más grande del mundo',
            'is_active' => true
        ]);

        Attraction::factory()->create([
            'name' => 'Cristo de la Concordia',
            'department_id' => $cochabamba->id,
            'type' => 'cultural',
            'description' => 'Estatua religiosa en Cochabamba',
            'is_active' => true
        ]);

        Attraction::factory()->create([
            'name' => 'Laguna Verde',
            'department_id' => $lapaz->id,
            'type' => 'natural',
            'description' => 'Hermosa laguna de color verde',
            'is_active' => false
        ]);
    }

    /** @test */
    public function can_search_attractions_by_name()
    {
        $response = $this->getJson('/api/search?name=Salar');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'department',
                            'type'
                        ]
                    ],
                    'total',
                    'message'
                ])
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1)
                ->assertJsonPath('data.0.name', 'Salar de Uyuni');
    }

    /** @test */
    public function can_search_attractions_by_department()
    {
        $department = Department::where('name', 'La Paz')->first();
        
        $response = $this->getJson("/api/search?department_id={$department->id}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1); // Solo activos
    }

    /** @test */
    public function can_search_attractions_by_tourism_type()
    {
        $response = $this->getJson('/api/search?tourism_type=natural');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1); // Solo activos
    }

    /** @test */
    public function can_search_attractions_by_general_query()
    {
        $response = $this->getJson('/api/search?query=salar');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1)
                ->assertJsonPath('data.0.name', 'Salar de Uyuni');
    }

    /** @test */
    public function search_only_returns_active_attractions()
    {
        $response = $this->getJson('/api/search?tourism_type=natural');

        $response->assertStatus(200)
                ->assertJsonPath('total', 1); // Solo el Salar, no la Laguna Verde (inactiva)
        
        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertContains('Salar de Uyuni', $names);
        $this->assertNotContains('Laguna Verde', $names);
    }

    /** @test */
    public function can_get_search_suggestions()
    {
        $response = $this->getJson('/api/search/suggestions?term=sal');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ])
                ->assertJsonPath('success', true);
        
        $suggestions = $response->json('data');
        $this->assertContains('Salar de Uyuni', $suggestions);
    }

    /** @test */
    public function suggestions_require_minimum_term_length()
    {
        $response = $this->getJson('/api/search/suggestions?term=s');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data', []);
    }

    /** @test */
    public function can_perform_advanced_search()
    {
        $response = $this->getJson('/api/search/advanced?search=salar&sort_by=name&sort_order=asc');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'total',
                    'filters_applied',
                    'message'
                ])
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 1);
    }

    /** @test */
    public function can_get_tourism_types()
    {
        $response = $this->getJson('/api/search/tourism-types');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ])
                ->assertJsonPath('success', true);
        
        $types = $response->json('data');
        $this->assertContains('natural', $types);
        $this->assertContains('cultural', $types);
    }

    /** @test */
    public function can_search_by_location()
    {
        $response = $this->getJson('/api/search/location?latitude=-16.5&longitude=-68.15&radius=100');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'total',
                    'search_center' => [
                        'latitude',
                        'longitude',
                        'radius_km'
                    ],
                    'message'
                ])
                ->assertJsonPath('success', true);
    }

    /** @test */
    public function location_search_validates_coordinates()
    {
        $response = $this->getJson('/api/search/location?latitude=invalid&longitude=-68.15');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['latitude']);
    }

    /** @test */
    public function search_validates_input_parameters()
    {
        $response = $this->getJson('/api/search?department_id=999');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['department_id']);
    }

    /** @test */
    public function search_handles_empty_results()
    {
        $response = $this->getJson('/api/search?name=NonExistentAttraction');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('total', 0)
                ->assertJsonPath('data', []);
    }
}