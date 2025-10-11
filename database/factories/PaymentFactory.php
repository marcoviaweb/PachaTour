<?php

namespace Database\Factories;

use App\Features\Payments\Models\Payment;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 50, 500);
        $commissionRate = $this->faker->randomFloat(4, 0.05, 0.20);
        $commissionAmount = round($amount * $commissionRate, 2);
        $operatorAmount = $amount - $commissionAmount;

        return [
            'booking_id' => Booking::factory(),
            'amount' => $amount,
            'commission_amount' => $commissionAmount,
            'operator_amount' => $operatorAmount,
            'payment_method' => $this->faker->randomElement(['credit_card', 'debit_card', 'bank_transfer', 'qr_code']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'payment_reference' => 'PAY-' . date('Ymd') . '-' . strtoupper($this->faker->bothify('########')),
            'gateway_transaction_id' => $this->faker->optional()->bothify('txn_####################'),
            'gateway_data' => $this->faker->optional()->passthrough([
                'card_last_four' => $this->faker->numerify('####'),
                'card_brand' => $this->faker->randomElement(['visa', 'mastercard', 'amex']),
                'gateway_fee' => $this->faker->randomFloat(2, 1, 10)
            ])
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'gateway_transaction_id' => null
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'gateway_transaction_id' => 'txn_' . $this->faker->bothify('####################')
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'gateway_transaction_id' => null
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'gateway_transaction_id' => 'txn_' . $this->faker->bothify('####################')
        ]);
    }

    public function creditCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'credit_card',
            'gateway_data' => [
                'card_last_four' => $this->faker->numerify('####'),
                'card_brand' => $this->faker->randomElement(['visa', 'mastercard', 'amex']),
                'gateway_fee' => $this->faker->randomFloat(2, 2, 8)
            ]
        ]);
    }

    public function qrCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'qr_code',
            'gateway_data' => [
                'qr_reference' => $this->faker->bothify('QR##########'),
                'bank_code' => $this->faker->randomElement(['001', '002', '003'])
            ]
        ]);
    }
}