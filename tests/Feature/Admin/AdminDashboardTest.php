<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Department;
use App\Models\Attraction;
use App\Models\Tour;
use App\Models\Booking;
use App\Models\Review;
use App\Features\Payments\Models\Commission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'tourist']);
    }

    public function test_admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas(['metrics', 'recentActivity', 'chartData']);
    }

    public function test_non_admin_cannot_access_dashboard()
    {
        $response = $this->actingAs($this->regularUser)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_dashboard_displays_correct_metrics()
    {
        // Create test data
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create([
            'department_id' => $department->id,
            'is_active' => true
        ]);
        $tour = Tour::factory()->create();
        $tourSchedule = \App\Models\TourSchedule::factory()->create(['tour_id' => $tour->id]);
        
        // Create the many-to-many relationship
        $tour->attractions()->attach($attraction->id);
        
        // Create bookings with different statuses
        Booking::factory()->create([
            'tour_schedule_id' => $tourSchedule->id,
            'user_id' => $this->regularUser->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 100.00,
            'commission_amount' => 10.00
        ]);
        
        Booking::factory()->create([
            'tour_schedule_id' => $tourSchedule->id,
            'user_id' => $this->regularUser->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'total_amount' => 150.00,
            'commission_amount' => 15.00
        ]);

        // Create reviews
        Review::factory()->forAttraction($attraction)->create([
            'user_id' => $this->regularUser->id,
            'rating' => 5,
            'status' => 'approved'
        ]);

        Review::factory()->forAttraction($attraction)->create([
            'user_id' => $this->regularUser->id,
            'rating' => 4,
            'status' => 'pending'
        ]);

        // Create commission
        $booking = Booking::first();
        Commission::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 10.00,
            'rate' => 0.10
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        
        $metrics = $response->viewData('metrics');
        
        $this->assertEquals(2, $metrics['total_users']); // admin + regular user
        $this->assertEquals(1, $metrics['total_attractions']);
        $this->assertEquals(2, $metrics['total_bookings']);
        $this->assertEquals(1, $metrics['confirmed_bookings']);
        $this->assertEquals(1, $metrics['pending_bookings']);
        $this->assertEquals(100.00, $metrics['total_revenue']);
        $this->assertEquals(10.00, $metrics['total_commissions']);
        $this->assertEquals(1, $metrics['pending_reviews']);
        $this->assertEquals(1, $metrics['approved_reviews']);
        $this->assertEquals(5.0, $metrics['average_rating']);
    }

    public function test_dashboard_shows_recent_activity()
    {
        // Create test data
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create([
            'department_id' => $department->id,
            'is_active' => true
        ]);
        $tour = Tour::factory()->create();
        $tourSchedule = \App\Models\TourSchedule::factory()->create(['tour_id' => $tour->id]);
        
        // Create the many-to-many relationship
        $tour->attractions()->attach($attraction->id);
        
        $booking = Booking::factory()->create([
            'tour_schedule_id' => $tourSchedule->id,
            'user_id' => $this->regularUser->id,
            'status' => 'confirmed'
        ]);

        $review = Review::factory()->forAttraction($attraction)->create([
            'user_id' => $this->regularUser->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        
        $recentActivity = $response->viewData('recentActivity');
        
        $this->assertCount(1, $recentActivity['recent_bookings']);
        $this->assertCount(1, $recentActivity['recent_users']); // regular user
        $this->assertCount(1, $recentActivity['recent_reviews']);
        
        $this->assertEquals($booking->id, $recentActivity['recent_bookings']->first()->id);
        $this->assertEquals($this->regularUser->id, $recentActivity['recent_users']->first()->id);
        $this->assertEquals($review->id, $recentActivity['recent_reviews']->first()->id);
    }

    public function test_dashboard_provides_chart_data()
    {
        // Create test data for charts
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create([
            'department_id' => $department->id,
            'is_active' => true
        ]);
        $tour = Tour::factory()->create();
        $tourSchedule = \App\Models\TourSchedule::factory()->create(['tour_id' => $tour->id]);
        
        // Create the many-to-many relationship
        $tour->attractions()->attach($attraction->id);
        
        Booking::factory()->create([
            'tour_schedule_id' => $tourSchedule->id,
            'user_id' => $this->regularUser->id,
            'status' => 'confirmed',
            'total_amount' => 100.00
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        
        $chartData = $response->viewData('chartData');
        
        $this->assertArrayHasKey('daily_bookings', $chartData);
        $this->assertArrayHasKey('monthly_revenue', $chartData);
        $this->assertArrayHasKey('bookings_by_status', $chartData);
        $this->assertArrayHasKey('top_attractions', $chartData);
    }

    public function test_admin_can_access_system_overview()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/overview');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'system_health' => [
                'database_connection',
                'storage_writable',
                'cache_working'
            ],
            'performance_metrics' => [
                'memory_usage',
                'memory_peak',
                'execution_time'
            ],
            'storage_usage' => [
                'total_size',
                'file_count',
                'formatted_size'
            ]
        ]);
    }

    public function test_system_health_checks_work()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/overview');

        $response->assertStatus(200);
        
        $data = $response->json();
        
        $this->assertTrue($data['system_health']['database_connection']);
        $this->assertTrue($data['system_health']['storage_writable']);
        $this->assertTrue($data['system_health']['cache_working']);
    }

    public function test_performance_metrics_are_provided()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/overview');

        $response->assertStatus(200);
        
        $data = $response->json();
        
        $this->assertIsNumeric($data['performance_metrics']['memory_usage']);
        $this->assertIsNumeric($data['performance_metrics']['memory_peak']);
        $this->assertIsNumeric($data['performance_metrics']['execution_time']);
        
        $this->assertGreaterThan(0, $data['performance_metrics']['memory_usage']);
        $this->assertGreaterThan(0, $data['performance_metrics']['memory_peak']);
        $this->assertGreaterThanOrEqual(0, $data['performance_metrics']['execution_time']); // Can be 0 if LARAVEL_START not defined
    }

    public function test_storage_usage_is_calculated()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/overview');

        $response->assertStatus(200);
        
        $data = $response->json();
        
        $this->assertIsNumeric($data['storage_usage']['total_size']);
        $this->assertIsNumeric($data['storage_usage']['file_count']);
        $this->assertIsString($data['storage_usage']['formatted_size']);
        
        $this->assertGreaterThanOrEqual(0, $data['storage_usage']['total_size']);
        $this->assertGreaterThanOrEqual(0, $data['storage_usage']['file_count']);
    }
}