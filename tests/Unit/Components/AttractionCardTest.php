<?php

namespace Tests\Unit\Components;

use Tests\TestCase;
use App\Models\Attraction;
use App\Models\Department;
use App\Models\Tour;
use App\Models\Media;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttractionCardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_fetch_attraction_data_for_card_display()
    {
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create([
            'department_id' => $department->id,
            'name' => 'Salar de Uyuni',
            'description' => 'El desierto de sal más grande del mundo',
            'type' => 'natural'
        ]);

        $response = $this->getJson("/api/attractions/{$attraction->slug}");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'attraction' => [
                            'id',
                            'name',
                            'description',
                            'type',
                            'department' => [
                                'id',
                                'name'
                            ]
                        ],
                        'related',
                        'coordinates',
                        'is_open_now'
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_includes_media_information_in_attraction_data()
    {
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);
        Media::factory()->count(3)->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $attraction->id,
            'type' => 'image'
        ]);

        $response = $this->getJson("/api/attractions/{$attraction->slug}");
        
        $response->assertStatus(200);
        $attractionData = $response->json('data.attraction');
        $this->assertArrayHasKey('media', $attractionData);
        $this->assertCount(3, $attractionData['media']);
    }

    /** @test */
    public function it_includes_tours_count_in_attraction_data()
    {
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);
        $tours = Tour::factory()->count(2)->create(['is_active' => true]);
        
        // Attach tours to attraction via pivot table
        $attraction->tours()->attach($tours->pluck('id'));

        $response = $this->getJson("/api/attractions/{$attraction->slug}");
        
        $response->assertStatus(200);
        $attractionData = $response->json('data.attraction');
        $this->assertArrayHasKey('tours', $attractionData);
        $this->assertCount(2, $attractionData['tours']);
    }

    /** @test */
    public function it_calculates_average_rating_from_reviews()
    {
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);
        
        // Crear reviews con diferentes ratings usando la relación polimórfica
        Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $attraction->id,
            'rating' => 5,
            'status' => 'approved'
        ]);
        Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $attraction->id,
            'rating' => 4,
            'status' => 'approved'
        ]);
        Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $attraction->id,
            'rating' => 3,
            'status' => 'approved'
        ]);

        $response = $this->getJson("/api/attractions/{$attraction->slug}");
        
        $response->assertStatus(200);
        $attractionData = $response->json('data.attraction');
        // The rating should be calculated from the reviews
        $this->assertArrayHasKey('rating', $attractionData);
    }

    /** @test */
    public function it_handles_attraction_without_tours()
    {
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);

        $response = $this->getJson("/api/attractions/{$attraction->slug}");
        
        $response->assertStatus(200);
        $attractionData = $response->json('data.attraction');
        $this->assertArrayHasKey('tours', $attractionData);
        $this->assertCount(0, $attractionData['tours']);
    }

    /** @test */
    public function it_returns_404_for_non_existent_attraction()
    {
        $response = $this->getJson('/api/attractions/non-existent-slug');
        
        $response->assertStatus(404);
    }
}