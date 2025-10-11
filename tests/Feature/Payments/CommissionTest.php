<?php

namespace Tests\Feature\Payments;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\Attraction;
use App\Models\Department;
use App\Features\Payments\Models\Commission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommissionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'tourist']);
    }

    public function test_admin_can_get_monthly_commission_report()
    {
        $this->createCommissionsForMonth(2024, 1, 3);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/commissions/monthly?year=2024&month=1');

        $response->assertOk()
            ->assertJsonStructure([
                'period' => ['year', 'month', 'month_name'],
                'summary' => ['total_commissions', 'total_bookings', 'average_commission'],
                'by_tour'
            ]);
    }

    public function test_admin_can_get_yearly_commission_report()
    {
        $this->createCommissionsForYear(2024, 5);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/commissions/yearly?year=2024');

        $response->assertOk()
            ->assertJsonStructure([
                'year',
                'total_commissions',
                'total_bookings',
                'monthly_breakdown' => [
                    '*' => ['month', 'month_name', 'total_amount', 'booking_count']
                ]
            ]);
    }

    public function test_admin_can_get_date_range_commission_report()
    {
        $this->createCommissionsForDateRange('2024-01-01', '2024-01-31', 4);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/commissions/date-range?start_date=2024-01-01&end_date=2024-01-31');

        $response->assertOk()
            ->assertJsonStructure([
                'period' => ['start_date', 'end_date'],
                'total_amount',
                'total_bookings',
                'commissions' => [
                    '*' => ['id', 'amount', 'rate', 'booking_id', 'tour_name', 'attraction_name', 'created_at']
                ]
            ]);
    }

    public function test_admin_can_get_pending_commissions()
    {
        Commission::factory()->count(3)->create(['status' => 'pending']);
        Commission::factory()->count(2)->create(['status' => 'paid']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/commissions/pending');

        $response->assertOk()
            ->assertJsonStructure([
                'pending_commissions' => [
                    '*' => ['id', 'amount', 'rate', 'booking_id', 'tour_name', 'attraction_name', 'user_name', 'created_at']
                ]
            ]);

        $this->assertCount(3, $response->json('pending_commissions'));
    }

    public function test_admin_can_mark_commissions_as_paid()
    {
        $commissions = Commission::factory()->count(3)->create(['status' => 'pending']);
        $commissionIds = $commissions->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/commissions/mark-paid', [
                'commission_ids' => $commissionIds
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'updated_count' => 3
            ]);

        foreach ($commissions as $commission) {
            $commission->refresh();
            $this->assertEquals('paid', $commission->status);
            $this->assertNotNull($commission->paid_at);
        }
    }

    public function test_admin_can_get_commission_rates()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/commissions/rates');

        $response->assertOk()
            ->assertJsonStructure([
                'commission_rates' => [
                    'default',
                    'premium',
                    'adventure',
                    'cultural',
                    'nature'
                ]
            ]);
    }

    public function test_validates_monthly_report_parameters()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/commissions/monthly?year=invalid&month=13');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year', 'month']);
    }

    public function test_validates_date_range_parameters()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/commissions/date-range?start_date=invalid&end_date=2024-01-01');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    public function test_non_admin_cannot_access_commission_reports()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/admin/commissions/monthly?year=2024&month=1');

        $response->assertStatus(403);
    }

    public function test_requires_authentication_for_commission_endpoints()
    {
        $response = $this->getJson('/api/admin/commissions/monthly?year=2024&month=1');

        $response->assertStatus(401);
    }

    private function createCommissionsForMonth(int $year, int $month, int $count): void
    {
        Commission::factory()->count($count)->create([
            'period_year' => $year,
            'period_month' => $month,
            'created_at' => now()->setYear($year)->setMonth($month)
        ]);
    }

    private function createCommissionsForYear(int $year, int $count): void
    {
        for ($month = 1; $month <= 12; $month++) {
            Commission::factory()->create([
                'period_year' => $year,
                'period_month' => $month,
                'created_at' => now()->setYear($year)->setMonth($month)
            ]);
        }
    }

    private function createCommissionsForDateRange(string $startDate, string $endDate, int $count): void
    {
        Commission::factory()->count($count)->create([
            'created_at' => now()->parse($startDate)->addDays(rand(0, 30))
        ]);
    }
}