<?php

namespace Tests\Unit\Payments;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\Attraction;
use App\Models\Department;
use App\Models\User;
use App\Features\Payments\Services\PaymentService;
use App\Features\Payments\Services\CommissionService;
use App\Features\Payments\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;
    private CommissionService $commissionService;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->commissionService = new CommissionService();
        $this->paymentService = new PaymentService($this->commissionService);
        
        $user = User::factory()->create();
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);
        $tour = Tour::factory()->create(['attraction_id' => $attraction->id]);
        
        $this->booking = Booking::factory()->create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'total_amount' => 100.00,
            'payment_status' => 'pending'
        ]);
    }

    public function test_can_get_available_payment_methods()
    {
        $methods = $this->paymentService->getAvailablePaymentMethods();

        $this->assertIsArray($methods);
        $this->assertNotEmpty($methods);
        
        foreach ($methods as $method) {
            $this->assertArrayHasKey('id', $method);
            $this->assertArrayHasKey('name', $method);
            $this->assertArrayHasKey('description', $method);
            $this->assertArrayHasKey('enabled', $method);
        }
    }

    public function test_can_process_credit_card_payment()
    {
        $paymentData = [
            'card_number' => '4111111111111111',
            'cvv' => '123',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cardholder_name' => 'John Doe'
        ];

        $result = $this->paymentService->processPayment($this->booking, 'credit_card', $paymentData);

        $this->assertArrayHasKey('payment_id', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('completed', $result['status']);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'status' => 'completed'
        ]);

        $this->booking->refresh();
        $this->assertEquals('paid', $this->booking->payment_status);
    }

    public function test_calculates_commission_correctly()
    {
        $paymentData = ['card_number' => '4111111111111111', 'cvv' => '123'];
        
        $result = $this->paymentService->processPayment($this->booking, 'credit_card', $paymentData);
        
        $payment = Payment::find($result['payment_id']);
        
        $expectedCommission = $this->booking->total_amount * 0.10; // 10% default rate
        $expectedOperatorAmount = $this->booking->total_amount - $expectedCommission;
        
        $this->assertEquals($expectedCommission, $payment->commission_amount);
        $this->assertEquals($expectedOperatorAmount, $payment->operator_amount);
    }

    public function test_throws_exception_for_already_paid_booking()
    {
        $this->booking->update(['payment_status' => 'paid']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Esta reserva ya ha sido pagada');

        $this->paymentService->processPayment($this->booking, 'credit_card', []);
    }

    public function test_throws_exception_for_unsupported_payment_method()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Método de pago no soportado');

        $this->paymentService->processPayment($this->booking, 'unsupported_method', []);
    }

    public function test_can_get_payment_status()
    {
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'status' => 'completed'
        ]);

        $status = $this->paymentService->getPaymentStatus($payment->id);

        $this->assertEquals('completed', $status);
    }

    public function test_can_refund_completed_payment()
    {
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'status' => 'completed'
        ]);

        $result = $this->paymentService->refundPayment($payment->id);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('refund_id', $result);
        $this->assertTrue($result['success']);

        $payment->refresh();
        $this->assertEquals('refunded', $payment->status);

        $this->booking->refresh();
        $this->assertEquals('refunded', $this->booking->payment_status);
    }

    public function test_cannot_refund_non_completed_payment()
    {
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'status' => 'pending'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Solo se pueden reembolsar pagos completados');

        $this->paymentService->refundPayment($payment->id);
    }

    public function test_generates_unique_payment_reference()
    {
        $paymentData = ['card_number' => '4111111111111111', 'cvv' => '123'];
        
        $result1 = $this->paymentService->processPayment($this->booking, 'credit_card', $paymentData);
        
        // Create another booking for second payment
        $booking2 = Booking::factory()->create([
            'user_id' => $this->booking->user_id,
            'tour_id' => $this->booking->tour_id,
            'total_amount' => 150.00,
            'payment_status' => 'pending'
        ]);
        
        $result2 = $this->paymentService->processPayment($booking2, 'credit_card', $paymentData);
        
        $payment1 = Payment::find($result1['payment_id']);
        $payment2 = Payment::find($result2['payment_id']);
        
        $this->assertNotEquals($payment1->payment_reference, $payment2->payment_reference);
    }
}