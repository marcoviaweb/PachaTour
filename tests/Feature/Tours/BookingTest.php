<?php

namespace Tests\Feature\Tours;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tour;
use App\Models\TourSchedule;
use App\Models\Booking;
use App\Models\Attraction;
use App\Models\Department;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class BookingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected User $admin;
    protected TourSchedule $schedule;
    protected Tour $tour;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->user = User::factory()->create(['role' => 'tourist']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create test data
        $this->tour = Tour::factory()->create();
        $this->schedule = TourSchedule::factory()->available()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->addDays(3)->toDateString(),
            'available_spots' => 10,
            'booked_spots' => 0,
        ]);
    }

    /** @test */
    public function authenticated_user_can_create_booking()
    {
        Sanctum::actingAs($this->user);

        $bookingData = [
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 2,
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com',
            'contact_phone' => '+591 70123456',
            'participant_details' => [
                [
                    'name' => 'Juan',
                    'last_name' => 'Pérez',
                    'document_type' => 'CI',
                    'document_number' => '12345678',
                    'birth_date' => '1990-01-01',
                    'nationality' => 'Boliviana'
                ],
                [
                    'name' => 'María',
                    'last_name' => 'García',
                    'document_type' => 'CI',
                    'document_number' => '87654321',
                    'birth_date' => '1992-05-15',
                    'nationality' => 'Boliviana'
                ]
            ],
            'special_requests' => ['Dieta vegetariana'],
            'notes' => 'Primera vez visitando Bolivia'
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Reserva creada exitosamente'
                ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 2,
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com',
            'status' => Booking::STATUS_PENDING
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_booking()
    {
        $bookingData = [
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 2,
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com'
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(401);
    }

    /** @test */
    public function cannot_create_booking_for_past_date()
    {
        Sanctum::actingAs($this->user);

        $pastSchedule = TourSchedule::factory()->available()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->subDays(1)->toDateString(),
            'available_spots' => 10,
            'booked_spots' => 0,
        ]);

        $bookingData = [
            'tour_schedule_id' => $pastSchedule->id,
            'participants_count' => 2,
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com'
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'error' => 'No se pueden hacer reservas para fechas pasadas'
                ]);
    }

    /** @test */
    public function cannot_create_booking_when_no_spots_available()
    {
        Sanctum::actingAs($this->user);

        // Fill all spots
        $this->schedule->update(['booked_spots' => 10]);

        $bookingData = [
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 2,
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com'
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(422)
                ->assertJsonFragment([
            'success' => false
        ]);
    }

    /** @test */
    public function user_can_view_their_bookings()
    {
        Sanctum::actingAs($this->user);

        // Create some bookings for the user
        $booking1 = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id
        ]);

        $booking2 = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id
        ]);

        // Create booking for another user (should not appear)
        $otherUser = User::factory()->create(['role' => 'tourist']);
        Booking::factory()->create([
            'user_id' => $otherUser->id,
            'tour_schedule_id' => $this->schedule->id
        ]);

        $response = $this->getJson('/api/bookings');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);

        $bookings = $response->json('data');
        $this->assertCount(2, $bookings);
    }

    /** @test */
    public function user_can_view_specific_booking()
    {
        Sanctum::actingAs($this->user);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id
        ]);

        $response = $this->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $booking->id,
                        'user_id' => $this->user->id
                    ]
                ]);
    }

    /** @test */
    public function user_cannot_view_other_users_booking()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create(['role' => 'tourist']);
        $booking = Booking::factory()->create([
            'user_id' => $otherUser->id,
            'tour_schedule_id' => $this->schedule->id
        ]);

        $response = $this->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'No tienes permisos para ver esta reserva'
                ]);
    }

    /** @test */
    public function admin_can_view_any_booking()
    {
        Sanctum::actingAs($this->admin);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id
        ]);

        $response = $this->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $booking->id
                    ]
                ]);
    }

    /** @test */
    public function user_can_update_their_booking()
    {
        Sanctum::actingAs($this->user);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PENDING,
            'participants_count' => 2
        ]);

        $updateData = [
            'participants_count' => 3,
            'contact_phone' => '+591 70987654',
            'notes' => 'Actualización de la reserva'
        ];

        $response = $this->putJson("/api/bookings/{$booking->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Reserva actualizada exitosamente'
                ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'participants_count' => 3,
            'contact_phone' => '+591 70987654',
            'notes' => 'Actualización de la reserva'
        ]);
    }

    /** @test */
    public function cannot_update_booking_close_to_tour_date()
    {
        Sanctum::actingAs($this->user);

        // Create schedule for tomorrow (less than 24 hours)
        $tomorrowSchedule = TourSchedule::factory()->available()->create([
            'tour_id' => $this->tour->id,
            'date' => now()->addHours(12)->toDateString(),
            'available_spots' => 10,
            'booked_spots' => 0,
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $tomorrowSchedule->id,
            'status' => Booking::STATUS_PENDING
        ]);

        $updateData = [
            'participants_count' => 3
        ];

        $response = $this->putJson("/api/bookings/{$booking->id}", $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['booking']);
    }

    /** @test */
    public function user_can_cancel_their_booking()
    {
        $user = User::factory()->create(['role' => 'tourist']);
        $tour = Tour::factory()->create();
        $schedule = TourSchedule::factory()->available()->create([
            'tour_id' => $tour->id,
            'date' => now()->addDays(3)->toDateString(),
            'available_spots' => 10,
            'booked_spots' => 0,
        ]);

        Sanctum::actingAs($user);

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'tour_schedule_id' => $schedule->id,
            'status' => Booking::STATUS_CONFIRMED
        ]);

        $cancelData = [
            'cancellation_reason' => 'Cambio de planes del cliente'
        ];

        $response = $this->patchJson("/api/bookings/{$booking->id}/cancel", $cancelData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Reserva cancelada exitosamente'
                ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => 'Cambio de planes del cliente'
        ]);
    }

    /** @test */
    public function can_confirm_booking()
    {
        Sanctum::actingAs($this->user);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PENDING,
            'participants_count' => 2
        ]);

        $response = $this->patchJson("/api/bookings/{$booking->id}/confirm");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Reserva confirmada exitosamente'
                ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => Booking::STATUS_CONFIRMED
        ]);

        // Check that spots were reserved
        $this->schedule->refresh();
        $this->assertEquals(2, $this->schedule->booked_spots);
    }

    /** @test */
    public function can_process_payment_for_booking()
    {
        Sanctum::actingAs($this->user);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_CONFIRMED,
            'payment_status' => Booking::PAYMENT_PENDING
        ]);

        $paymentData = [
            'payment_method' => 'Tarjeta de crédito',
            'payment_reference' => 'TXN123456789'
        ];

        $response = $this->patchJson("/api/bookings/{$booking->id}/payment", $paymentData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Pago procesado exitosamente'
                ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => Booking::STATUS_PAID,
            'payment_status' => Booking::PAYMENT_PAID,
            'payment_method' => 'Tarjeta de crédito',
            'payment_reference' => 'TXN123456789'
        ]);
    }

    /** @test */
    public function can_get_booking_summary()
    {
        Sanctum::actingAs($this->user);

        $summaryData = [
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 2
        ];

        $response = $this->getJson('/api/bookings/summary?' . http_build_query($summaryData));

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'tour' => ['id', 'name', 'duration'],
                        'schedule' => ['id', 'date', 'start_time', 'end_time', 'available_spots'],
                        'booking' => ['participants_count', 'price_per_person', 'total_amount', 'currency']
                    ]
                ]);
    }

    /** @test */
    public function admin_can_get_booking_statistics()
    {
        Sanctum::actingAs($this->admin);

        // Create some test bookings
        Booking::factory()->count(5)->create([
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PAID,
            'payment_status' => Booking::PAYMENT_PAID
        ]);

        Booking::factory()->count(3)->create([
            'tour_schedule_id' => $this->schedule->id,
            'status' => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_PENDING
        ]);

        $response = $this->getJson('/api/admin/bookings/statistics');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_bookings',
                        'total_revenue',
                        'total_commissions',
                        'status_counts',
                        'payment_status_counts',
                        'average_booking_value'
                    ]
                ]);
    }

    /** @test */
    public function validates_required_fields_for_booking_creation()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/bookings', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'tour_schedule_id',
                    'participants_count',
                    'contact_name',
                    'contact_email'
                ]);
    }

    /** @test */
    public function validates_participant_details_format()
    {
        Sanctum::actingAs($this->user);

        $bookingData = [
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 2,
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com',
            'participant_details' => [
                [
                    'name' => '', // Invalid: empty name
                    'last_name' => 'Pérez',
                    'document_type' => 'InvalidType', // Invalid: not in allowed list
                    'document_number' => '12345678',
                    'birth_date' => '2030-01-01' // Invalid: future date
                ]
            ]
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'participant_details.0.name',
                    'participant_details.0.document_type',
                    'participant_details.0.birth_date'
                ]);
    }

    /** @test */
    public function validates_participants_count_matches_details_count()
    {
        Sanctum::actingAs($this->user);

        $bookingData = [
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 3, // Says 3 participants
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com',
            'participant_details' => [ // But only provides 2 details
                [
                    'name' => 'Juan',
                    'last_name' => 'Pérez',
                    'document_type' => 'CI',
                    'document_number' => '12345678',
                    'birth_date' => '1990-01-01'
                ],
                [
                    'name' => 'María',
                    'last_name' => 'García',
                    'document_type' => 'CI',
                    'document_number' => '87654321',
                    'birth_date' => '1992-05-15'
                ]
            ]
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['participant_details']);
    }

    /** @test */
    public function requires_emergency_contact_for_large_groups()
    {
        Sanctum::actingAs($this->user);

        $bookingData = [
            'tour_schedule_id' => $this->schedule->id,
            'participants_count' => 6, // Large group
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com'
            // Missing emergency contact
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['emergency_contact_name']);
    }
}