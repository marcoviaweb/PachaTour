<?php

namespace Tests\Unit\Components;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Review;
use App\Models\UserFavorite;
use App\Models\Attraction;
use App\Models\Department;
use App\Models\Tour;
use App\Models\TourSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->department = Department::factory()->create();
        $this->attraction = Attraction::factory()->create([
            'department_id' => $this->department->id
        ]);
        $this->tour = Tour::factory()->create();
        
        // Create the many-to-many relationship between tour and attraction
        $this->tour->attractions()->attach($this->attraction->id);
        
        $this->tourSchedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->addDays(7)->toDateString()
        ]);
        
        $this->user = User::factory()->create([
            'role' => 'tourist',
            'is_active' => true
        ]);
    }

    /** @test */
    public function it_returns_dashboard_stats_for_authenticated_user()
    {
        Sanctum::actingAs($this->user);

        // Create test bookings
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->tourSchedule->id,
            'status' => 'confirmed'
        ]);

        Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->tourSchedule->id,
            'status' => 'completed'
        ]);

        // Create test review
        Review::factory()->forAttraction($this->attraction)->create([
            'user_id' => $this->user->id,
            'status' => 'approved'
        ]);

        $response = $this->getJson('/api/user/dashboard/stats');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'activeBookings',
                    'completedBookings',
                    'reviewsCount',
                    'visitedDestinations'
                ])
                ->assertJson([
                    'activeBookings' => 1,
                    'completedBookings' => 1,
                    'reviewsCount' => 1,
                    'visitedDestinations' => 1
                ]);
    }

    /** @test */
    public function it_returns_upcoming_bookings_for_authenticated_user()
    {
        Sanctum::actingAs($this->user);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->tourSchedule->id,
            'status' => 'confirmed'
        ]);

        $response = $this->getJson('/api/user/bookings/upcoming');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'booking_number',
                            'status',
                            'status_name',
                            'tour_name',
                            'tour_date',
                            'tour_time',
                            'participants_count',
                            'total_amount',
                            'currency',
                            'attraction_name',
                            'department_name'
                        ]
                    ]
                ]);

        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function it_returns_booking_history_for_authenticated_user()
    {
        Sanctum::actingAs($this->user);

        $pastSchedule = TourSchedule::factory()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->subDays(7)->toDateString()
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $pastSchedule->id,
            'status' => 'completed'
        ]);

        $response = $this->getJson('/api/user/bookings/history');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'booking_number',
                            'status',
                            'tour_name',
                            'tour_date',
                            'has_review',
                            'attraction_name'
                        ]
                    ]
                ]);

        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function it_returns_user_reviews()
    {
        Sanctum::actingAs($this->user);

        $review = Review::factory()->forAttraction($this->attraction)->create([
            'user_id' => $this->user->id,
            'status' => 'approved'
        ]);

        $response = $this->getJson('/api/user/reviews');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'rating',
                            'title',
                            'comment',
                            'status',
                            'attraction_name',
                            'department_name'
                        ]
                    ]
                ]);

        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function it_returns_user_favorites()
    {
        Sanctum::actingAs($this->user);

        $favorite = UserFavorite::create([
            'user_id' => $this->user->id,
            'attraction_id' => $this->attraction->id
        ]);

        $response = $this->getJson('/api/user/favorites');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'recommendations'
                ]);
    }

    /** @test */
    public function it_can_add_attraction_to_favorites()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/user/favorites', [
            'attraction_id' => $this->attraction->id
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Attraction added to favorites'
                ]);

        $this->assertDatabaseHas('user_favorites', [
            'user_id' => $this->user->id,
            'attraction_id' => $this->attraction->id
        ]);
    }

    /** @test */
    public function it_can_remove_attraction_from_favorites()
    {
        Sanctum::actingAs($this->user);

        $favorite = UserFavorite::create([
            'user_id' => $this->user->id,
            'attraction_id' => $this->attraction->id
        ]);

        $response = $this->deleteJson("/api/user/favorites/{$favorite->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Attraction removed from favorites'
                ]);

        $this->assertDatabaseMissing('user_favorites', [
            'id' => $favorite->id
        ]);
    }

    /** @test */
    public function it_returns_user_profile()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'nationality',
                        'preferred_language'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_user_profile()
    {
        Sanctum::actingAs($this->user);

        $updateData = [
            'name' => 'Updated Name',
            'last_name' => 'Updated Last Name',
            'phone' => '+591 12345678',
            'nationality' => 'Bolivian',
            'preferred_language' => 'es'
        ];

        $response = $this->putJson('/api/user/profile', $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Profile updated successfully'
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'last_name' => 'Updated Last Name'
        ]);
    }

    /** @test */
    public function it_can_change_user_password()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/user/change-password', [
            'current_password' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Password changed successfully'
                ]);
    }

    /** @test */
    public function it_validates_current_password_when_changing_password()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/user/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['current_password']);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_dashboard_endpoints()
    {
        $endpoints = [
            '/api/user/dashboard/stats',
            '/api/user/bookings/upcoming',
            '/api/user/bookings/history',
            '/api/user/reviews',
            '/api/user/favorites',
            '/api/user/profile'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function it_validates_required_fields_when_adding_favorite()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/user/favorites', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['attraction_id']);
    }

    /** @test */
    public function it_validates_profile_update_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/user/profile', [
            'name' => '', // Required field
            'email' => 'invalid-email', // Invalid email format
            'birth_date' => 'invalid-date' // Invalid date
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }
}