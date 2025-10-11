<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\UserActivity;
use App\Features\Admin\Services\UserActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true
        ]);

        $this->testUser = User::factory()->create([
            'role' => 'tourist',
            'is_active' => true
        ]);
    }

    /** @test */
    public function admin_can_list_users_with_pagination()
    {
        User::factory()->count(20)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'is_active',
                        'created_at',
                        'bookings_count',
                        'reviews_count'
                    ]
                ],
                'current_page',
                'per_page',
                'total'
            ]);
    }

    /** @test */
    public function admin_can_filter_users_by_search()
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users?search=John');

        $response->assertStatus(200);
        
        $users = $response->json('data');
        $this->assertCount(1, $users);
        $this->assertEquals('John Doe', $users[0]['name']);
    }

    /** @test */
    public function admin_can_filter_users_by_role()
    {
        User::factory()->create(['role' => 'admin']);
        User::factory()->count(3)->create(['role' => 'tourist']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users?role=tourist');

        $response->assertStatus(200);
        
        $users = $response->json('data');
        $this->assertGreaterThanOrEqual(3, count($users));
        
        foreach ($users as $user) {
            $this->assertEquals('tourist', $user['role']);
        }
    }

    /** @test */
    public function admin_can_view_user_details_with_activity()
    {
        UserActivity::factory()->count(5)->create(['user_id' => $this->testUser->id]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/users/{$this->testUser->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'is_active',
                    'bookings',
                    'reviews'
                ],
                'recent_activity' => [
                    '*' => [
                        'action',
                        'description',
                        'performed_at'
                    ]
                ]
            ]);
    }

    /** @test */
    public function admin_can_update_user_information()
    {
        $updateData = [
            'name' => 'Updated Name',
            'phone' => '+591 70123456',
            'nationality' => 'Bolivian'
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/users/{$this->testUser->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Usuario actualizado exitosamente'
            ]);

        $this->testUser->refresh();
        $this->assertEquals('Updated Name', $this->testUser->name);
        $this->assertEquals('+591 70123456', $this->testUser->phone);
        $this->assertEquals('Bolivian', $this->testUser->nationality);

        // Verify activity was logged
        $this->assertDatabaseHas('user_activities', [
            'user_id' => $this->testUser->id,
            'action' => 'user_updated'
        ]);
    }

    /** @test */
    public function admin_can_activate_inactive_user()
    {
        $this->testUser->update(['is_active' => false]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/users/{$this->testUser->id}/activate");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Usuario activado exitosamente'
            ]);

        $this->testUser->refresh();
        $this->assertTrue($this->testUser->is_active);

        // Verify activity was logged
        $this->assertDatabaseHas('user_activities', [
            'user_id' => $this->testUser->id,
            'action' => 'account_activated'
        ]);
    }

    /** @test */
    public function admin_cannot_activate_already_active_user()
    {
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/users/{$this->testUser->id}/activate");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'El usuario ya está activo'
            ]);
    }

    /** @test */
    public function admin_can_deactivate_active_user()
    {
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/users/{$this->testUser->id}/deactivate");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Usuario desactivado exitosamente'
            ]);

        $this->testUser->refresh();
        $this->assertFalse($this->testUser->is_active);

        // Verify activity was logged
        $this->assertDatabaseHas('user_activities', [
            'user_id' => $this->testUser->id,
            'action' => 'account_deactivated'
        ]);
    }

    /** @test */
    public function admin_cannot_deactivate_admin_user()
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/users/{$adminUser->id}/deactivate");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'No se puede desactivar un usuario administrador'
            ]);
    }

    /** @test */
    public function admin_can_reset_user_password()
    {
        $originalPassword = $this->testUser->password;

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/users/{$this->testUser->id}/reset-password");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'temporary_password',
                'note'
            ]);

        $this->testUser->refresh();
        $this->assertNotEquals($originalPassword, $this->testUser->password);
        $this->assertNull($this->testUser->password_changed_at);

        // Verify activity was logged
        $this->assertDatabaseHas('user_activities', [
            'user_id' => $this->testUser->id,
            'action' => 'password_reset'
        ]);
    }

    /** @test */
    public function admin_can_send_password_reset_link()
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => $this->testUser->email])
            ->andReturn(Password::RESET_LINK_SENT);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/users/{$this->testUser->id}/send-password-reset");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Enlace de restablecimiento enviado exitosamente'
            ]);

        // Verify activity was logged
        $this->assertDatabaseHas('user_activities', [
            'user_id' => $this->testUser->id,
            'action' => 'password_reset_link_sent'
        ]);
    }

    /** @test */
    public function admin_can_get_user_activity_logs()
    {
        UserActivity::factory()->count(10)->create(['user_id' => $this->testUser->id]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/users/{$this->testUser->id}/activity");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'action',
                    'description',
                    'performed_at',
                    'metadata'
                ]
            ]);

        $activities = $response->json();
        $this->assertLessThanOrEqual(20, count($activities)); // Default limit
    }

    /** @test */
    public function admin_can_get_user_statistics()
    {
        User::factory()->count(5)->create(['role' => 'tourist']);
        User::factory()->count(2)->create(['role' => 'admin']);
        User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_users',
                'active_users',
                'inactive_users',
                'users_by_role',
                'recent_registrations',
                'users_with_bookings'
            ]);
    }

    /** @test */
    public function non_admin_cannot_access_user_management()
    {
        $response = $this->actingAs($this->testUser)
            ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_user_management()
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(401);
    }

    /** @test */
    public function admin_cannot_update_user_with_invalid_data()
    {
        $invalidData = [
            'name' => '', // Required field
            'email' => 'invalid-email', // Invalid format
            'role' => 'invalid-role' // Invalid role
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/users/{$this->testUser->id}", $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'role']);
    }

    /** @test */
    public function admin_cannot_update_user_with_duplicate_email()
    {
        $existingUser = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/users/{$this->testUser->id}", [
                'email' => $existingUser->email
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}