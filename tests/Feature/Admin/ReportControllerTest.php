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
use Carbon\Carbon;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;
    private Department $department;
    private Attraction $attraction;
    private Tour $tour;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'tourist']);
        
        $this->department = Department::factory()->create();
        $this->attraction = Attraction::factory()->create(['department_id' => $this->department->id]);
        $this->tour = Tour::factory()->create(['attraction_id' => $this->attraction->id]);
    }

    public function test_admin_can_access_booking_reports()
    {
        Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 100.00
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/bookings');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'bookings' => [
                'data',
                'current_page',
                'total'
            ],
            'summary' => [
                'total_bookings',
                'total_revenue',
                'average_booking_value',
                'bookings_by_status'
            ]
        ]);
    }

    public function test_booking_reports_can_be_filtered_by_date()
    {
        // Create bookings on different dates
        Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'created_at' => Carbon::now()->subDays(5),
            'status' => 'confirmed'
        ]);

        Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'created_at' => Carbon::now()->subDays(15),
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/bookings?' . http_build_query([
                'start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d')
            ]));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(1, $data['summary']['total_bookings']);
    }

    public function test_booking_reports_can_be_filtered_by_status()
    {
        Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'status' => 'confirmed'
        ]);

        Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/bookings?status=confirmed');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(1, $data['summary']['total_bookings']);
    }

    public function test_booking_reports_can_be_filtered_by_department()
    {
        $otherDepartment = Department::factory()->create();
        $otherAttraction = Attraction::factory()->create(['department_id' => $otherDepartment->id]);
        $otherTour = Tour::factory()->create(['attraction_id' => $otherAttraction->id]);

        Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'status' => 'confirmed'
        ]);

        Booking::factory()->create([
            'tour_id' => $otherTour->id,
            'user_id' => $this->regularUser->id,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/bookings?department_id=' . $this->department->id);

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(1, $data['summary']['total_bookings']);
    }

    public function test_admin_can_access_revenue_reports()
    {
        Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'payment_status' => 'paid',
            'total_amount' => 100.00,
            'commission_amount' => 10.00
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/revenue');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'revenue_data',
            'revenue_by_department',
            'top_attractions',
            'summary' => [
                'total_revenue',
                'total_commission',
                'total_bookings',
                'average_booking_value'
            ]
        ]);
    }

    public function test_revenue_reports_support_different_periods()
    {
        Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'payment_status' => 'paid',
            'total_amount' => 100.00
        ]);

        $periods = ['daily', 'weekly', 'monthly', 'yearly'];

        foreach ($periods as $period) {
            $response = $this->actingAs($this->adminUser)
                ->get('/admin/reports/revenue?period=' . $period);

            $response->assertStatus(200);
            $this->assertIsArray($response->json('revenue_data'));
        }
    }

    public function test_admin_can_access_user_reports()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'users' => [
                'data',
                'current_page',
                'total'
            ],
            'registration_trends',
            'users_by_role',
            'active_users'
        ]);
    }

    public function test_user_reports_can_be_filtered_by_role()
    {
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'tourist']);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/users?role=tourist');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertGreaterThanOrEqual(1, count($data['users']['data']));
        
        foreach ($data['users']['data'] as $user) {
            $this->assertEquals('tourist', $user['role']);
        }
    }

    public function test_admin_can_access_attraction_reports()
    {
        Review::factory()->create([
            'user_id' => $this->regularUser->id,
            'attraction_id' => $this->attraction->id,
            'rating' => 5,
            'status' => 'approved'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/attractions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'attractions',
            'summary' => [
                'total_attractions',
                'average_rating',
                'total_reviews'
            ]
        ]);
    }

    public function test_attraction_reports_can_be_filtered_by_department()
    {
        $otherDepartment = Department::factory()->create();
        $otherAttraction = Attraction::factory()->create(['department_id' => $otherDepartment->id]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/attractions?department_id=' . $this->department->id);

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertCount(1, $data['attractions']);
        $this->assertEquals($this->attraction->id, $data['attractions'][0]['id']);
    }

    public function test_admin_can_access_commission_reports()
    {
        $booking = Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'payment_status' => 'paid'
        ]);

        Commission::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 10.00,
            'rate' => 0.10
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/commissions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'commissions' => [
                'data',
                'current_page',
                'total'
            ],
            'commission_trends',
            'summary' => [
                'total_commission',
                'average_commission',
                'commission_count'
            ]
        ]);
    }

    public function test_commission_reports_can_be_filtered_by_date()
    {
        $booking = Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'payment_status' => 'paid'
        ]);

        Commission::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 10.00,
            'rate' => 0.10,
            'created_at' => Carbon::now()->subDays(5)
        ]);

        Commission::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 15.00,
            'rate' => 0.15,
            'created_at' => Carbon::now()->subDays(15)
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/commissions?' . http_build_query([
                'start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d')
            ]));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(1, $data['summary']['commission_count']);
        $this->assertEquals(10.00, $data['summary']['total_commission']);
    }

    public function test_admin_can_export_reports_as_json()
    {
        Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/export?type=bookings&format=json');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
    }

    public function test_admin_can_export_reports_as_csv()
    {
        Booking::factory()->create([
            'tour_id' => $this->tour->id,
            'user_id' => $this->regularUser->id,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/export?type=bookings&format=csv');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition', function ($value) {
            return str_contains($value, 'bookings_report_');
        });
    }

    public function test_non_admin_cannot_access_reports()
    {
        $endpoints = [
            '/admin/reports/bookings',
            '/admin/reports/revenue',
            '/admin/reports/users',
            '/admin/reports/attractions',
            '/admin/reports/commissions',
            '/admin/reports/export?type=bookings'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($this->regularUser)
                ->get($endpoint);

            $response->assertStatus(403);
        }
    }

    public function test_unauthenticated_user_cannot_access_reports()
    {
        $endpoints = [
            '/admin/reports/bookings',
            '/admin/reports/revenue',
            '/admin/reports/users',
            '/admin/reports/attractions',
            '/admin/reports/commissions'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->get($endpoint);
            $response->assertStatus(302);
            $response->assertRedirect('/login');
        }
    }

    public function test_report_validation_works()
    {
        // Test invalid date range
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/bookings?start_date=2024-12-31&end_date=2024-01-01');

        $response->assertStatus(422);

        // Test invalid status
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/bookings?status=invalid_status');

        $response->assertStatus(422);

        // Test invalid department
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/bookings?department_id=999999');

        $response->assertStatus(422);
    }

    public function test_export_validation_works()
    {
        // Test invalid export type
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/export?type=invalid_type');

        $response->assertStatus(422);

        // Test invalid format
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/reports/export?type=bookings&format=invalid_format');

        $response->assertStatus(422);
    }
}