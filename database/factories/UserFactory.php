<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        
        return [
            'name' => $firstName,
            'last_name' => $lastName,
            'email' => fake()->unique()->safeEmail(),
            'role' => fake()->randomElement([User::ROLE_VISITOR, User::ROLE_TOURIST]),
            'phone' => fake()->optional(0.7)->phoneNumber(),
            'birth_date' => fake()->optional(0.8, null)->dateTimeBetween('-70 years', '-18 years')?->format('Y-m-d'),
            'gender' => fake()->optional(0.6)->randomElement(['male', 'female', 'other']),
            'nationality' => fake()->randomElement([
                'Boliviana', 'Argentina', 'Brasileña', 'Peruana', 'Chilena', 
                'Colombiana', 'Estadounidense', 'Española', 'Francesa', 'Alemana'
            ]),
            'country' => fake()->country(),
            'city' => fake()->city(),
            'preferred_language' => fake()->randomElement(['es', 'en', 'pt', 'fr']),
            'interests' => fake()->optional(0.8)->randomElements([
                'Naturaleza', 'Cultura', 'Aventura', 'Gastronomía', 'Historia',
                'Fotografía', 'Senderismo', 'Arqueología', 'Vida silvestre', 'Relajación'
            ], fake()->numberBetween(2, 5)),
            'bio' => fake()->optional(0.3)->paragraph(),
            'newsletter_subscription' => fake()->boolean(60),
            'marketing_emails' => fake()->boolean(40),
            'last_login_at' => fake()->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'last_login_ip' => fake()->optional(0.8)->ipv4(),
            'is_active' => fake()->boolean(95),
            'email_verified_at' => fake()->boolean(80) ? now() : null,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the user is a tourist.
     */
    public function tourist(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_TOURIST,
            'interests' => ['Naturaleza', 'Cultura', 'Aventura', 'Fotografía'],
            'newsletter_subscription' => true,
        ]);
    }

    /**
     * Indicate that the user is from Bolivia.
     */
    public function bolivian(): static
    {
        return $this->state(fn (array $attributes) => [
            'nationality' => 'Boliviana',
            'country' => 'Bolivia',
            'city' => fake()->randomElement([
                'La Paz', 'Santa Cruz', 'Cochabamba', 'Sucre', 'Potosí', 
                'Oruro', 'Tarija', 'Trinidad', 'Cobija'
            ]),
            'preferred_language' => 'es',
        ]);
    }

    /**
     * Indicate that the user is international.
     */
    public function international(): static
    {
        return $this->state(fn (array $attributes) => [
            'nationality' => fake()->randomElement([
                'Argentina', 'Brasileña', 'Peruana', 'Chilena', 'Estadounidense', 
                'Española', 'Francesa', 'Alemana', 'Italiana', 'Canadiense'
            ]),
            'preferred_language' => fake()->randomElement(['en', 'es', 'pt', 'fr']),
            'interests' => ['Cultura', 'Historia', 'Naturaleza', 'Aventura', 'Fotografía'],
        ]);
    }
}
