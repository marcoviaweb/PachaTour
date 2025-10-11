<?php

namespace Tests\Unit\Components;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Features\Payments\Models\Payment;
use App\Features\Payments\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class PaymentFormTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Booking $booking;
    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        // Create necessary related models for booking
        $department = \App\Models\Department::factory()->create();
        $attraction = \App\Models\Attraction::factory()->create([
            'department_id' => $department->id
        ]);
        $tour = \App\Models\Tour::factory()->create();
        
        // Attach attraction to tour (many-to-many relationship)
        $tour->attractions()->attach($attraction->id);
        
        $tourSchedule = \App\Models\TourSchedule::factory()->create([
            'tour_id' => $tour->id
        ]);
        
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $tourSchedule->id,
            'total_amount' => 150.00,
            'payment_status' => 'pending'
        ]);
        
        $this->paymentService = app(PaymentService::class);
    }

    /** @test */
    public function it_can_get_available_payment_methods()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->getJson('/api/payments/methods');

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

        $methods = $response->json('payment_methods');
        $this->assertNotEmpty($methods);
        
        $methodIds = collect($methods)->pluck('id')->toArray();
        $this->assertContains('credit_card', $methodIds);
        $this->assertContains('debit_card', $methodIds);
        $this->assertContains('bank_transfer', $methodIds);
        $this->assertContains('qr_code', $methodIds);
    }

    /** @test */
    public function it_can_process_credit_card_payment()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'payment_data' => [
                'card_number' => '4111111111111111',
                'cvv' => '123',
                'expiry_month' => 12,
                'expiry_year' => date('Y') + 1,
                'cardholder_name' => 'Juan Pérez'
            ]
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'payment_id',
                'status',
                'message'
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('completed', $response->json('status'));

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'status' => 'completed'
        ]);

        // Verify booking was updated
        $this->booking->refresh();
        $this->assertEquals('paid', $this->booking->payment_status);
    }

    /** @test */
    public function it_can_process_debit_card_payment()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'debit_card',
            'payment_data' => [
                'card_number' => '5555555555554444',
                'cvv' => '456',
                'expiry_month' => 6,
                'expiry_year' => date('Y') + 2,
                'cardholder_name' => 'María García'
            ]
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'debit_card',
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function it_can_process_bank_transfer_payment()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'bank_transfer',
            'payment_data' => [
                'bank_code' => 'BNB',
                'account_number' => '1234567890'
            ]
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'bank_transfer',
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function it_can_process_qr_code_payment()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'qr_code',
            'payment_data' => []
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'qr_code',
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_for_credit_card()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'payment_data' => [
                // Missing required fields
            ]
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'payment_data.card_number',
                'payment_data.cvv',
                'payment_data.expiry_month',
                'payment_data.expiry_year',
                'payment_data.cardholder_name'
            ]);
    }

    /** @test */
    public function it_validates_card_number_format()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'payment_data' => [
                'card_number' => '123', // Invalid format
                'cvv' => '123',
                'expiry_month' => 12,
                'expiry_year' => date('Y') + 1,
                'cardholder_name' => 'Juan Pérez'
            ]
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_data.card_number']);
    }

    /** @test */
    public function it_validates_cvv_format()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'payment_data' => [
                'card_number' => '4111111111111111',
                'cvv' => '12', // Invalid format
                'expiry_month' => 12,
                'expiry_year' => date('Y') + 1,
                'cardholder_name' => 'Juan Pérez'
            ]
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_data.cvv']);
    }

    /** @test */
    public function it_validates_expiry_date_not_in_past()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'payment_data' => [
                'card_number' => '4111111111111111',
                'cvv' => '123',
                'expiry_month' => 12,
                'expiry_year' => date('Y') - 1, // Past year
                'cardholder_name' => 'Juan Pérez'
            ]
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_data.expiry_year']);
    }

    /** @test */
    public function it_validates_required_fields_for_bank_transfer()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'bank_transfer',
            'payment_data' => [
                // Missing required fields
            ]
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'payment_data.bank_code',
                'payment_data.account_number'
            ]);
    }

    /** @test */
    public function it_prevents_payment_for_already_paid_booking()
    {
        $this->actingAs($this->user, 'sanctum');

        // Mark booking as already paid
        $this->booking->update(['payment_status' => 'paid']);

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'payment_data' => [
                'card_number' => '4111111111111111',
                'cvv' => '123',
                'expiry_month' => 12,
                'expiry_year' => date('Y') + 1,
                'cardholder_name' => 'Juan Pérez'
            ]
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function it_can_get_payment_status()
    {
        $this->actingAs($this->user, 'sanctum');

        $payment = Payment::factory()->completed()->create([
            'booking_id' => $this->booking->id
        ]);

        $response = $this->getJson("/api/payments/{$payment->id}/status");

        $response->assertOk()
            ->assertJson([
                'payment_id' => $payment->id,
                'status' => 'completed'
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_payment_status()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->getJson('/api/payments/999999/status');

        $response->assertNotFound();
    }

    /** @test */
    public function it_can_refund_completed_payment()
    {
        $this->actingAs($this->user, 'sanctum');

        $payment = Payment::factory()->completed()->create([
            'booking_id' => $this->booking->id
        ]);

        $response = $this->postJson("/api/payments/{$payment->id}/refund");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'refund_id',
                'message'
            ]);

        $this->assertTrue($response->json('success'));

        // Verify payment status was updated
        $payment->refresh();
        $this->assertEquals('refunded', $payment->status);

        // Verify booking status was updated
        $this->booking->refresh();
        $this->assertEquals('refunded', $this->booking->payment_status);
    }

    /** @test */
    public function it_prevents_refund_of_non_completed_payment()
    {
        $this->actingAs($this->user, 'sanctum');

        $payment = Payment::factory()->pending()->create([
            'booking_id' => $this->booking->id
        ]);

        $response = $this->postJson("/api/payments/{$payment->id}/refund");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_payment_endpoints()
    {
        $response = $this->getJson('/api/payments/methods');
        $response->assertUnauthorized();

        $response = $this->postJson('/api/payments/process', []);
        $response->assertUnauthorized();

        $response = $this->getJson('/api/payments/1/status');
        $response->assertUnauthorized();

        $response = $this->postJson('/api/payments/1/refund');
        $response->assertUnauthorized();
    }

    /** @test */
    public function it_validates_booking_exists()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => 999999, // Non-existent booking
            'payment_method' => 'credit_card',
            'payment_data' => [
                'card_number' => '4111111111111111',
                'cvv' => '123',
                'expiry_month' => 12,
                'expiry_year' => date('Y') + 1,
                'cardholder_name' => 'Juan Pérez'
            ]
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_id']);
    }

    /** @test */
    public function it_validates_payment_method_is_supported()
    {
        $this->actingAs($this->user, 'sanctum');

        $paymentData = [
            'booking_id' => $this->booking->id,
            'payment_method' => 'unsupported_method',
            'payment_data' => []
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }
}