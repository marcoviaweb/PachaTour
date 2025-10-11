<?php

namespace Database\Factories;

use App\Features\Payments\Models\Commission;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 5, 100);
        $baseAmount = $amount / 0.10; // Assuming 10% commission rate
        $rate = $amount / $baseAmount;

        return [
            'booking_id' => Booking::factory(),
            'tour_id' => Tour::factory(),
            'amount' => $amount,
            'rate' => round($rate, 4),
            'status' => $this->faker->randomElement(['pending', 'paid']),
            'period_month' => $this->faker->numberBetween(1, 12),
            'period_year' => $this->faker->numberBetween(2023, 2025),
            'paid_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now')
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => $this->faker->dateTimeBetween('-1 month', 'now')
        ]);
    }

    public function forMonth(int $year, int $month): static
    {
        return $this->state(fn (array $attributes) => [
            'period_year' => $year,
            'period_month' => $month,
            'created_at' => $this->faker->dateTimeBetween(
                "{$year}-{$month}-01",
                "{$year}-{$month}-" . cal_days_in_month(CAL_GREGORIAN, $month, $year)
            )
        ]);
    }

    public function premiumRate(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->randomFloat(2, 5, 100);
            return [
                'rate' => 0.15, // 15% for premium tours
                'amount' => round($amount * (0.15 / ($attributes['rate'] ?? 0.10)), 2)
            ];
        });
    }

    public function adventureRate(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->randomFloat(2, 5, 100);
            return [
                'rate' => 0.12, // 12% for adventure tours
                'amount' => round($amount * (0.12 / ($attributes['rate'] ?? 0.10)), 2)
            ];
        });
    }

    public function culturalRate(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? $this->faker->randomFloat(2, 5, 100);
            return [
                'rate' => 0.08, // 8% for cultural tours
                'amount' => round($amount * (0.08 / ($attributes['rate'] ?? 0.10)), 2)
            ];
        });
    }
}