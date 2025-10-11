<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Booking;
use App\Models\User;
use App\Models\TourSchedule;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $participantsCount = $this->faker->numberBetween(1, 6);
        $pricePerPerson = $this->faker->randomFloat(2, 100, 1500);
        $totalAmount = $participantsCount * $pricePerPerson;
        $commissionRate = $this->faker->randomFloat(2, 5, 15);
        $commissionAmount = ($totalAmount * $commissionRate) / 100;

        $statuses = array_keys(Booking::STATUSES);
        $paymentStatuses = array_keys(Booking::PAYMENT_STATUSES);

        return [
            'user_id' => User::where('role', '!=', 'admin')->inRandomOrder()->first()?->id ?? 1,
            'tour_schedule_id' => TourSchedule::inRandomOrder()->first()?->id ?? 1,
            'participants_count' => $participantsCount,
            'total_amount' => $totalAmount,
            'currency' => 'BOB',
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'status' => $this->faker->randomElement($statuses),
            'payment_status' => $this->faker->randomElement($paymentStatuses),
            'payment_method' => $this->faker->optional(0.7)->randomElement([
                'Tarjeta de crédito', 'Transferencia bancaria', 'PayPal', 
                'Efectivo', 'QR Boliviano', 'Tigo Money'
            ]),
            'payment_reference' => $this->faker->optional(0.7)->regexify('[A-Z0-9]{10,15}'),
            'participant_details' => $this->generateParticipantDetails($participantsCount),
            'special_requests' => $this->faker->optional(0.3)->randomElements([
                'Dieta vegetariana', 'Habitación individual', 'Asistencia especial',
                'Celebración de cumpleaños', 'Fotografía profesional', 'Guía en inglés'
            ], $this->faker->numberBetween(1, 3)),
            'notes' => $this->faker->optional(0.4)->paragraph(),
            'contact_name' => $this->faker->name(),
            'contact_email' => $this->faker->email(),
            'contact_phone' => $this->faker->optional(0.8)->phoneNumber(),
            'emergency_contact_name' => $this->faker->optional(0.6)->name(),
            'emergency_contact_phone' => $this->faker->optional(0.6)->phoneNumber(),
            'confirmed_at' => $this->faker->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'refund_amount' => null,
            'refunded_at' => null,
        ];
    }

    /**
     * Generate participant details for the booking.
     */
    private function generateParticipantDetails(int $count): array
    {
        $participants = [];
        
        for ($i = 0; $i < $count; $i++) {
            $participants[] = [
                'name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'document_type' => $this->faker->randomElement(['CI', 'Pasaporte', 'DNI']),
                'document_number' => $this->faker->numerify('########'),
                'birth_date' => $this->faker->dateTimeBetween('-70 years', '-5 years')->format('Y-m-d'),
                'nationality' => $this->faker->randomElement([
                    'Boliviana', 'Argentina', 'Brasileña', 'Peruana', 'Chilena', 'Estadounidense'
                ]),
                'dietary_restrictions' => $this->faker->optional(0.2)->randomElement([
                    'Vegetariano', 'Vegano', 'Sin gluten', 'Sin lactosa'
                ]),
                'medical_conditions' => $this->faker->optional(0.1)->sentence(),
            ];
        }
        
        return $participants;
    }

    /**
     * Indicate that the booking is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_CONFIRMED,
            'payment_status' => Booking::PAYMENT_PAID,
            'confirmed_at' => $this->faker->dateTimeBetween('-15 days', 'now'),
            'payment_method' => 'Tarjeta de crédito',
            'payment_reference' => $this->faker->regexify('[A-Z0-9]{12}'),
        ]);
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_PENDING,
            'confirmed_at' => null,
            'payment_method' => null,
            'payment_reference' => null,
        ]);
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_CANCELLED,
            'cancelled_at' => $this->faker->dateTimeBetween('-10 days', 'now'),
            'cancellation_reason' => $this->faker->randomElement([
                'Cambio de planes del cliente',
                'Condiciones climáticas adversas',
                'Problemas de salud',
                'Cancelación por parte del operador',
                'Fuerza mayor'
            ]),
        ]);
    }

    /**
     * Indicate that the booking is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_COMPLETED,
            'payment_status' => Booking::PAYMENT_PAID,
            'confirmed_at' => $this->faker->dateTimeBetween('-30 days', '-7 days'),
            'payment_method' => 'Tarjeta de crédito',
            'payment_reference' => $this->faker->regexify('[A-Z0-9]{12}'),
        ]);
    }

    /**
     * Indicate that the booking is for a large group.
     */
    public function largeGroup(): static
    {
        return $this->state(function (array $attributes) {
            $participantsCount = $this->faker->numberBetween(8, 15);
            $pricePerPerson = $attributes['total_amount'] / $attributes['participants_count'];
            $totalAmount = $participantsCount * $pricePerPerson;
            $commissionAmount = ($totalAmount * $attributes['commission_rate']) / 100;

            return [
                'participants_count' => $participantsCount,
                'total_amount' => $totalAmount,
                'commission_amount' => $commissionAmount,
                'participant_details' => $this->generateParticipantDetails($participantsCount),
                'special_requests' => ['Descuento por grupo', 'Guía dedicado'],
            ];
        });
    }

    /**
     * Indicate that the booking is for a solo traveler.
     */
    public function solo(): static
    {
        return $this->state(function (array $attributes) {
            $pricePerPerson = $attributes['total_amount'] / $attributes['participants_count'];
            $totalAmount = $pricePerPerson;
            $commissionAmount = ($totalAmount * $attributes['commission_rate']) / 100;

            return [
                'participants_count' => 1,
                'total_amount' => $totalAmount,
                'commission_amount' => $commissionAmount,
                'participant_details' => $this->generateParticipantDetails(1),
                'special_requests' => $this->faker->optional(0.5)->randomElements([
                    'Habitación individual', 'Compañía de otros viajeros solos'
                ], 1),
            ];
        });
    }
}
