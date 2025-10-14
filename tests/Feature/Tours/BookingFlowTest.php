<?php

namespace Tests\Feature\Tours;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Features\Tours\Models\Tour;
use App\Features\Tours\Models\TourSchedule;
use App\Features\Tours\Models\Booking;
use Laravel\Sanctum\Sanctum;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_booking_flow_works()
    {
        // Create fresh data for this test
        $user = User::factory()->create(['role' => 'tourist']);
        $tour = Tour::factory()->create(['price_per_person' => 150.00]);
        $schedule = TourSchedule::factory()->available()->create([
            'tour_id' => $tour->id,
            'date' => now()->addDays(3)->toDateString(),
            'available_spots' => 10,
            'booked_spots' => 0,
        ]);

        Sanctum::actingAs($user);

        // 1. Create booking
        $bookingData = [
            'tour_schedule_id' => $schedule->id,
            'participants_count' => 2,
            'contact_name' => 'Juan Pérez',
            'contact_email' => 'juan@example.com',
            'contact_phone' => '+591 70123456',
        ];

        $response = $this->postJson('/api/bookings', $bookingData);
        $response->assertStatus(201);
        
        $booking = Booking::where('user_id', $user->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals(2, $booking->participants_count);
        $this->assertEquals(300.00, $booking->total_amount);

        // 2. Update booking
        $updateData = [
            'participants_count' => 3,
            'notes' => 'Updated booking'
        ];

        $response = $this->putJson("/api/bookings/{$booking->id}", $updateData);
        $response->assertStatus(200);
        
        $booking->refresh();
        $this->assertEquals(3, $booking->participants_count);
        $this->assertEquals(450.00, $booking->total_amount);

        // 3. Confirm booking
        $response = $this->patchJson("/api/bookings/{$booking->id}/confirm");
        $response->assertStatus(200);
        
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);

        // 4. Process payment
        $paymentData = [
            'payment_method' => 'Tarjeta de crédito',
            'payment_reference' => 'TXN123456789'
        ];

        $response = $this->patchJson("/api/bookings/{$booking->id}/payment", $paymentData);
        $response->assertStatus(200);
        
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PAID, $booking->status);
        $this->assertEquals(Booking::PAYMENT_PAID, $booking->payment_status);

        // 5. Cancel booking (should fail because it's paid)
        $cancelData = [
            'cancellation_reason' => 'Cambio de planes'
        ];

        $response = $this->patchJson("/api/bookings/{$booking->id}/cancel", $cancelData);
        $response->assertStatus(422); // Should fail because booking is paid

        // 6. View booking
        $response = $this->getJson("/api/bookings/{$booking->id}");
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $booking->id,
                        'user_id' => $user->id,
                        'participants_count' => 3,
                        'total_amount' => '450.00'
                    ]
                ]);

        // 7. List user bookings
        $response = $this->getJson('/api/bookings');
        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }
}