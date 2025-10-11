<?php

namespace Tests\Feature\Attractions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attraction;
use App\Models\Department;
use App\Models\Media;

class AttractionApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->department = Department::factory()->create();
    }

    /** @test */
    public function public_can_get_active_attractions_list()
    {
        // Create active and inactive attractions
        Attraction::factory()->count(3)->create([
            'department_id' => $this->department->id,
            'is_active' => true
        ]);
        
        Attraction::factory()->count(2)->create([
            'department_id' => $this->department->id,
            'is_active' => false
        ]);

        $response = $this->getJson('/api/attractions');

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
                            'media'
                        ]
                    ],
                    'current_page',
                    'total'
                ],
                'filters' => [
                    'types',
                    'departments'
                ],
                'message'
            ])
            ->assertJsonCount(3, 'data.data'); // Only active attractions
    }

    /** @test */
    public function public_can_get_attraction_by_slug()
    {
        $attraction = Attraction::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
            'slug' => 'test-attraction'
        ]);

        $response = $this->getJson("/api/attractions/{$attraction->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'attraction' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'department',
                        'media',
                        'tours'
                    ],
                    'related',
                    'coordinates',
                    'is_open_now'
                ],
                'message'
            ])
            ->assertJsonPath('data.attraction.slug', 'test-attraction');
    }

    /** @test */
    public function public_cannot_get_inactive_attraction()
    {
        $attraction = Attraction::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => false,
            'slug' => 'inactive-attraction'
        ]);

        $response = $this->getJson("/api/attractions/{$attraction->slug}");

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function public_can_search_attractions()
    {
        Attraction::factory()->create([
            'department_id' => $this->department->id,
            'name' => 'Salar de Uyuni',
            'is_active' => true
        ]);
        
        Attraction::factory()->create([
            'department_id' => $this->department->id,
            'name' => 'Laguna Colorada',
            'is_active' => true
        ]);

        $response = $this->getJson('/api/attractions/search?q=Salar');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'attractions',
                    'departments'
                ],
                'message'
            ]);

        // Should find the Salar attraction
        $attractions = $response->json('data.attractions');
        $this->assertGreaterThanOrEqual(1, count($attractions));
        
        // Check if we found the right attraction
        $found = false;
        foreach ($attractions as $attraction) {
            if ($attraction['name'] === 'Salar de Uyuni') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Salar de Uyuni attraction should be found in search results');
    }

    /** @test */
    public function search_requires_minimum_query_length()
    {
        $response = $this->getJson('/api/attractions/search?q=S');

        $response->assertStatus(200)
            ->assertJsonPath('data', [])
            ->assertJsonPath('message', 'Query too short');
    }

    /** @test */
    public function public_can_get_featured_attractions()
    {
        // Create featured and non-featured attractions
        Attraction::factory()->count(3)->create([
            'department_id' => $this->department->id,
            'is_active' => true,
            'is_featured' => true,
            'rating' => 4.5
        ]);
        
        Attraction::factory()->count(2)->create([
            'department_id' => $this->department->id,
            'is_active' => true,
            'is_featured' => false
        ]);

        $response = $this->getJson('/api/attractions/featured');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'is_featured',
                        'department'
                    ]
                ],
                'message'
            ])
            ->assertJsonCount(3, 'data');

        // All returned attractions should be featured
        foreach ($response->json('data') as $attraction) {
            $this->assertTrue($attraction['is_featured']);
        }
    }

    /** @test */
    public function public_can_filter_attractions_by_department()
    {
        $department2 = Department::factory()->create();
        
        Attraction::factory()->count(2)->create([
            'department_id' => $this->department->id,
            'is_active' => true
        ]);
        
        Attraction::factory()->count(3)->create([
            'department_id' => $department2->id,
            'is_active' => true
        ]);

        $response = $this->getJson("/api/attractions?department={$this->department->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');

        // All returned attractions should belong to the specified department
        foreach ($response->json('data.data') as $attraction) {
            $this->assertEquals($this->department->id, $attraction['department']['id']);
        }
    }

    /** @test */
    public function public_can_filter_attractions_by_type()
    {
        Attraction::factory()->count(2)->create([
            'department_id' => $this->department->id,
            'type' => 'natural',
            'is_active' => true
        ]);
        
        Attraction::factory()->count(3)->create([
            'department_id' => $this->department->id,
            'type' => 'cultural',
            'is_active' => true
        ]);

        $response = $this->getJson('/api/attractions?type=natural');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');

        // All returned attractions should be of natural type
        foreach ($response->json('data.data') as $attraction) {
            $this->assertEquals('natural', $attraction['type']);
        }
    }

    /** @test */
    public function public_can_filter_attractions_by_price_range()
    {
        Attraction::factory()->create([
            'department_id' => $this->department->id,
            'entry_price' => 50.00,
            'is_active' => true
        ]);
        
        Attraction::factory()->create([
            'department_id' => $this->department->id,
            'entry_price' => 150.00,
            'is_active' => true
        ]);
        
        Attraction::factory()->create([
            'department_id' => $this->department->id,
            'entry_price' => 250.00,
            'is_active' => true
        ]);

        $response = $this->getJson('/api/attractions?min_price=100&max_price=200');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        $attraction = $response->json('data.data.0');
        $this->assertEquals(150.00, $attraction['entry_price']);
    }

    /** @test */
    public function public_can_get_attractions_by_department_slug()
    {
        $department = Department::factory()->create(['slug' => 'la-paz']);
        
        Attraction::factory()->count(3)->create([
            'department_id' => $department->id,
            'is_active' => true
        ]);

        $response = $this->getJson('/api/attractions/department/la-paz');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'department' => [
                        'id',
                        'name',
                        'slug'
                    ],
                    'attractions' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'media'
                            ]
                        ]
                    ]
                ],
                'message'
            ])
            ->assertJsonCount(3, 'data.attractions.data');
    }

    /** @test */
    public function public_can_get_attraction_types_with_counts()
    {
        Attraction::factory()->count(2)->create([
            'department_id' => $this->department->id,
            'type' => 'natural',
            'is_active' => true
        ]);
        
        Attraction::factory()->count(3)->create([
            'department_id' => $this->department->id,
            'type' => 'cultural',
            'is_active' => true
        ]);

        $response = $this->getJson('/api/attractions/types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'natural' => [
                        'name',
                        'count',
                        'slug'
                    ],
                    'cultural' => [
                        'name',
                        'count',
                        'slug'
                    ]
                ],
                'message'
            ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['natural']['count']);
        $this->assertEquals(3, $data['cultural']['count']);
    }

    /** @test */
    public function attraction_visit_count_increments_on_view()
    {
        $attraction = Attraction::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
            'visits_count' => 5
        ]);

        $response = $this->getJson("/api/attractions/{$attraction->slug}");

        $response->assertStatus(200);

        // Check that visit count was incremented
        $attraction->refresh();
        $this->assertEquals(6, $attraction->visits_count);
    }

    /** @test */
    public function attractions_can_be_sorted_by_different_criteria()
    {
        Attraction::factory()->create([
            'department_id' => $this->department->id,
            'name' => 'A First Attraction',
            'rating' => 3.0,
            'entry_price' => 200.00,
            'is_active' => true
        ]);
        
        Attraction::factory()->create([
            'department_id' => $this->department->id,
            'name' => 'Z Last Attraction',
            'rating' => 5.0,
            'entry_price' => 100.00,
            'is_active' => true
        ]);

        // Test sorting by name
        $response = $this->getJson('/api/attractions?sort_by=name&sort_direction=asc');
        $response->assertStatus(200);
        $attractions = $response->json('data.data');
        $this->assertEquals('A First Attraction', $attractions[0]['name']);

        // Test sorting by rating
        $response = $this->getJson('/api/attractions?sort_by=rating&sort_direction=desc');
        $response->assertStatus(200);
        $attractions = $response->json('data.data');
        $this->assertEquals(5.0, $attractions[0]['rating']);

        // Test sorting by price
        $response = $this->getJson('/api/attractions?sort_by=price&sort_direction=asc');
        $response->assertStatus(200);
        $attractions = $response->json('data.data');
        $this->assertEquals(100.00, $attractions[0]['entry_price']);
    }
}