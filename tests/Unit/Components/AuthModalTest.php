<?php

namespace Tests\Unit\Components;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthModalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_email_format_correctly()
    {
        // Test invalid email formats - these should have validation errors
        $invalidEmails = [
            'invalid-email',
            '@domain.com',
            'user@',
            'user..name@domain.com'
        ];

        foreach ($invalidEmails as $email) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'password123'
            ]);

            // Should have validation errors for invalid email format
            $this->assertTrue(
                $response->getSession()->has('errors') || 
                $response->status() === 422
            );
        }
    }

    /** @test */
    public function it_validates_password_strength_for_registration()
    {
        // Test weak passwords
        $weakPasswords = [
            'short',           // Too short
            'alllowercase',    // No uppercase
            'ALLUPPERCASE',    // No lowercase
            'NoNumbers',       // No numbers
            '12345678'         // Only numbers
        ];

        foreach ($weakPasswords as $password) {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => $password,
                'password_confirmation' => $password,
                'terms_accepted' => true
            ]);

            $response->assertSessionHasErrors(['password']);
        }

        // Test strong password
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'terms_accepted' => true
        ]);

        $response->assertSessionDoesntHaveErrors(['password']);
    }

    /** @test */
    public function it_validates_name_format_for_registration()
    {
        // Test invalid names
        $invalidNames = [
            'A',                    // Too short
            str_repeat('A', 101),   // Too long
            'Name123',              // Contains numbers
            'Name@#$',              // Contains special characters
            ''                      // Empty
        ];

        foreach ($invalidNames as $name) {
            $response = $this->post('/register', [
                'name' => $name,
                'email' => 'test@example.com',
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
                'terms_accepted' => true
            ]);

            $response->assertSessionHasErrors(['name']);
        }

        // Test valid names
        $validNames = [
            'Juan Pérez',
            'María José García',
            'José Luis Rodríguez Zapatero',
            'Ana María'
        ];

        foreach ($validNames as $name) {
            $response = $this->post('/register', [
                'name' => $name,
                'email' => "test{$name}@example.com",
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
                'terms_accepted' => true
            ]);

            $response->assertSessionDoesntHaveErrors(['name']);
        }
    }

    /** @test */
    public function it_requires_password_confirmation_match()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongPass123',
            'password_confirmation' => 'DifferentPass123',
            'terms_accepted' => true
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function it_requires_terms_acceptance_for_registration()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'terms_accepted' => false
        ]);

        $response->assertSessionHasErrors(['terms_accepted']);
    }

    /** @test */
    public function it_prevents_duplicate_email_registration()
    {
        // Create existing user
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'terms_accepted' => true
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_successfully_registers_valid_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'terms_accepted' => true
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Verify password is hashed
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('StrongPass123', $user->password));
    }

    /** @test */
    public function it_successfully_logs_in_valid_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_rejects_invalid_login_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /** @test */
    public function it_handles_remember_me_functionality()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        
        // Check if remember token is set
        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Test login required fields
        $response = $this->post('/login', []);
        $response->assertSessionHasErrors(['email', 'password']);

        // Test registration required fields
        $response = $this->post('/register', []);
        $response->assertSessionHasErrors(['name', 'email', 'password', 'terms_accepted']);
    }

    /** @test */
    public function it_handles_forgot_password_request()
    {
        $this->markTestSkipped('Password reset functionality requires mail configuration');
        
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com'
        ]);

        // Should not return a server error
        $this->assertNotEquals(500, $response->status());
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function it_validates_forgot_password_email()
    {
        // Test with non-existent email
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertSessionHasErrors(['email']);

        // Test with invalid email format
        $response = $this->post('/forgot-password', [
            'email' => 'invalid-email'
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_handles_password_reset_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // Generate password reset token
        $token = \Illuminate\Support\Str::random(60);
        \DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrongPass123',
            'password_confirmation' => 'NewStrongPass123'
        ]);

        $response->assertRedirect('/');
        
        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('NewStrongPass123', $user->password));
    }

    /** @test */
    public function it_rejects_password_reset_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'NewStrongPass123',
            'password_confirmation' => 'NewStrongPass123'
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}