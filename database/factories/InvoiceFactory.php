<?php

namespace Database\Factories;

use App\Features\Payments\Models\Invoice;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 500);
        $taxAmount = 0; // Bolivia no tiene IVA en turismo
        $totalAmount = $subtotal + $taxAmount;
        
        $issueDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $dueDate = (clone $issueDate)->modify('+30 days');

        return [
            'booking_id' => Booking::factory(),
            'invoice_number' => $this->generateInvoiceNumber(),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => $this->faker->randomElement(['issued', 'paid', 'cancelled']),
            'email_sent_at' => $this->faker->optional(0.7)->dateTimeBetween($issueDate, 'now')
        ];
    }

    public function issued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'issued'
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid'
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled'
        ]);
    }

    public function overdue(): static
    {
        $pastDate = $this->faker->dateTimeBetween('-3 months', '-1 month');
        $dueDate = (clone $pastDate)->modify('+30 days');
        
        return $this->state(fn (array $attributes) => [
            'issue_date' => $pastDate,
            'due_date' => $dueDate,
            'status' => 'issued' // Still unpaid
        ]);
    }

    public function recent(): static
    {
        $recentDate = $this->faker->dateTimeBetween('-1 week', 'now');
        $dueDate = (clone $recentDate)->modify('+30 days');
        
        return $this->state(fn (array $attributes) => [
            'issue_date' => $recentDate,
            'due_date' => $dueDate
        ]);
    }

    public function emailSent(): static
    {
        return $this->state(function (array $attributes) {
            $issueDate = $attributes['issue_date'] ?? $this->faker->dateTimeBetween('-1 month', 'now');
            
            return [
                'email_sent_at' => $this->faker->dateTimeBetween($issueDate, 'now')
            ];
        });
    }

    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $sequence = $this->faker->numberBetween(1, 9999);
        
        return sprintf('INV-%s%s-%04d', $year, $month, $sequence);
    }
}