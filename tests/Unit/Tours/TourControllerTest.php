<?php

namespace Tests\Unit\Tours;

use Tests\TestCase;
use App\Models\Tour;
use App\Models\User;
use App\Models\Attraction;
use App\Features\Tours\Controllers\TourController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class TourControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->controller = new TourController();
    }

    /** @test */
    public function it_can_calculate_tour_statistics()
    {
        // Clear existing tours first
        Tour::query()->delete();
        
        // Create test data
        $activeTours = Tour::factory()->count(5)->create(['is_active' => true, 'is_featured' => false]);
        $inactiveTours = Tour::factory()->count(2)->create(['is_active' => false, 'is_featured' => false]);
        $featuredTours = Tour::factory()->count(3)->create(['is_featured' => true]);

        $this->actingAs($this->adminUser);
        
        $response = $this->controller->statistics();
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        
        // Debug output
        $totalCount = Tour::count();
        $activeCount = Tour::where('is_active', true)->count();
        $featuredCount = Tour::where('is_featured', true)->count();
        
        $this->assertEquals($totalCount, $data['data']['total_tours']);
        $this->assertEquals($activeCount, $data['data']['active_tours']);
        $this->assertEquals($featuredCount, $data['data']['featured_tours']);
        $this->assertArrayHasKey('tours_by_type', $data['data']);
        $this->assertArrayHasKey('average_price', $data['data']);
    }

    /** @test */
    public function it_validates_tour_capacity_constraints()
    {
        $tour = Tour::factory()->create([
            'min_participants' => 2,
            'max_participants' => 10
        ]);

        // Test minimum participants validation
        $this->assertTrue($tour->min_participants >= 1);
        $this->assertTrue($tour->max_participants >= $tour->min_participants);
        $this->assertTrue($tour->max_participants <= 100);
    }

    /** @test */
    public function it_handles_tour_type_validation()
    {
        $validTypes = array_keys(Tour::TYPES);
        
        foreach ($validTypes as $type) {
            $tour = Tour::factory()->create(['type' => $type]);
            $this->assertEquals($type, $tour->type);
            $this->assertArrayHasKey($type, Tour::TYPES);
        }
    }

    /** @test */
    public function it_validates_difficulty_levels()
    {
        $validDifficulties = array_keys(Tour::DIFFICULTIES);
        
        foreach ($validDifficulties as $difficulty) {
            $tour = Tour::factory()->create(['difficulty_level' => $difficulty]);
            $this->assertEquals($difficulty, $tour->difficulty_level);
            $this->assertArrayHasKey($difficulty, Tour::DIFFICULTIES);
        }
    }

    /** @test */
    public function it_calculates_formatted_duration_correctly()
    {
        // Multi-day tour
        $multiDayTour = Tour::factory()->create([
            'duration_days' => 3,
            'duration_hours' => null
        ]);
        $this->assertEquals('3 días', $multiDayTour->formatted_duration);

        // Single day with hours
        $singleDayTour = Tour::factory()->create([
            'duration_days' => 1,
            'duration_hours' => 6
        ]);
        $this->assertEquals('6 horas', $singleDayTour->formatted_duration);

        // Single day without hours specified
        $defaultTour = Tour::factory()->create([
            'duration_days' => 1,
            'duration_hours' => null
        ]);
        $this->assertEquals('1 día', $defaultTour->formatted_duration);
    }

    /** @test */
    public function it_formats_price_correctly()
    {
        $tour = Tour::factory()->create([
            'price_per_person' => 150.50,
            'currency' => 'BOB'
        ]);

        $this->assertEquals('150.50 BOB', $tour->formatted_price);
    }

    /** @test */
    public function it_validates_itinerary_structure()
    {
        $validItinerary = [
            [
                'day' => 1,
                'title' => 'Día 1: Llegada',
                'description' => 'Llegada y check-in',
                'activities' => ['Check-in', 'Orientación']
            ],
            [
                'day' => 2,
                'title' => 'Día 2: Exploración',
                'description' => 'Tour por la ciudad',
                'activities' => ['Desayuno', 'City tour', 'Almuerzo']
            ]
        ];

        $tour = Tour::factory()->create([
            'duration_days' => 2,
            'itinerary' => $validItinerary
        ]);

        $this->assertIsArray($tour->itinerary);
        $this->assertCount(2, $tour->itinerary);
        $this->assertEquals(1, $tour->itinerary[0]['day']);
        $this->assertEquals(2, $tour->itinerary[1]['day']);
    }

    /** @test */
    public function it_handles_language_settings_correctly()
    {
        $tour = Tour::factory()->create([
            'guide_language' => 'es',
            'available_languages' => ['es', 'en', 'qu']
        ]);

        $this->assertEquals('es', $tour->guide_language);
        $this->assertContains('es', $tour->available_languages);
        $this->assertContains('en', $tour->available_languages);
        $this->assertContains('qu', $tour->available_languages);
    }

    /** @test */
    public function it_validates_service_arrays()
    {
        $includedServices = [
            'Transporte',
            'Guía profesional',
            'Almuerzo',
            'Entrada a sitios'
        ];

        $excludedServices = [
            'Bebidas alcohólicas',
            'Propinas',
            'Gastos personales'
        ];

        $tour = Tour::factory()->create([
            'included_services' => $includedServices,
            'excluded_services' => $excludedServices
        ]);

        $this->assertIsArray($tour->included_services);
        $this->assertIsArray($tour->excluded_services);
        $this->assertCount(4, $tour->included_services);
        $this->assertCount(3, $tour->excluded_services);
        $this->assertContains('Transporte', $tour->included_services);
        $this->assertContains('Propinas', $tour->excluded_services);
    }

    /** @test */
    public function it_validates_requirements_and_what_to_bring()
    {
        $requirements = [
            'Edad mínima 18 años',
            'Buena condición física',
            'No apto para embarazadas'
        ];

        $whatToBring = [
            'Ropa cómoda',
            'Zapatos de trekking',
            'Protector solar',
            'Agua'
        ];

        $tour = Tour::factory()->create([
            'requirements' => $requirements,
            'what_to_bring' => $whatToBring
        ]);

        $this->assertIsArray($tour->requirements);
        $this->assertIsArray($tour->what_to_bring);
        $this->assertCount(3, $tour->requirements);
        $this->assertCount(4, $tour->what_to_bring);
    }

    /** @test */
    public function it_validates_time_format()
    {
        $tour = Tour::factory()->create([
            'departure_time' => '08:00',
            'return_time' => '18:00'
        ]);

        $this->assertNotNull($tour->departure_time);
        $this->assertNotNull($tour->return_time);
        
        // Verify time format
        $this->assertEquals('08:00', $tour->departure_time->format('H:i'));
        $this->assertEquals('18:00', $tour->return_time->format('H:i'));
    }

    /** @test */
    public function it_validates_availability_dates()
    {
        $tour = Tour::factory()->create([
            'available_from' => now()->addDays(1)->toDateString(),
            'available_until' => now()->addMonths(6)->toDateString()
        ]);

        $this->assertTrue($tour->available_from->isFuture());
        $this->assertTrue($tour->available_until->isAfter($tour->available_from));
    }

    /** @test */
    public function it_handles_boolean_flags_correctly()
    {
        $tour = Tour::factory()->create([
            'is_featured' => true,
            'is_active' => false
        ]);

        $this->assertTrue($tour->is_featured);
        $this->assertFalse($tour->is_active);
        $this->assertIsBool($tour->is_featured);
        $this->assertIsBool($tour->is_active);
    }
}