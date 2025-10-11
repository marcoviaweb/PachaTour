<?php

namespace Tests\Unit\Components;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use App\Models\User;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock social auth configuration
        Config::set('services.google', [
            'client_id' => 'test-google-client-id',
            'client_secret' => 'test-google-client-secret',
            'redirect' => 'http://localhost/auth/google/callback'
        ]);

        Config::set('services.facebook', [
            'client_id' => 'test-facebook-client-id',
            'client_secret' => 'test-facebook-client-secret',
            'redirect' => 'http://localhost/auth/facebook/callback'
        ]);
    }

    /** @test */
    public function it_redirects_to_google_auth_provider()
    {
        $response = $this->get('/auth/google/redirect');
        
        $response->assertStatus(302);
        $this->assertStringContains('accounts.google.com', $response->headers->get('Location'));
    }

    /** @test */
    public function it_redirects_to_facebook_auth_provider()
    {
        $response = $this->get('/auth/facebook/redirect');
        
        $response->assertStatus(302);
        $this->assertStringContains('facebook.com', $response->headers->get('Location'));
    }

    /** @test */
    public function it_handles_google_callback_for_new_user()
    {
        // Mock Socialite user data
        $mockUser = (object) [
            'id' => '123456789',
            'email' => 'test@gmail.com',
            'name' => 'Test User',
            'avatar' => 'https://example.com/avatar.jpg'
        ];

        // Mock Socialite facade
        \Socialite::shouldReceive('driver->user')
            ->once()
            ->andReturn($mockUser);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        
        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'test@gmail.com',
            'name' => 'Test User',
            'social_provider' => 'google',
            'social_id' => '123456789'
        ]);

        // Verify user is authenticated
        $user = User::where('email', 'test@gmail.com')->first();
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_handles_google_callback_for_existing_user()
    {
        // Create existing user
        $existingUser = User::factory()->create([
            'email' => 'test@gmail.com',
            'social_provider' => 'google',
            'social_id' => '123456789'
        ]);

        // Mock Socialite user data
        $mockUser = (object) [
            'id' => '123456789',
            'email' => 'test@gmail.com',
            'name' => 'Test User Updated',
            'avatar' => 'https://example.com/avatar.jpg'
        ];

        \Socialite::shouldReceive('driver->user')
            ->once()
            ->andReturn($mockUser);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        
        // Verify user was updated
        $existingUser->refresh();
        $this->assertEquals('Test User Updated', $existingUser->name);
        
        // Verify user is authenticated
        $this->assertAuthenticatedAs($existingUser);
    }

    /** @test */
    public function it_handles_facebook_callback_for_new_user()
    {
        // Mock Socialite user data
        $mockUser = (object) [
            'id' => 'fb123456789',
            'email' => 'test@facebook.com',
            'name' => 'Facebook User',
            'avatar' => 'https://facebook.com/avatar.jpg'
        ];

        \Socialite::shouldReceive('driver->user')
            ->once()
            ->andReturn($mockUser);

        $response = $this->get('/auth/facebook/callback');

        $response->assertRedirect('/');
        
        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'test@facebook.com',
            'name' => 'Facebook User',
            'social_provider' => 'facebook',
            'social_id' => 'fb123456789'
        ]);
    }

    /** @test */
    public function it_handles_email_conflict_with_existing_regular_user()
    {
        // Create existing regular user (non-social)
        $existingUser = User::factory()->create([
            'email' => 'test@gmail.com',
            'social_provider' => null,
            'social_id' => null
        ]);

        // Mock Socialite user data with same email
        $mockUser = (object) [
            'id' => '123456789',
            'email' => 'test@gmail.com',
            'name' => 'Social User',
            'avatar' => 'https://example.com/avatar.jpg'
        ];

        \Socialite::shouldReceive('driver->user')
            ->once()
            ->andReturn($mockUser);

        $response = $this->get('/auth/google/callback');

        // Should link the social account to existing user
        $response->assertRedirect('/');
        
        $existingUser->refresh();
        $this->assertEquals('google', $existingUser->social_provider);
        $this->assertEquals('123456789', $existingUser->social_id);
        $this->assertAuthenticatedAs($existingUser);
    }

    /** @test */
    public function it_handles_social_auth_errors_gracefully()
    {
        // Mock Socialite to throw exception
        \Socialite::shouldReceive('driver->user')
            ->once()
            ->andThrow(new \Exception('OAuth error'));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Error de autenticación con Google. Inténtalo de nuevo.');
        $this->assertGuest();
    }

    /** @test */
    public function it_handles_missing_email_from_social_provider()
    {
        // Mock Socialite user data without email
        $mockUser = (object) [
            'id' => '123456789',
            'email' => null,
            'name' => 'Test User',
            'avatar' => 'https://example.com/avatar.jpg'
        ];

        \Socialite::shouldReceive('driver->user')
            ->once()
            ->andReturn($mockUser);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'No se pudo obtener el correo electrónico de Google.');
        $this->assertGuest();
    }

    /** @test */
    public function it_validates_supported_social_providers()
    {
        // Test unsupported provider
        $response = $this->get('/auth/unsupported/redirect');
        $response->assertStatus(404);

        $response = $this->get('/auth/unsupported/callback');
        $response->assertStatus(404);
    }

    /** @test */
    public function it_sets_correct_user_role_for_social_registration()
    {
        // Mock Socialite user data
        $mockUser = (object) [
            'id' => '123456789',
            'email' => 'test@gmail.com',
            'name' => 'Test User',
            'avatar' => 'https://example.com/avatar.jpg'
        ];

        \Socialite::shouldReceive('driver->user')
            ->once()
            ->andReturn($mockUser);

        $response = $this->get('/auth/google/callback');

        // Verify user has correct default role
        $user = User::where('email', 'test@gmail.com')->first();
        $this->assertEquals('tourist', $user->role);
    }

    /** @test */
    public function it_handles_social_auth_state_parameter()
    {
        // Test with state parameter for CSRF protection
        $response = $this->get('/auth/google/redirect?state=test-state');
        
        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        $this->assertStringContains('state=', $location);
    }

    /** @test */
    public function it_logs_social_authentication_attempts()
    {
        // Mock Socialite user data
        $mockUser = (object) [
            'id' => '123456789',
            'email' => 'test@gmail.com',
            'name' => 'Test User',
            'avatar' => 'https://example.com/avatar.jpg'
        ];

        \Socialite::shouldReceive('driver->user')
            ->once()
            ->andReturn($mockUser);

        // Mock Log facade to verify logging
        \Log::shouldReceive('info')
            ->once()
            ->with('Social authentication successful', [
                'provider' => 'google',
                'user_id' => '123456789',
                'email' => 'test@gmail.com'
            ]);

        $response = $this->get('/auth/google/callback');
        $response->assertRedirect('/');
    }

    /** @test */
    public function it_handles_provider_specific_scopes()
    {
        // Test Google with specific scopes
        $response = $this->get('/auth/google/redirect');
        $location = $response->headers->get('Location');
        
        // Should include email and profile scopes
        $this->assertStringContains('scope=', $location);
        
        // Test Facebook with specific scopes
        $response = $this->get('/auth/facebook/redirect');
        $location = $response->headers->get('Location');
        
        $this->assertStringContains('scope=', $location);
    }
}