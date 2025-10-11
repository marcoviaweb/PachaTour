<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_social_provider()
    {
        $socialData = [
            'provider' => 'google',
            'provider_id' => '123456789',
            'email' => 'test@example.com',
            'name' => 'Juan Pérez',
            'avatar' => 'https://example.com/avatar.jpg',
        ];

        $response = $this->postJson('/api/auth/social-login', $socialData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user' => [
                        'id',
                        'name',
                        'last_name',
                        'email',
                        'role',
                        'preferred_language',
                        'avatar_path',
                    ],
                    'access_token',
                    'token_type',
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'social_provider' => 'google',
            'social_id' => '123456789',
        ]);
    }

    public function test_existing_user_can_login_with_social_provider()
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'social_provider' => null,
            'social_id' => null,
        ]);

        $socialData = [
            'provider' => 'google',
            'provider_id' => '123456789',
            'email' => 'existing@example.com',
            'name' => 'Juan Pérez',
        ];

        $response = $this->postJson('/api/auth/social-login', $socialData);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('google', $user->social_provider);
        $this->assertEquals('123456789', $user->social_id);
    }

    public function test_authenticated_user_can_link_social_account()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $socialData = [
            'provider' => 'facebook',
            'provider_id' => '987654321',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/link-social', $socialData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Cuenta social vinculada exitosamente']);

        $user->refresh();
        $this->assertEquals('facebook', $user->social_provider);
        $this->assertEquals('987654321', $user->social_id);
    }

    public function test_user_cannot_link_already_linked_social_account()
    {
        $existingUser = User::factory()->create([
            'social_provider' => 'facebook',
            'social_id' => '987654321',
        ]);

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $socialData = [
            'provider' => 'facebook',
            'provider_id' => '987654321',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/link-social', $socialData);

        $response->assertStatus(409)
                ->assertJson(['message' => 'Esta cuenta social ya está vinculada a otro usuario']);
    }

    public function test_authenticated_user_can_unlink_social_account()
    {
        $user = User::factory()->create([
            'social_provider' => 'google',
            'social_id' => '123456789',
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/auth/unlink-social');

        $response->assertStatus(200)
                ->assertJson(['message' => 'Cuenta social desvinculada exitosamente']);

        $user->refresh();
        $this->assertNull($user->social_provider);
        $this->assertNull($user->social_id);
    }

    public function test_user_cannot_unlink_non_existent_social_account()
    {
        $user = User::factory()->create([
            'social_provider' => null,
            'social_id' => null,
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/auth/unlink-social');

        $response->assertStatus(400)
                ->assertJson(['message' => 'No hay cuenta social vinculada']);
    }
}