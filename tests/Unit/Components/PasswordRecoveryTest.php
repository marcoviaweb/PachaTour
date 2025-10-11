<?php

namespace Tests\Unit\Components;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\User;
use Carbon\Carbon;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
    }

    /** @test */
    public function it_validates_email_for_password_reset_request()
    {
        // Test with invalid email format
        $response = $this->post('/forgot-password', [
            'email' => 'invalid-email'
        ]);

        $response->assertSessionHasErrors(['email']);

        // Test with empty email
        $response = $this->post('/forgot-password', [
            'email' => ''
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_sends_password_reset_email_for_existing_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com'
        ]);

        $response->assertSessionHas('status');
        
        // Verify notification was sent
        Notification::assertSentTo($user, ResetPassword::class);
        
        // Verify token was created in database
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@example.com'
        ]);
    }

    /** @test */
    public function it_handles_password_reset_request_for_nonexistent_user()
    {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com'
        ]);

        // Should still show success message for security
        $response->assertSessionHas('status');
        
        // But no notification should be sent
        Notification::assertNothingSent();
    }

    /** @test */
    public function it_validates_password_reset_form_fields()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'valid-reset-token';

        // Test missing fields
        $response = $this->post('/reset-password', []);
        $response->assertSessionHasErrors(['token', 'email', 'password']);

        // Test invalid email format
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'invalid-email',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123'
        ]);
        $response->assertSessionHasErrors(['email']);

        // Test weak password
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak'
        ]);
        $response->assertSessionHasErrors(['password']);

        // Test password confirmation mismatch
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewPassword123',
            'password_confirmation' => 'DifferentPassword123'
        ]);
        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function it_validates_password_strength_requirements()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'valid-reset-token';

        // Create valid reset token
        \DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        // Test passwords that don't meet requirements
        $weakPasswords = [
            'short',           // Too short
            'alllowercase',    // No uppercase
            'ALLUPPERCASE',    // No lowercase  
            'NoNumbers',       // No numbers
            '12345678'         // Only numbers
        ];

        foreach ($weakPasswords as $password) {
            $response = $this->post('/reset-password', [
                'token' => $token,
                'email' => 'test@example.com',
                'password' => $password,
                'password_confirmation' => $password
            ]);

            $response->assertSessionHasErrors(['password']);
        }

        // Test strong password
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'StrongPassword123',
            'password_confirmation' => 'StrongPassword123'
        ]);

        $response->assertRedirect('/');
        $response->assertSessionDoesntHaveErrors();
    }

    /** @test */
    public function it_successfully_resets_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword')
        ]);

        $token = 'valid-reset-token';

        // Create password reset token
        \DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewStrongPassword123',
            'password_confirmation' => 'NewStrongPassword123'
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('status');

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('NewStrongPassword123', $user->password));
        $this->assertFalse(Hash::check('oldpassword', $user->password));

        // Verify reset token was deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'test@example.com'
        ]);
    }

    /** @test */
    public function it_rejects_invalid_reset_token()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'NewStrongPassword123',
            'password_confirmation' => 'NewStrongPassword123'
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_rejects_expired_reset_token()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'expired-token';

        // Create expired reset token (older than 1 hour)
        \DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()->subHours(2)
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewStrongPassword123',
            'password_confirmation' => 'NewStrongPassword123'
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_prevents_token_reuse()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'valid-token';

        // Create reset token
        \DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        // First reset should succeed
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123'
        ]);

        $response->assertRedirect('/');

        // Second attempt with same token should fail
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'AnotherPassword123',
            'password_confirmation' => 'AnotherPassword123'
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_handles_password_reset_for_nonexistent_user()
    {
        $response = $this->post('/reset-password', [
            'token' => 'any-token',
            'email' => 'nonexistent@example.com',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123'
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_rate_limits_password_reset_requests()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Make multiple requests quickly
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/forgot-password', [
                'email' => 'test@example.com'
            ]);
        }

        // Should be rate limited after 5 attempts
        $response->assertStatus(429);
    }

    /** @test */
    public function it_logs_password_reset_attempts()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        \Log::shouldReceive('info')
            ->once()
            ->with('Password reset requested', ['email' => 'test@example.com']);

        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com'
        ]);

        $response->assertSessionHas('status');
    }

    /** @test */
    public function it_cleans_up_old_reset_tokens()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Create old tokens
        \DB::table('password_reset_tokens')->insert([
            [
                'email' => 'test@example.com',
                'token' => Hash::make('old-token-1'),
                'created_at' => Carbon::now()->subDays(2)
            ],
            [
                'email' => 'test@example.com',
                'token' => Hash::make('old-token-2'),
                'created_at' => Carbon::now()->subDays(3)
            ]
        ]);

        // Request new reset
        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com'
        ]);

        // Old tokens should be cleaned up
        $tokenCount = \DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->count();

        $this->assertEquals(1, $tokenCount);
    }
}