<?php

namespace Tests\Unit\Admin;

use App\Features\Admin\Services\UserActivityService;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserActivityService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new UserActivityService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_log_user_activity()
    {
        $activity = $this->service->logActivity(
            $this->user->id,
            'test_action',
            'Test description',
            ['key' => 'value']
        );

        $this->assertInstanceOf(UserActivity::class, $activity);
        $this->assertEquals($this->user->id, $activity->user_id);
        $this->assertEquals('test_action', $activity->action);
        $this->assertEquals('Test description', $activity->description);
        $this->assertEquals(['key' => 'value'], $activity->metadata);
        $this->assertNotNull($activity->performed_at);

        $this->assertDatabaseHas('user_activities', [
            'user_id' => $this->user->id,
            'action' => 'test_action',
            'description' => 'Test description'
        ]);
    }

    /** @test */
    public function it_can_get_user_activity_with_limit()
    {
        UserActivity::factory()->count(25)->create(['user_id' => $this->user->id]);

        $activities = $this->service->getUserActivity($this->user->id, 10);

        $this->assertCount(10, $activities);
        
        // Should be ordered by performed_at desc
        $timestamps = $activities->pluck('performed_at');
        $sortedTimestamps = $timestamps->sortDesc();
        $this->assertEquals($sortedTimestamps->values(), $timestamps->values());
    }

    /** @test */
    public function it_can_get_user_activity_statistics()
    {
        // Create different types of activities
        UserActivity::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'action' => 'login'
        ]);
        
        UserActivity::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'action' => 'booking_created'
        ]);

        UserActivity::factory()->create([
            'user_id' => $this->user->id,
            'action' => 'login',
            'performed_at' => now()->subDays(35) // Old activity
        ]);

        $stats = $this->service->getUserActivityStats($this->user->id);

        $this->assertArrayHasKey('actions_by_type', $stats);
        $this->assertArrayHasKey('recent_activity_count', $stats);
        $this->assertArrayHasKey('last_activity', $stats);

        $this->assertEquals(4, $stats['actions_by_type']['login']); // 3 + 1 old
        $this->assertEquals(2, $stats['actions_by_type']['booking_created']);
        $this->assertEquals(5, $stats['recent_activity_count']); // Only recent activities
    }

    /** @test */
    public function it_can_get_system_activity_statistics()
    {
        $user2 = User::factory()->create();

        // Create activities for different users
        UserActivity::factory()->count(3)->create(['user_id' => $this->user->id]);
        UserActivity::factory()->count(2)->create(['user_id' => $user2->id]);
        
        // Create activities for today
        UserActivity::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'performed_at' => now()
        ]);

        $stats = $this->service->getSystemActivityStats();

        $this->assertArrayHasKey('total_activities', $stats);
        $this->assertArrayHasKey('activities_today', $stats);
        $this->assertArrayHasKey('activities_this_week', $stats);
        $this->assertArrayHasKey('activities_this_month', $stats);
        $this->assertArrayHasKey('most_common_actions', $stats);
        $this->assertArrayHasKey('active_users_today', $stats);

        $this->assertEquals(7, $stats['total_activities']); // 3 + 2 + 2
        $this->assertEquals(2, $stats['activities_today']);
        $this->assertEquals(1, $stats['active_users_today']); // Only user1 has activities today
    }

    /** @test */
    public function it_can_clean_old_activity_logs()
    {
        // Create old activities
        UserActivity::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'performed_at' => now()->subDays(100)
        ]);

        // Create recent activities
        UserActivity::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'performed_at' => now()->subDays(30)
        ]);

        $deletedCount = $this->service->cleanOldLogs(90);

        $this->assertEquals(5, $deletedCount);
        $this->assertEquals(3, UserActivity::count());
    }

    /** @test */
    public function it_can_log_authentication_events()
    {
        $activity = $this->service->logAuthEvent($this->user->id, 'login', ['method' => 'email']);

        $this->assertEquals('login', $activity->action);
        $this->assertEquals('Usuario inició sesión', $activity->description);
        $this->assertEquals(['method' => 'email'], $activity->metadata);
    }

    /** @test */
    public function it_can_log_booking_events()
    {
        $bookingId = 123;
        $activity = $this->service->logBookingEvent(
            $this->user->id,
            'booking_created',
            $bookingId,
            ['tour_id' => 456]
        );

        $this->assertEquals('booking_created', $activity->action);
        $this->assertEquals('Nueva reserva creada', $activity->description);
        $this->assertEquals([
            'booking_id' => $bookingId,
            'tour_id' => 456
        ], $activity->metadata);
    }

    /** @test */
    public function it_can_log_review_events()
    {
        $reviewId = 789;
        $activity = $this->service->logReviewEvent(
            $this->user->id,
            'review_created',
            $reviewId,
            ['attraction_id' => 101]
        );

        $this->assertEquals('review_created', $activity->action);
        $this->assertEquals('Nueva valoración creada', $activity->description);
        $this->assertEquals([
            'review_id' => $reviewId,
            'attraction_id' => 101
        ], $activity->metadata);
    }

    /** @test */
    public function it_handles_unknown_auth_events()
    {
        $activity = $this->service->logAuthEvent($this->user->id, 'unknown_event');

        $this->assertEquals('unknown_event', $activity->action);
        $this->assertEquals('unknown_event', $activity->description); // Falls back to action name
    }

    /** @test */
    public function it_captures_request_information()
    {
        $this->withHeaders([
            'User-Agent' => 'Test Browser',
            'X-Forwarded-For' => '192.168.1.1'
        ]);

        $activity = $this->service->logActivity(
            $this->user->id,
            'test_action',
            'Test description'
        );

        $this->assertNotNull($activity->ip_address);
        $this->assertNotNull($activity->user_agent);
    }
}