<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserActivity>
 */
class UserActivityFactory extends Factory
{
    protected $model = UserActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actions = [
            'login',
            'logout',
            'booking_created',
            'booking_cancelled',
            'review_created',
            'profile_updated',
            'password_changed',
            'account_activated',
            'account_deactivated'
        ];

        $descriptions = [
            'login' => 'Usuario inició sesión',
            'logout' => 'Usuario cerró sesión',
            'booking_created' => 'Nueva reserva creada',
            'booking_cancelled' => 'Reserva cancelada',
            'review_created' => 'Nueva valoración creada',
            'profile_updated' => 'Perfil actualizado',
            'password_changed' => 'Contraseña cambiada',
            'account_activated' => 'Cuenta activada',
            'account_deactivated' => 'Cuenta desactivada'
        ];

        $action = $this->faker->randomElement($actions);

        return [
            'user_id' => User::factory(),
            'action' => $action,
            'description' => $descriptions[$action],
            'metadata' => $this->faker->randomElement([
                null,
                ['booking_id' => $this->faker->numberBetween(1, 100)],
                ['review_id' => $this->faker->numberBetween(1, 50)],
                ['ip_address' => $this->faker->ipv4()],
            ]),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'performed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Create a login activity
     */
    public function login(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'login',
            'description' => 'Usuario inició sesión',
            'metadata' => ['login_method' => 'email']
        ]);
    }

    /**
     * Create a booking activity
     */
    public function booking(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'booking_created',
            'description' => 'Nueva reserva creada',
            'metadata' => ['booking_id' => $this->faker->numberBetween(1, 100)]
        ]);
    }

    /**
     * Create an admin action activity
     */
    public function adminAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => $this->faker->randomElement(['user_updated', 'account_activated', 'password_reset']),
            'description' => 'Acción administrativa realizada',
            'metadata' => ['performed_by_admin' => true]
        ]);
    }
}
