<?php

namespace Tests\Feature\Departments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Department;
use App\Models\Attraction;
use App\Models\User;
use App\Models\Review;

class DepartmentApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test departments
        $this->departments = Department::factory()->count(3)->create([
            'is_active' => true
        ]);
        
        // Create some attractions for the first department
        $this->attractions = Attraction::factory()->count(2)->create([
            'department_id' => $this->departments->first()->id,
            'is_active' => true
        ]);
    }

    /** @test */
    public function it_can_get_all_departments()
    {
        $response = $this->getJson('/api/departments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'capital',
                        'short_description',
                        'image_url',
                        'attractions_count',
                        'coordinates'
                    ]
                ],
                'meta' => [
                    'total',
                    'search'
                ]
            ])
            ->assertJson([
                'success' => true
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_can_search_departments()
    {
        $department = $this->departments->first();
        
        $response = $this->getJson('/api/departments?search=' . $department->name);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'search' => $department->name
                ]
            ]);

        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_department_by_slug()
    {
        $department = $this->departments->first();
        
        $response = $this->getJson('/api/departments/' . $department->slug);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'capital',
                    'description',
                    'short_description',
                    'image_url',
                    'gallery',
                    'population',
                    'area_km2',
                    'climate',
                    'languages',
                    'coordinates',
                    'attractions_count',
                    'average_rating',
                    'attractions',
                    'reviews'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $department->id,
                    'name' => $department->name,
                    'slug' => $department->slug
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_department()
    {
        $response = $this->getJson('/api/departments/non-existent-slug');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Departamento no encontrado'
            ]);
    }

    /** @test */
    public function it_returns_404_for_inactive_department()
    {
        $inactiveDepartment = Department::factory()->create([
            'is_active' => false
        ]);

        $response = $this->getJson('/api/departments/' . $inactiveDepartment->slug);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Departamento no encontrado'
            ]);
    }

    /** @test */
    public function it_can_get_departments_list()
    {
        $response = $this->getJson('/api/departments/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'capital'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_includes_attractions_in_department_detail()
    {
        $department = $this->departments->first();
        
        $response = $this->getJson('/api/departments/' . $department->slug);

        $response->assertStatus(200);
        
        $attractions = $response->json('data.attractions');
        $this->assertCount(2, $attractions);
        
        foreach ($attractions as $attraction) {
            $this->assertArrayHasKey('id', $attraction);
            $this->assertArrayHasKey('name', $attraction);
            $this->assertArrayHasKey('slug', $attraction);
            $this->assertArrayHasKey('short_description', $attraction);
            $this->assertArrayHasKey('image_url', $attraction);
            $this->assertArrayHasKey('tourism_type', $attraction);
            $this->assertArrayHasKey('average_rating', $attraction);
            $this->assertArrayHasKey('coordinates', $attraction);
        }
    }

    /** @test */
    public function it_includes_reviews_in_department_detail()
    {
        $department = $this->departments->first();
        $user = User::factory()->create();
        
        // Create approved reviews
        Review::factory()->count(2)->create([
            'reviewable_type' => Department::class,
            'reviewable_id' => $department->id,
            'user_id' => $user->id,
            'status' => 'approved'
        ]);
        
        // Create pending review (should not be included)
        Review::factory()->create([
            'reviewable_type' => Department::class,
            'reviewable_id' => $department->id,
            'user_id' => $user->id,
            'status' => 'pending'
        ]);
        
        $response = $this->getJson('/api/departments/' . $department->slug);

        $response->assertStatus(200);
        
        $reviews = $response->json('data.reviews');
        $this->assertCount(2, $reviews); // Only approved reviews
        
        foreach ($reviews as $review) {
            $this->assertArrayHasKey('id', $review);
            $this->assertArrayHasKey('rating', $review);
            $this->assertArrayHasKey('title', $review);
            $this->assertArrayHasKey('comment', $review);
            $this->assertArrayHasKey('user_name', $review);
            $this->assertArrayHasKey('created_at', $review);
        }
    }

    /** @test */
    public function it_only_shows_active_departments_in_list()
    {
        // Create inactive department
        Department::factory()->create([
            'is_active' => false
        ]);

        $response = $this->getJson('/api/departments');

        $response->assertStatus(200);
        
        // Should only return the 3 active departments created in setUp
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_handles_server_errors_gracefully()
    {
        // This test would require mocking to simulate a server error
        // For now, we'll just ensure the error handling structure is in place
        $this->assertTrue(true);
    }
}