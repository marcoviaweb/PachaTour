<?php

namespace Tests\Unit\Search;

use Tests\TestCase;
use App\Features\Search\Services\SearchService;
use App\Models\Attraction;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SearchService $searchService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->searchService = new SearchService();
        $this->createTestData();
    }

    private function createTestData(): void
    {
        $lapaz = Department::factory()->create(['name' => 'La Paz']);
        $cochabamba = Department::factory()->create(['name' => 'Cochabamba']);

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
            'description' => 'Estatua religiosa',
            'is_active' => true
        ]);

        Attraction::factory()->create([
            'name' => 'Laguna Verde',
            'department_id' => $lapaz->id,
            'type' => 'natural',
            'is_active' => false
        ]);
    }

    /** @test */
    public function can_search_attractions_by_name()
    {
        $results = $this->searchService->searchAttractions(['name' => 'Salar']);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_search_attractions_by_department()
    {
        $department = Department::where('name', 'La Paz')->first();
        $results = $this->searchService->searchAttractions(['department_id' => $department->id]);

        $this->assertCount(1, $results); // Solo activos
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_search_attractions_by_tourism_type()
    {
        $results = $this->searchService->searchAttractions(['tourism_type' => 'natural']);

        $this->assertCount(1, $results); // Solo activos
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_search_attractions_by_general_query()
    {
        $results = $this->searchService->searchAttractions(['query' => 'salar']);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function search_only_returns_active_attractions()
    {
        $results = $this->searchService->searchAttractions(['tourism_type' => 'natural']);

        $this->assertCount(1, $results);
        $names = $results->pluck('name')->toArray();
        $this->assertContains('Salar de Uyuni', $names);
        $this->assertNotContains('Laguna Verde', $names);
    }

    /** @test */
    public function can_get_suggestions()
    {
        $suggestions = $this->searchService->getSuggestions('sal');

        $this->assertIsArray($suggestions);
        $this->assertContains('Salar de Uyuni', $suggestions);
    }

    /** @test */
    public function suggestions_include_departments()
    {
        $suggestions = $this->searchService->getSuggestions('La');

        $this->assertIsArray($suggestions);
        $this->assertContains('La Paz', $suggestions);
    }

    /** @test */
    public function suggestions_respect_limit()
    {
        $suggestions = $this->searchService->getSuggestions('a', 1);

        $this->assertCount(1, $suggestions);
    }

    /** @test */
    public function can_perform_advanced_search()
    {
        $filters = [
            'search' => 'salar',
            'sort_by' => 'name',
            'sort_order' => 'asc'
        ];

        $results = $this->searchService->advancedSearch($filters);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function advanced_search_can_sort_by_name()
    {
        $filters = [
            'sort_by' => 'name',
            'sort_order' => 'desc'
        ];

        $results = $this->searchService->advancedSearch($filters);

        $this->assertCount(2, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_get_tourism_types()
    {
        $types = $this->searchService->getTourismTypes();

        $this->assertIsArray($types);
        $this->assertContains('natural', $types);
        $this->assertContains('cultural', $types);
    }

    /** @test */
    public function can_search_by_location()
    {
        // Crear atractivo con coordenadas específicas
        Attraction::factory()->create([
            'name' => 'Test Location',
            'latitude' => -16.5,
            'longitude' => -68.15, // La Paz aproximadamente
            'is_active' => true
        ]);

        $results = $this->searchService->searchByLocation(-16.5, -68.15, 100);

        $this->assertGreaterThanOrEqual(1, $results->count());
    }

    /** @test */
    public function location_search_respects_radius()
    {
        // Crear atractivo con coordenadas específicas
        Attraction::factory()->create([
            'name' => 'Far Location',
            'latitude' => -10.0,
            'longitude' => -60.0, // Muy lejos
            'is_active' => true
        ]);

        $results = $this->searchService->searchByLocation(-16.5, -68.15, 10); // Radio pequeño

        // No debería encontrar el atractivo lejano
        $names = $results->pluck('name')->toArray();
        $this->assertNotContains('Far Location', $names);
    }

    /** @test */
    public function search_handles_empty_criteria()
    {
        $results = $this->searchService->searchAttractions([]);

        $this->assertCount(2, $results); // Solo activos
    }

    /** @test */
    public function search_handles_multiple_criteria()
    {
        $department = Department::where('name', 'La Paz')->first();
        
        $results = $this->searchService->searchAttractions([
            'name' => 'Salar',
            'department_id' => $department->id,
            'tourism_type' => 'natural'
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }
}