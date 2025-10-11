<?php

namespace Tests\Feature\Reviews;

use Tests\TestCase;
use App\Models\User;
use App\Models\Review;
use App\Models\Attraction;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_reviews_endpoint()
    {
        $response = $this->getJson('/api/reviews');
        
        if ($response->status() !== 200) {
            dump('Response status: ' . $response->status());
            dump('Response content: ' . $response->content());
        }
        
        // Should return 200 even with no reviews
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta'
            ]);
    }

    public function test_can_create_review_with_minimal_data()
    {
        $user = User::factory()->create(['role' => 'tourist']);
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);

        $this->actingAs($user, 'sanctum');

        $reviewData = [
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $attraction->id,
            'rating' => 4.5,
            'title' => 'Great place to visit',
            'comment' => 'I really enjoyed my time here. The scenery was beautiful and the staff was friendly.'
        ];

        $response = $this->postJson('/api/reviews', $reviewData);

        if ($response->status() !== 201) {
            dump('Response status: ' . $response->status());
            dump('Response content: ' . $response->content());
        }

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }
}