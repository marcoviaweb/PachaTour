<?php

namespace Tests\Feature\Payments;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\Attraction;
use App\Models\Department;
use App\Features\Payments\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'tourist']);
        
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);
        $tour = Tour::factory()->create(['attraction_id' => $attraction->id]);
        
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_id' => $tour->id,
            'payment_status' => 'pending'
        ]);
    }

    public function test_can_get_available_payment_methods()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/payments/methods');

        $response->assertOk()
            ->assertJsonStructure([
                'payment_methods' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'enabled'
                    ]
                ]
            ]);
    }

    public function test_can_process_credit_card_payment()
    {
        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'payment_data' => [
                'card_number' => '4111111111111111',
                'cvv' => '123',
                'expiry_month' => 12,
                'expiry_year' => 2025,
                'cardholder_name' => 'John Doe'
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/payments/process', $paymentData);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'payment_id',
                'status',
                'message'
            ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'status' => 'completed'
        ]);

        $this->booking->refresh();
        $this->assertEquals('paid', $this->booking->payment_status);
    }

    public function test_can_process_qr_payment()
    {
        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'qr_code',
            'payment_data' => []
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/payments/process', $paymentData);

        $response->assertOk();

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'qr_code'
        ]);
    }

    public function test_cannot_process_payment_for_already_paid_booking()
    {
        $this->booking->update(['payment_status' => 'paid']);

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'payment_data' => [
                'card_number' => '4111111111111111',
                'cvv' => '123',
                'expiry_month' => 12,
                'expiry_year' => 2025,
                'cardholder_name' => 'John Doe'
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false
            ]);
    }

    public function test_validates_credit_card_data()
    {
        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'payment_data' => [
                'card_number' => '123', // Invalid card number
                'cvv' => '12', // Invalid CVV
                'expiry_month' => 13, // Invalid month
                'expiry_year' => 2020, // Past year
                'cardholder_name' => ''
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'payment_data.card_number',
                'payment_data.cvv',
                'payment_data.expiry_month',
                'payment_data.expiry_year',
                'payment_data.cardholder_name'
            ]);
    }

    public function test_can_get_payment_status()
    {
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/payments/{$payment->id}/status");

        $response->assertOk()
            ->assertJson([
                'payment_id' => $payment->id,
                'status' => 'completed'
            ]);
    }

    public function test_can_refund_payment()
    {
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/payments/{$payment->id}/refund");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'refund_id',
                'message'
            ]);

        $payment->refresh();
        $this->assertEquals('refunded', $payment->status);
    }

    public function test_cannot_refund_non_completed_payment()
    {
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/payments/{$payment->id}/refund");

        $response->assertStatus(400);
    }

    public function test_requires_authentication_for_payment_processing()
    {
        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'payment_data' => []
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(401);
    }
}