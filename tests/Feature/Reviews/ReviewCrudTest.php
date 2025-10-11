<?php

namespace Tests\Feature\Reviews;

use Tests\TestCase;
use App\Models\User;
use App\Models\Review;
use App\Models\Attraction;
use App\Models\Booking;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ReviewCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Attraction $attraction;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'tourist']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        $department = Department::factory()->create();
        $this->attraction = Attraction::factory()->create(['department_id' => $department->id]);
        
        // Create tour and schedule for booking
        $tour = \App\Models\Tour::factory()->create();
        $tour->attractions()->attach($this->attraction->id); // Many-to-many relationship
        $schedule = \App\Models\TourSchedule::factory()->create(['tour_id' => $tour->id]);
        
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $schedule->id,
            'status' => 'completed'
        ]);
    }

    public function test_can_list_approved_reviews()
    {
        // Create approved and pending reviews
        Review::factory()->count(3)->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'status' => Review::STATUS_APPROVED
        ]);

        Review::factory()->count(2)->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'status' => Review::STATUS_PENDING
        ]);

        $response = $this->getJson('/api/reviews?' . http_build_query([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'rating',
                            'title',
                            'comment',
                            'status',
                            'user' => ['id', 'name'],
                            'created_at'
                        ]
                    ]
                ],
                'meta' => [
                    'total_reviews',
                    'average_rating',
                    'rating_distribution'
                ]
            ]);

        // Should only return approved reviews
        $this->assertEquals(3, count($response->json('data.data')));
    }

    public function test_can_create_review_when_authenticated()
    {
        Sanctum::actingAs($this->user);

        $reviewData = [
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'booking_id' => $this->booking->id,
            'rating' => 4.5,
            'title' => 'Excelente experiencia',
            'comment' => 'Me encantó este lugar, muy recomendable para familias.',
            'detailed_ratings' => [
                'service' => 5,
                'value' => 4,
                'location' => 5
            ],
            'pros' => ['Hermoso paisaje', 'Buen servicio'],
            'cons' => ['Un poco caro'],
            'would_recommend' => true,
            'visit_date' => '2024-01-15',
            'travel_type' => Review::TRAVEL_FAMILY
        ];

        $response = $this->postJson('/api/reviews', $reviewData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'rating',
                    'title',
                    'comment',
                    'status',
                    'user'
                ]
            ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 4.5,
            'title' => 'Excelente experiencia',
            'status' => Review::STATUS_PENDING
        ]);
    }

    public function test_cannot_create_review_when_not_authenticated()
    {
        $reviewData = [
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 4.5,
            'title' => 'Excelente experiencia',
            'comment' => 'Me encantó este lugar, muy recomendable para familias.'
        ];

        $response = $this->postJson('/api/reviews', $reviewData);

        $response->assertStatus(401);
    }

    public function test_validates_required_fields_when_creating_review()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/reviews', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'reviewable_type',
                'reviewable_id',
                'rating',
                'title',
                'comment'
            ]);
    }

    public function test_validates_rating_range()
    {
        Sanctum::actingAs($this->user);

        // Test rating too low
        $response = $this->postJson('/api/reviews', [
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 0,
            'title' => 'Test',
            'comment' => 'Test comment'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);

        // Test rating too high
        $response = $this->postJson('/api/reviews', [
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 6,
            'title' => 'Test',
            'comment' => 'Test comment'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_validates_comment_length()
    {
        Sanctum::actingAs($this->user);

        // Test comment too short
        $response = $this->postJson('/api/reviews', [
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 4,
            'title' => 'Test title',
            'comment' => 'Short'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);

        // Test comment too long
        $response = $this->postJson('/api/reviews', [
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 4,
            'title' => 'Test title',
            'comment' => str_repeat('a', 2001)
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_can_show_approved_review()
    {
        $review = Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'status' => Review::STATUS_APPROVED
        ]);

        $response = $this->getJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'rating',
                    'title',
                    'comment',
                    'status',
                    'user',
                    'reviewable',
                    'booking'
                ]
            ]);
    }

    public function test_cannot_show_pending_review_to_non_owner()
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'status' => Review::STATUS_PENDING
        ]);

        $response = $this->getJson("/api/reviews/{$review->id}");

        $response->assertStatus(404);
    }

    public function test_can_show_own_pending_review()
    {
        Sanctum::actingAs($this->user);

        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'status' => Review::STATUS_PENDING
        ]);

        $response = $this->getJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
    }

    public function test_can_update_own_review_when_editable()
    {
        Sanctum::actingAs($this->user);

        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'status' => Review::STATUS_PENDING
        ]);

        $updateData = [
            'rating' => 3.5,
            'title' => 'Updated title',
            'comment' => 'Updated comment with more details about the experience.',
            'would_recommend' => false
        ];

        $response = $this->putJson("/api/reviews/{$review->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3.5,
            'title' => 'Updated title',
            'status' => Review::STATUS_PENDING // Should reset to pending after edit
        ]);
    }

    public function test_cannot_update_review_of_other_user()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'status' => Review::STATUS_PENDING
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'rating' => 3.5,
            'title' => 'Updated title',
            'comment' => 'Updated comment'
        ]);

        $response->assertStatus(403);
    }

    public function test_can_delete_own_review()
    {
        Sanctum::actingAs($this->user);

        $review = Review::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Reseña eliminada exitosamente'
            ]);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id
        ]);
    }

    public function test_admin_can_delete_any_review()
    {
        Sanctum::actingAs($this->admin);

        $review = Review::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_can_get_own_reviews()
    {
        Sanctum::actingAs($this->user);

        // Create reviews for this user
        Review::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
        ]);
        
        // Create reviews for other users
        Review::factory()->count(2)->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
        ]);

        $response = $this->getJson('/api/reviews/my-reviews');

        // Debug the response
        if ($response->status() !== 200) {
            dump('Response status: ' . $response->status());
            dump('Response content: ' . $response->content());
        }

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'rating',
                            'title',
                            'status',
                            'reviewable',
                            'booking'
                        ]
                    ]
                ]
            ]);

        // Should only return user's own reviews
        $this->assertEquals(3, count($response->json('data.data')));
    }

    public function test_can_vote_review_as_helpful()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'status' => Review::STATUS_APPROVED,
            'helpful_votes' => 0
        ]);

        $response = $this->postJson("/api/reviews/{$review->id}/vote-helpful");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Voto registrado'
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'helpful_votes' => 1
        ]);
    }

    public function test_cannot_vote_own_review()
    {
        Sanctum::actingAs($this->user);

        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'status' => Review::STATUS_APPROVED
        ]);

        $response = $this->postJson("/api/reviews/{$review->id}/vote-helpful");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No puedes votar tu propia reseña'
            ]);
    }

    public function test_can_filter_reviews_by_rating()
    {
        // Create reviews with different ratings
        Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 5,
            'status' => Review::STATUS_APPROVED
        ]);

        Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 3,
            'status' => Review::STATUS_APPROVED
        ]);

        $response = $this->getJson('/api/reviews?' . http_build_query([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'min_rating' => 4
        ]));

        $response->assertStatus(200);
        
        $reviews = $response->json('data.data');
        $this->assertEquals(1, count($reviews));
        $this->assertEquals(5, $reviews[0]['rating']);
    }

    public function test_can_search_reviews_by_content()
    {
        Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'title' => 'Excelente lugar',
            'comment' => 'Muy recomendable para familias',
            'status' => Review::STATUS_APPROVED
        ]);

        Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'title' => 'Lugar regular',
            'comment' => 'No me gustó mucho',
            'status' => Review::STATUS_APPROVED
        ]);

        $response = $this->getJson('/api/reviews?' . http_build_query([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'search' => 'excelente'
        ]));

        $response->assertStatus(200);
        
        $reviews = $response->json('data.data');
        $this->assertEquals(1, count($reviews));
        $this->assertStringContainsString('Excelente', $reviews[0]['title']);
    }

    public function test_calculates_average_rating_correctly()
    {
        // Create reviews with known ratings
        Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 5,
            'status' => Review::STATUS_APPROVED
        ]);

        Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 3,
            'status' => Review::STATUS_APPROVED
        ]);

        $response = $this->getJson('/api/reviews?' . http_build_query([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id
        ]));

        $response->assertStatus(200);
        
        $averageRating = $response->json('meta.average_rating');
        $this->assertEquals(4.0, $averageRating);
    }
}