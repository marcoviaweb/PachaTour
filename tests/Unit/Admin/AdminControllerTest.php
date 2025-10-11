<?php

namespace Tests\Unit\Admin;

use App\Features\Admin\Controllers\AdminController;
use App\Models\User;
use App\Models\Department;
use App\Models\Attraction;
use App\Models\Tour;
use App\Models\Booking;
use App\Models\Review;
use App\Features\Payments\Models\Commission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private AdminController $controller;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new AdminController();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->adminUser);
    }

    public function test_dashboard_returns_correct_view_and_data()
    {
        $response = $this->controller->dashboard();

        $this->assertEquals('admin.dashboard', $response->name());
        $this->assertArrayHasKey('metrics', $response->getData());
        $this->assertArrayHasKey('recentActivity', $response->getData());
        $this->assertArrayHasKey('chartData', $response->getData());
    }

    public function test_overview_returns_system_information()
    {
        $response = $this->controller->overview();

        $this->assertEquals(200, $response->getStatusCode());
        
        $data = $response->getData(true);
        
        $this->assertArrayHasKey('system_health', $data);
        $this->assertArrayHasKey('performance_metrics', $data);
        $this->assertArrayHasKey('storage_usage', $data);
    }

    public function test_dashboard_metrics_calculation()
    {
        // Create test data
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);
        $tour = Tour::factory()->create();
        $user = User::factory()->create(['role' => 'tourist']);
        
        // Create the many-to-many relationship
        $tour->attractions()->attach($attraction->id);
        
        // Create a tour schedule
        $tourSchedule = \App\Models\TourSchedule::factory()->create([
            'tour_id' => $tour->id
        ]);
        
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'tour_schedule_id' => $tourSchedule->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 100.00,
            'commission_amount' => 10.00
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'reviewable_type' => 'App\\Models\\Attraction',
            'reviewable_id' => $attraction->id,
            'rating' => 5,
            'status' => 'approved'
        ]);

        Commission::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 10.00,
            'rate' => 0.10
        ]);

        $response = $this->controller->dashboard();
        $metrics = $response->getData()['metrics'];

        $this->assertEquals(2, $metrics['total_users']); // admin + tourist
        $this->assertEquals(1, $metrics['total_attractions']);
        $this->assertEquals(1, $metrics['total_bookings']);
        $this->assertEquals(1, $metrics['confirmed_bookings']);
        $this->assertEquals(0, $metrics['pending_bookings']);
        $this->assertEquals(100.00, $metrics['total_revenue']);
        $this->assertEquals(10.00, $metrics['total_commissions']);
        $this->assertEquals(0, $metrics['pending_reviews']);
        $this->assertEquals(1, $metrics['approved_reviews']);
        $this->assertEquals(5.0, $metrics['average_rating']);
    }

    public function test_recent_activity_data_structure()
    {
        // Create test data
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);
        $tour = Tour::factory()->create();
        $user = User::factory()->create(['role' => 'tourist']);
        
        // Create the many-to-many relationship
        $tour->attractions()->attach($attraction->id);
        
        // Create a tour schedule
        $tourSchedule = \App\Models\TourSchedule::factory()->create([
            'tour_id' => $tour->id
        ]);
        
        Booking::factory()->create([
            'user_id' => $user->id,
            'tour_schedule_id' => $tourSchedule->id
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'reviewable_type' => 'App\\Models\\Attraction',
            'reviewable_id' => $attraction->id,
            'status' => 'pending'
        ]);

        $response = $this->controller->dashboard();
        $recentActivity = $response->getData()['recentActivity'];

        $this->assertArrayHasKey('recent_bookings', $recentActivity);
        $this->assertArrayHasKey('recent_users', $recentActivity);
        $this->assertArrayHasKey('recent_reviews', $recentActivity);

        $this->assertCount(1, $recentActivity['recent_bookings']);
        $this->assertCount(1, $recentActivity['recent_users']);
        $this->assertCount(1, $recentActivity['recent_reviews']);
    }

    public function test_chart_data_structure()
    {
        // Create test data
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);
        $tour = Tour::factory()->create();
        $user = User::factory()->create(['role' => 'tourist']);
        
        // Create the many-to-many relationship
        $tour->attractions()->attach($attraction->id);
        
        // Create a tour schedule
        $tourSchedule = \App\Models\TourSchedule::factory()->create([
            'tour_id' => $tour->id
        ]);
        
        Booking::factory()->create([
            'user_id' => $user->id,
            'tour_schedule_id' => $tourSchedule->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 100.00
        ]);

        $response = $this->controller->dashboard();
        $chartData = $response->getData()['chartData'];

        $this->assertArrayHasKey('daily_bookings', $chartData);
        $this->assertArrayHasKey('monthly_revenue', $chartData);
        $this->assertArrayHasKey('bookings_by_status', $chartData);
        $this->assertArrayHasKey('top_attractions', $chartData);
    }

    public function test_system_health_checks()
    {
        $response = $this->controller->overview();
        $data = $response->getData(true);
        $systemHealth = $data['system_health'];

        $this->assertIsBool($systemHealth['database_connection']);
        $this->assertIsBool($systemHealth['storage_writable']);
        $this->assertIsBool($systemHealth['cache_working']);

        // These should be true in a working test environment
        $this->assertTrue($systemHealth['database_connection']);
        $this->assertTrue($systemHealth['storage_writable']);
        $this->assertTrue($systemHealth['cache_working']);
    }

    public function test_performance_metrics_are_numeric()
    {
        $response = $this->controller->overview();
        $data = $response->getData(true);
        $performanceMetrics = $data['performance_metrics'];

        $this->assertIsNumeric($performanceMetrics['memory_usage']);
        $this->assertIsNumeric($performanceMetrics['memory_peak']);
        $this->assertIsNumeric($performanceMetrics['execution_time']);

        $this->assertGreaterThan(0, $performanceMetrics['memory_usage']);
        $this->assertGreaterThan(0, $performanceMetrics['memory_peak']);
        $this->assertGreaterThanOrEqual(0, $performanceMetrics['execution_time']); // Can be 0 if LARAVEL_START not defined
    }

    public function test_storage_usage_calculation()
    {
        $response = $this->controller->overview();
        $data = $response->getData(true);
        $storageUsage = $data['storage_usage'];

        $this->assertIsNumeric($storageUsage['total_size']);
        $this->assertIsNumeric($storageUsage['file_count']);
        $this->assertIsString($storageUsage['formatted_size']);

        $this->assertGreaterThanOrEqual(0, $storageUsage['total_size']);
        $this->assertGreaterThanOrEqual(0, $storageUsage['file_count']);
        $this->assertStringContainsString('B', $storageUsage['formatted_size']);
    }

    public function test_database_connection_check_handles_failure()
    {
        // Temporarily break the database connection
        config(['database.connections.testing.database' => 'nonexistent_database']);
        
        $response = $this->controller->overview();
        $data = $response->getData(true);
        
        // The check should handle the failure gracefully
        $this->assertIsBool($data['system_health']['database_connection']);
    }

    public function test_cache_check_handles_failure()
    {
        // Mock cache failure
        Cache::shouldReceive('put')->andThrow(new \Exception('Cache error'));
        Cache::shouldReceive('get')->andReturn(null);
        
        $response = $this->controller->overview();
        $data = $response->getData(true);
        
        // The check should handle the failure gracefully
        $this->assertIsBool($data['system_health']['cache_working']);
    }

    public function test_format_bytes_function()
    {
        $controller = new AdminController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('formatBytes');
        $method->setAccessible(true);

        // Test different byte sizes
        $this->assertEquals('0 B', $method->invoke($controller, 0));
        $this->assertEquals('1 B', $method->invoke($controller, 1));
        $this->assertEquals('1 KB', $method->invoke($controller, 1024));
        $this->assertEquals('1 MB', $method->invoke($controller, 1024 * 1024));
        $this->assertEquals('1 GB', $method->invoke($controller, 1024 * 1024 * 1024));
        
        // Test precision
        $this->assertEquals('1.5 KB', $method->invoke($controller, 1536));
        $this->assertEquals('2.25 MB', $method->invoke($controller, 2359296));
    }

    public function test_empty_data_handling()
    {
        // Test dashboard with no data
        $response = $this->controller->dashboard();
        $metrics = $response->getData()['metrics'];

        $this->assertEquals(1, $metrics['total_users']); // Only admin user
        $this->assertEquals(0, $metrics['total_attractions']);
        $this->assertEquals(0, $metrics['total_bookings']);
        $this->assertEquals(0, $metrics['confirmed_bookings']);
        $this->assertEquals(0, $metrics['pending_bookings']);
        $this->assertEquals(0, $metrics['total_revenue']);
        $this->assertEquals(0, $metrics['total_commissions']);
        $this->assertEquals(0, $metrics['pending_reviews']);
        $this->assertEquals(0, $metrics['approved_reviews']);
        $this->assertNull($metrics['average_rating']);
    }
}