<?php

namespace Tests\Unit\Search;

use Tests\TestCase;
use App\Features\Search\Services\FilterService;
use App\Models\Attraction;
use App\Models\Department;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilterServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FilterService $filterService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->filterService = new FilterService();
        $this->createTestData();
    }

    private function createTestData(): void
    {
        $lapaz = Department::factory()->create(['name' => 'La Paz']);
        $cochabamba = Department::factory()->create(['name' => 'Cochabamba']);

        $salar = Attraction::factory()->create([
            'name' => 'Salar de Uyuni',
            'department_id' => $lapaz->id,
            'type' => 'natural',
            'rating' => 4.8,
            'difficulty_level' => 'Fácil',
            'estimated_duration' => 480,
            'amenities' => ['Guía turístico', 'Transporte'],
            'is_featured' => true,
            'is_active' => true
        ]);

        $cristo = Attraction::factory()->create([
            'name' => 'Cristo de la Concordia',
            'department_id' => $cochabamba->id,
            'type' => 'cultural',
            'rating' => 4.2,
            'difficulty_level' => 'Moderado',
            'estimated_duration' => 120,
            'amenities' => ['Mirador'],
            'is_featured' => false,
            'is_active' => true
        ]);

        $tour1 = Tour::factory()->create([
            'price_per_person' => 150.00
        ]);

        $tour2 = Tour::factory()->create([
            'price_per_person' => 50.00
        ]);

        // Crear relaciones entre tours y atractivos
        $salar->tours()->attach($tour1->id);
        $cristo->tours()->attach($tour2->id);
    }

    /** @test */
    public function can_filter_by_search_text()
    {
        $results = $this->filterService->applyFilters(['search' => 'Salar']);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_filter_by_department()
    {
        $department = Department::where('name', 'La Paz')->first();
        $results = $this->filterService->applyFilters(['department_id' => $department->id]);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_filter_by_type()
    {
        $results = $this->filterService->applyFilters(['type' => 'natural']);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_filter_by_price_range()
    {
        $results = $this->filterService->applyFilters([
            'min_price' => 100,
            'max_price' => 200
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_filter_by_rating()
    {
        $results = $this->filterService->applyFilters(['min_rating' => 4.5]);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_filter_by_difficulty_level()
    {
        $results = $this->filterService->applyFilters(['difficulty_level' => 'Fácil']);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_filter_by_duration_range()
    {
        $results = $this->filterService->applyFilters([
            'min_duration' => 300,
            'max_duration' => 600
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_filter_by_amenities()
    {
        $results = $this->filterService->applyFilters(['amenities' => ['Guía turístico']]);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_filter_by_featured_status()
    {
        $results = $this->filterService->applyFilters(['is_featured' => true]);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_apply_multiple_filters()
    {
        $results = $this->filterService->applyFilters([
            'type' => 'natural',
            'min_rating' => 4.0,
            'is_featured' => true
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_sort_results_by_name()
    {
        $results = $this->filterService->applyFilters([
            'sort_by' => 'name',
            'sort_order' => 'asc'
        ]);

        $this->assertCount(2, $results);
        $this->assertEquals('Cristo de la Concordia', $results->first()->name);
    }

    /** @test */
    public function can_sort_results_by_rating()
    {
        $results = $this->filterService->applyFilters([
            'sort_by' => 'rating',
            'sort_order' => 'desc'
        ]);

        $this->assertCount(2, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_get_filter_options()
    {
        $options = $this->filterService->getFilterOptions();

        $this->assertArrayHasKey('types', $options);
        $this->assertArrayHasKey('difficulty_levels', $options);
        $this->assertArrayHasKey('amenities', $options);
        $this->assertArrayHasKey('price_range', $options);
        $this->assertArrayHasKey('rating_range', $options);
        $this->assertArrayHasKey('duration_range', $options);

        $this->assertContains('natural', $options['types']);
        $this->assertContains('cultural', $options['types']);
    }

    /** @test */
    public function can_apply_dynamic_filters()
    {
        $queryParams = [
            'q' => 'Salar',
            'type' => 'natural',
            'sort' => 'rating',
            'order' => 'desc'
        ];

        $results = $this->filterService->applyDynamicFilters($queryParams);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function returns_empty_collection_when_no_matches()
    {
        $results = $this->filterService->applyFilters(['type' => 'nonexistent']);

        $this->assertCount(0, $results);
    }

    /** @test */
    public function filters_only_active_attractions()
    {
        // Crear atractivo inactivo
        Attraction::factory()->create([
            'name' => 'Atractivo Inactivo',
            'type' => 'natural',
            'is_active' => false
        ]);

        $results = $this->filterService->applyFilters(['type' => 'natural']);

        $this->assertCount(1, $results); // Solo el Salar activo
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }

    /** @test */
    public function can_filter_by_location()
    {
        // Crear atractivo con coordenadas específicas
        Attraction::factory()->create([
            'name' => 'Atractivo Cercano',
            'latitude' => -16.5,
            'longitude' => -68.15,
            'is_active' => true
        ]);

        $results = $this->filterService->applyFilters([
            'latitude' => -16.5,
            'longitude' => -68.15,
            'radius' => 50
        ]);

        $this->assertGreaterThanOrEqual(1, $results->count());
    }

    /** @test */
    public function handles_empty_filters()
    {
        $results = $this->filterService->applyFilters([]);

        $this->assertCount(2, $results); // Todos los atractivos activos
    }

    /** @test */
    public function can_filter_by_multiple_amenities()
    {
        $results = $this->filterService->applyFilters([
            'amenities' => ['Guía turístico', 'Transporte']
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('Salar de Uyuni', $results->first()->name);
    }
}