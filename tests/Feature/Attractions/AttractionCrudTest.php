<?php

namespace Tests\Feature\Attractions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attraction;
use App\Models\Department;
use Laravel\Sanctum\Sanctum;

class AttractionCrudTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $adminUser;
    protected User $regularUser;
    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'tourist']);
        $this->department = Department::factory()->create();
    }

    /** @test */
    public function admin_can_get_attractions_list()
    {
        Sanctum::actingAs($this->adminUser);
        
        Attraction::factory()->count(5)->create(['department_id' => $this->department->id]);

        $response = $this->getJson('/api/admin/attractions');

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
                            'department',
                            'is_active',
                            'is_featured'
                        ]
                    ],
                    'current_page',
                    'total'
                ],
                'message'
            ]);
    }

    /** @test */
    public function admin_can_create_attraction()
    {
        Sanctum::actingAs($this->adminUser);

        $attractionData = [
            'department_id' => $this->department->id,
            'name' => 'Salar de Uyuni',
            'description' => 'El Salar de Uyuni es el mayor desierto de sal continuo y alto del mundo, con una superficie de 10 582 km². Está situado a unos 3650 msnm en el suroeste de Bolivia.',
            'short_description' => 'El mayor desierto de sal del mundo',
            'type' => 'natural',
            'latitude' => -20.1338,
            'longitude' => -67.4891,
            'city' => 'Uyuni',
            'entry_price' => 150.00,
            'currency' => 'BOB',
            'difficulty_level' => 'moderate',
            'estimated_duration' => 480, // 8 hours
            'is_featured' => true,
            'is_active' => true
        ];

        $response = $this->postJson('/api/admin/attractions', $attractionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'department_id',
                    'type'
                ],
                'message'
            ]);

        $this->assertDatabaseHas('attractions', [
            'name' => 'Salar de Uyuni',
            'slug' => 'salar-de-uyuni',
            'department_id' => $this->department->id
        ]);
    }

    /** @test */
    public function admin_can_update_attraction()
    {
        Sanctum::actingAs($this->adminUser);
        
        $attraction = Attraction::factory()->create(['department_id' => $this->department->id]);

        $updateData = [
            'name' => 'Updated Attraction Name',
            'description' => 'This is an updated description that meets the minimum length requirement for attraction descriptions.',
            'entry_price' => 200.00
        ];

        $response = $this->putJson("/api/admin/attractions/{$attraction->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Attraction Name')
            ->assertJsonPath('data.entry_price', '200.00');

        $this->assertDatabaseHas('attractions', [
            'id' => $attraction->id,
            'name' => 'Updated Attraction Name',
            'entry_price' => 200.00
        ]);
    }

    /** @test */
    public function admin_can_delete_attraction_without_active_bookings()
    {
        Sanctum::actingAs($this->adminUser);
        
        $attraction = Attraction::factory()->create(['department_id' => $this->department->id]);

        $response = $this->deleteJson("/api/admin/attractions/{$attraction->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('attractions', ['id' => $attraction->id]);
    }

    /** @test */
    public function admin_can_toggle_attraction_status()
    {
        Sanctum::actingAs($this->adminUser);
        
        $attraction = Attraction::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true
        ]);

        $response = $this->patchJson("/api/admin/attractions/{$attraction->id}/toggle-status");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('attractions', [
            'id' => $attraction->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function admin_can_toggle_featured_status()
    {
        Sanctum::actingAs($this->adminUser);
        
        $attraction = Attraction::factory()->create([
            'department_id' => $this->department->id,
            'is_featured' => false
        ]);

        $response = $this->patchJson("/api/admin/attractions/{$attraction->id}/toggle-featured");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_featured', true);

        $this->assertDatabaseHas('attractions', [
            'id' => $attraction->id,
            'is_featured' => true
        ]);
    }

    /** @test */
    public function regular_user_cannot_access_admin_routes()
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/admin/attractions');
        $response->assertStatus(403);

        $response = $this->postJson('/api/admin/attractions', []);
        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_admin_routes()
    {
        $response = $this->getJson('/api/admin/attractions');
        $response->assertStatus(401);

        $response = $this->postJson('/api/admin/attractions', []);
        $response->assertStatus(401);
    }

    /** @test */
    public function create_attraction_validates_required_fields()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/attractions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'department_id',
                'name',
                'description',
                'type',
                'latitude',
                'longitude',
                'city'
            ]);
    }

    /** @test */
    public function create_attraction_validates_field_formats()
    {
        Sanctum::actingAs($this->adminUser);

        $invalidData = [
            'department_id' => 'invalid',
            'name' => 'A', // Too short
            'description' => 'Short', // Too short
            'type' => 'invalid_type',
            'latitude' => 100, // Out of range
            'longitude' => 200, // Out of range
            'city' => 'A', // Too short
            'entry_price' => -10, // Negative
            'currency' => 'INVALID', // Invalid currency
            'estimated_duration' => 5 // Too short
        ];

        $response = $this->postJson('/api/admin/attractions', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'department_id',
                'name',
                'description',
                'type',
                'latitude',
                'longitude',
                'city',
                'entry_price',
                'currency',
                'estimated_duration'
            ]);
    }

    /** @test */
    public function create_attraction_validates_bolivia_coordinates()
    {
        Sanctum::actingAs($this->adminUser);

        $attractionData = [
            'department_id' => $this->department->id,
            'name' => 'Test Attraction',
            'description' => 'This is a test description that meets the minimum length requirement for attraction descriptions.',
            'type' => 'natural',
            'latitude' => 40.7128, // New York coordinates (outside Bolivia)
            'longitude' => -74.0060,
            'city' => 'Test City'
        ];

        $response = $this->postJson('/api/admin/attractions', $attractionData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['coordinates']);
    }

    /** @test */
    public function admin_can_get_attraction_statistics()
    {
        Sanctum::actingAs($this->adminUser);

        // Create attractions with different types and statuses
        Attraction::factory()->create([
            'department_id' => $this->department->id,
            'type' => 'natural',
            'is_active' => true,
            'is_featured' => true
        ]);
        
        Attraction::factory()->create([
            'department_id' => $this->department->id,
            'type' => 'cultural',
            'is_active' => false,
            'is_featured' => false
        ]);

        $response = $this->getJson('/api/admin/attractions/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'active',
                    'inactive',
                    'featured',
                    'by_type',
                    'by_department',
                    'top_rated',
                    'most_visited'
                ],
                'message'
            ]);
    }

    /** @test */
    public function attraction_slug_is_automatically_generated_and_unique()
    {
        Sanctum::actingAs($this->adminUser);

        // Create first attraction
        $attractionData1 = [
            'department_id' => $this->department->id,
            'name' => 'Test Attraction',
            'description' => 'This is a test description that meets the minimum length requirement for attraction descriptions.',
            'type' => 'natural',
            'latitude' => -20.1338,
            'longitude' => -67.4891,
            'city' => 'Test City'
        ];

        $response1 = $this->postJson('/api/admin/attractions', $attractionData1);
        $response1->assertStatus(201);

        // Create second attraction with same name
        $attractionData2 = $attractionData1;
        $response2 = $this->postJson('/api/admin/attractions', $attractionData2);
        $response2->assertStatus(201);

        // Check that slugs are different
        $attraction1 = Attraction::where('name', 'Test Attraction')->first();
        $attraction2 = Attraction::where('name', 'Test Attraction')->skip(1)->first();

        $this->assertEquals('test-attraction', $attraction1->slug);
        $this->assertEquals('test-attraction-1', $attraction2->slug);
    }
}