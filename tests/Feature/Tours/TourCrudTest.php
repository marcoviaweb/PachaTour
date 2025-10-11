<?php

namespace Tests\Feature\Tours;

use Tests\TestCase;
use App\Models\Tour;
use App\Models\User;
use App\Models\Attraction;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class TourCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'tourist']);
    }

    /** @test */
    public function admin_can_view_tours_list()
    {
        Sanctum::actingAs($this->adminUser);
        
        Tour::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/tours');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'slug',
                                'type',
                                'price_per_person',
                                'is_active',
                                'is_featured'
                            ]
                        ]
                    ],
                    'meta'
                ]);
    }

    /** @test */
    public function non_admin_cannot_access_admin_tours()
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/admin/tours');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_tour()
    {
        Sanctum::actingAs($this->adminUser);
        
        $department = Department::factory()->create();
        $attractions = Attraction::factory()->count(2)->create([
            'department_id' => $department->id
        ]);

        $tourData = [
            'name' => 'Tour Salar de Uyuni',
            'description' => 'Un increíble tour de 3 días por el Salar de Uyuni, el desierto de sal más grande del mundo. Incluye visitas a islas de cactus, flamencos rosados y paisajes únicos.',
            'short_description' => 'Tour de 3 días por el Salar de Uyuni',
            'type' => 'nature',
            'duration_days' => 3,
            'duration_hours' => null,
            'price_per_person' => 450.00,
            'currency' => 'BOB',
            'min_participants' => 2,
            'max_participants' => 8,
            'difficulty_level' => 'moderate',
            'included_services' => [
                'Transporte 4x4',
                'Guía especializado',
                'Alojamiento 2 noches',
                'Todas las comidas'
            ],
            'excluded_services' => [
                'Bebidas alcohólicas',
                'Propinas',
                'Gastos personales'
            ],
            'requirements' => [
                'Buena condición física',
                'Ropa de abrigo'
            ],
            'what_to_bring' => [
                'Ropa abrigada',
                'Lentes de sol',
                'Protector solar',
                'Cámara fotográfica'
            ],
            'meeting_point' => 'Plaza Principal de Uyuni',
            'departure_time' => '07:00',
            'return_time' => '18:00',
            'itinerary' => [
                [
                    'day' => 1,
                    'title' => 'Día 1: Cementerio de Trenes e Isla Incahuasi',
                    'description' => 'Visita al cementerio de trenes y la isla de cactus',
                    'activities' => ['Cementerio de trenes', 'Isla Incahuasi', 'Atardecer en el salar']
                ],
                [
                    'day' => 2,
                    'title' => 'Día 2: Lagunas de Colores',
                    'description' => 'Exploración de las lagunas coloradas',
                    'activities' => ['Laguna Colorada', 'Flamencos', 'Géiseres']
                ],
                [
                    'day' => 3,
                    'title' => 'Día 3: Amanecer y Regreso',
                    'description' => 'Amanecer en el salar y regreso',
                    'activities' => ['Amanecer', 'Desayuno', 'Regreso a Uyuni']
                ]
            ],
            'guide_language' => 'es',
            'available_languages' => ['es', 'en'],
            'is_featured' => true,
            'is_active' => true,
            'attractions' => [
                [
                    'id' => $attractions[0]->id,
                    'visit_order' => 1,
                    'duration_minutes' => 120,
                    'notes' => 'Primera parada del tour',
                    'is_optional' => false,
                    'arrival_time' => '09:00',
                    'departure_time' => '11:00'
                ],
                [
                    'id' => $attractions[1]->id,
                    'visit_order' => 2,
                    'duration_minutes' => 180,
                    'notes' => 'Parada principal',
                    'is_optional' => false,
                    'arrival_time' => '12:00',
                    'departure_time' => '15:00'
                ]
            ]
        ];

        $response = $this->postJson('/api/admin/tours', $tourData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'attractions'
                    ]
                ]);

        $this->assertDatabaseHas('tours', [
            'name' => 'Tour Salar de Uyuni',
            'type' => 'nature',
            'duration_days' => 3,
            'price_per_person' => 450.00
        ]);

        // Verify attractions were attached
        $tour = Tour::where('name', 'Tour Salar de Uyuni')->first();
        $this->assertCount(2, $tour->attractions);
    }

    /** @test */
    public function tour_creation_validates_required_fields()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/tours', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'name',
                    'description',
                    'type',
                    'duration_days',
                    'price_per_person',
                    'min_participants',
                    'max_participants',
                    'difficulty_level',
                    'meeting_point'
                ]);
    }

    /** @test */
    public function tour_creation_validates_field_formats()
    {
        Sanctum::actingAs($this->adminUser);

        $invalidData = [
            'name' => '', // Empty name
            'description' => 'Too short', // Less than 50 characters
            'type' => 'invalid_type', // Invalid type
            'duration_days' => 0, // Invalid duration
            'price_per_person' => -10, // Negative price
            'min_participants' => 0, // Invalid minimum
            'max_participants' => 2, // Less than minimum
            'difficulty_level' => 'invalid_difficulty',
            'meeting_point' => '',
            'departure_time' => '25:00', // Invalid time format
            'return_time' => '06:00', // Before departure time
            'currency' => 'EUR' // Invalid currency
        ];

        $response = $this->postJson('/api/admin/tours', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'name',
                    'description',
                    'type',
                    'duration_days',
                    'price_per_person',
                    'min_participants',
                    'difficulty_level',
                    'meeting_point',
                    'departure_time',
                    'return_time',
                    'currency'
                ]);
    }

    /** @test */
    public function admin_can_view_specific_tour()
    {
        Sanctum::actingAs($this->adminUser);
        
        $tour = Tour::factory()->create();

        $response = $this->getJson("/api/admin/tours/{$tour->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'type',
                        'attractions',
                        'schedules',
                        'media'
                    ]
                ]);
    }

    /** @test */
    public function admin_can_update_tour()
    {
        Sanctum::actingAs($this->adminUser);
        
        $tour = Tour::factory()->create([
            'name' => 'Tour Original',
            'price_per_person' => 100.00
        ]);

        $updateData = [
            'name' => 'Tour Actualizado',
            'price_per_person' => 150.00,
            'description' => 'Descripción actualizada del tour con más de cincuenta caracteres para cumplir con la validación mínima requerida.'
        ];

        $response = $this->putJson("/api/admin/tours/{$tour->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        $this->assertDatabaseHas('tours', [
            'id' => $tour->id,
            'name' => 'Tour Actualizado',
            'price_per_person' => 150.00
        ]);
    }

    /** @test */
    public function admin_can_delete_tour_without_bookings()
    {
        Sanctum::actingAs($this->adminUser);
        
        $tour = Tour::factory()->create();

        $response = $this->deleteJson("/api/admin/tours/{$tour->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Tour eliminado exitosamente'
                ]);

        $this->assertDatabaseMissing('tours', ['id' => $tour->id]);
    }

    /** @test */
    public function admin_can_toggle_tour_status()
    {
        Sanctum::actingAs($this->adminUser);
        
        $tour = Tour::factory()->create(['is_active' => true]);

        $response = $this->patchJson("/api/admin/tours/{$tour->id}/toggle-status");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => ['is_active' => false]
                ]);

        $this->assertDatabaseHas('tours', [
            'id' => $tour->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function admin_can_toggle_featured_status()
    {
        Sanctum::actingAs($this->adminUser);
        
        $tour = Tour::factory()->create(['is_featured' => false]);

        $response = $this->patchJson("/api/admin/tours/{$tour->id}/toggle-featured");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => ['is_featured' => true]
                ]);

        $this->assertDatabaseHas('tours', [
            'id' => $tour->id,
            'is_featured' => true
        ]);
    }

    /** @test */
    public function admin_can_get_tour_statistics()
    {
        Sanctum::actingAs($this->adminUser);
        
        Tour::factory()->count(5)->create(['is_active' => true]);
        Tour::factory()->count(2)->create(['is_active' => false]);
        Tour::factory()->count(3)->create(['is_featured' => true]);

        $response = $this->getJson('/api/admin/tours/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_tours',
                        'active_tours',
                        'featured_tours',
                        'tours_by_type',
                        'tours_by_difficulty',
                        'average_price',
                        'price_range'
                    ]
                ]);
    }

    /** @test */
    public function admin_can_duplicate_tour()
    {
        Sanctum::actingAs($this->adminUser);
        
        $originalTour = Tour::factory()->create(['name' => 'Tour Original']);

        $response = $this->postJson("/api/admin/tours/{$originalTour->id}/duplicate");

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        $this->assertDatabaseHas('tours', [
            'name' => 'Tour Original (Copia)',
            'is_active' => false,
            'is_featured' => false
        ]);
    }

    /** @test */
    public function tour_list_supports_filtering()
    {
        Sanctum::actingAs($this->adminUser);
        
        Tour::factory()->create(['type' => 'nature', 'is_active' => true]);
        Tour::factory()->create(['type' => 'cultural', 'is_active' => false]);
        Tour::factory()->create(['type' => 'nature', 'is_featured' => true]);

        // Filter by type
        $response = $this->getJson('/api/admin/tours?type=nature');
        $response->assertStatus(200);
        
        // Filter by status
        $response = $this->getJson('/api/admin/tours?status=active');
        $response->assertStatus(200);
        
        // Filter by featured
        $response = $this->getJson('/api/admin/tours?featured=1');
        $response->assertStatus(200);
    }

    /** @test */
    public function tour_list_supports_search()
    {
        Sanctum::actingAs($this->adminUser);
        
        Tour::factory()->create(['name' => 'Salar de Uyuni Adventure']);
        Tour::factory()->create(['name' => 'Lake Titicaca Tour']);
        Tour::factory()->create(['description' => 'Amazing Uyuni salt flats experience']);

        $response = $this->getJson('/api/admin/tours?search=Uyuni');

        $response->assertStatus(200);
        // Should return tours that match "Uyuni" in name or description
    }

    /** @test */
    public function tour_list_supports_sorting()
    {
        Sanctum::actingAs($this->adminUser);
        
        Tour::factory()->create(['name' => 'A Tour', 'price_per_person' => 100]);
        Tour::factory()->create(['name' => 'B Tour', 'price_per_person' => 200]);
        Tour::factory()->create(['name' => 'C Tour', 'price_per_person' => 50]);

        // Sort by name
        $response = $this->getJson('/api/admin/tours?sort_by=name&sort_direction=asc');
        $response->assertStatus(200);
        
        // Sort by price
        $response = $this->getJson('/api/admin/tours?sort_by=price_per_person&sort_direction=desc');
        $response->assertStatus(200);
    }
}