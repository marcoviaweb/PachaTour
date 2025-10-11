<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tour;
use App\Models\TourSchedule;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TourSchedule>
 */
class TourScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('now', '+3 months');
        $startTime = $this->faker->time('H:i', '18:00');
        $endTime = Carbon::createFromFormat('H:i', $startTime)->addHours($this->faker->numberBetween(2, 8))->format('H:i');
        
        return [
            'tour_id' => Tour::factory(),
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'available_spots' => $this->faker->numberBetween(4, 20),
            'booked_spots' => 0,
            'price_override' => $this->faker->optional(0.3)->randomFloat(2, 50, 500),
            'status' => $this->faker->randomElement([
                TourSchedule::STATUS_AVAILABLE,
                TourSchedule::STATUS_AVAILABLE,
                TourSchedule::STATUS_AVAILABLE, // More likely to be available
                TourSchedule::STATUS_FULL,
                TourSchedule::STATUS_CANCELLED
            ]),
            'notes' => $this->faker->optional(0.4)->sentence(),
            'special_conditions' => $this->faker->optional(0.2)->randomElements([
                'Clima favorable requerido',
                'Grupo mínimo 4 personas',
                'Equipo especial incluido',
                'Guía bilingüe disponible'
            ], $this->faker->numberBetween(1, 2)),
            'guide_name' => $this->faker->optional(0.7)->name(),
            'guide_contact' => $this->faker->optional(0.5)->randomElement([
                $this->faker->email(),
                $this->faker->phoneNumber()
            ]),
            'is_private' => $this->faker->boolean(20), // 20% chance of being private
            'weather_forecast' => $this->faker->optional(0.6)->randomFloat(1, 3.0, 10.0),
            'weather_conditions' => $this->faker->optional(0.6)->randomElement([
                'Soleado',
                'Parcialmente nublado',
                'Nublado',
                'Lluvia ligera',
                'Viento moderado',
                'Condiciones ideales'
            ])
        ];
    }

    /**
     * Indicate that the schedule is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TourSchedule::STATUS_AVAILABLE,
            'booked_spots' => $this->faker->numberBetween(0, max(1, $attributes['available_spots'] - 1))
        ]);
    }

    /**
     * Indicate that the schedule is full.
     */
    public function full(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TourSchedule::STATUS_FULL,
                'booked_spots' => $attributes['available_spots']
            ];
        });
    }

    /**
     * Indicate that the schedule is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TourSchedule::STATUS_CANCELLED,
            'notes' => 'Cancelado por condiciones climáticas'
        ]);
    }

    /**
     * Indicate that the schedule is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TourSchedule::STATUS_COMPLETED,
            'date' => $this->faker->dateTimeBetween('-2 months', '-1 day')->format('Y-m-d')
        ]);
    }

    /**
     * Indicate that the schedule is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->toDateString()
        ]);
    }

    /**
     * Indicate that the schedule is for tomorrow.
     */
    public function tomorrow(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->addDay()->toDateString()
        ]);
    }

    /**
     * Indicate that the schedule is in the past.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('-2 months', '-1 day')->format('Y-m-d'),
            'status' => $this->faker->randomElement([
                TourSchedule::STATUS_COMPLETED,
                TourSchedule::STATUS_CANCELLED
            ])
        ]);
    }

    /**
     * Indicate that the schedule has a specific guide.
     */
    public function withGuide(string $name = null, string $contact = null): static
    {
        return $this->state(fn (array $attributes) => [
            'guide_name' => $name ?? $this->faker->name(),
            'guide_contact' => $contact ?? $this->faker->email()
        ]);
    }

    /**
     * Indicate that the schedule is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => true,
            'available_spots' => $this->faker->numberBetween(1, 6) // Smaller groups for private tours
        ]);
    }
}
