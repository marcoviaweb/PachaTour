<?php

namespace Tests\Feature\Reviews;

use Tests\TestCase;
use App\Models\User;
use App\Models\Review;
use App\Models\Attraction;
use App\Models\Department;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class ModerationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $user;
    protected Attraction $attraction;
    protected Review $pendingReview;
    protected Review $approvedReview;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuarios
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'tourist']);

        // Crear departamento primero
        $department = Department::factory()->create();

        // Crear atractivo
        $this->attraction = Attraction::factory()->create([
            'department_id' => $department->id
        ]);

        // Crear reseñas de prueba
        $this->pendingReview = Review::factory()->create([
            'user_id' => $this->user->id,
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'status' => Review::STATUS_PENDING,
        ]);

        $this->approvedReview = Review::factory()->create([
            'user_id' => $this->user->id,
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'status' => Review::STATUS_APPROVED,
            'moderated_at' => now(),
            'moderated_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function admin_can_view_pending_reviews()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/reviews/pending');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'rating',
                            'title',
                            'comment',
                            'status',
                            'created_at',
                            'user',
                            'reviewable'
                        ]
                    ]
                ],
                'meta' => [
                    'total_pending',
                    'total_approved_today',
                    'total_rejected_today'
                ]
            ]);
    }

    /** @test */
    public function admin_can_approve_pending_review()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/admin/reviews/{$this->pendingReview->id}/approve", [
            'notes' => 'Reseña aprobada por cumplir con las políticas'
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Reseña aprobada exitosamente'
            ]);

        $this->pendingReview->refresh();
        $this->assertEquals(Review::STATUS_APPROVED, $this->pendingReview->status);
        $this->assertEquals($this->admin->id, $this->pendingReview->moderated_by);
        $this->assertNotNull($this->pendingReview->moderated_at);
    }

    /** @test */
    public function admin_can_reject_pending_review()
    {
        Sanctum::actingAs($this->admin);

        $reason = 'Contenido inapropiado que viola nuestras políticas';

        $response = $this->postJson("/api/admin/reviews/{$this->pendingReview->id}/reject", [
            'reason' => $reason,
            'notify_user' => true
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Reseña rechazada exitosamente'
            ]);

        $this->pendingReview->refresh();
        $this->assertEquals(Review::STATUS_REJECTED, $this->pendingReview->status);
        $this->assertEquals($reason, $this->pendingReview->moderation_notes);
        $this->assertEquals($this->admin->id, $this->pendingReview->moderated_by);
    }

    /** @test */
    public function non_admin_cannot_access_moderation_endpoints()
    {
        Sanctum::actingAs($this->user); // Usuario normal, no admin

        $response = $this->getJson('/api/admin/reviews/pending');

        $response->assertStatus(403);
    }
}