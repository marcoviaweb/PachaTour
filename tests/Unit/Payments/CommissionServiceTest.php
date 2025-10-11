<?php

namespace Tests\Unit\Payments;

use Tests\TestCase;
use App\Features\Payments\Services\CommissionService;
use App\Features\Payments\Models\Commission;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\Attraction;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private CommissionService $commissionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commissionService = new CommissionService();
    }

    public function test_calculates_default_commission_rate()
    {
        $amount = 100.00;
        $commission = $this->commissionService->calculateCommission($amount);
        
        $this->assertEquals(10.00, $commission); // 10% default rate
    }

    public function test_calculates_commission_by_tour_type()
    {
        $amount = 100.00;
        
        $premiumCommission = $this->commissionService->calculateCommission($amount, 'premium');
        $adventureCommission = $this->commissionService->calculateCommission($amount, 'adventure');
        $culturalCommission = $this->commissionService->calculateCommission($amount, 'cultural');
        $natureCommission = $this->commissionService->calculateCommission($amount, 'nature');
        
        $this->assertEquals(15.00, $premiumCommission); // 15%
        $this->assertEquals(12.00, $adventureCommission); // 12%
        $this->assertEquals(8.00, $culturalCommission); // 8%
        $this->assertEquals(10.00, $natureCommission); // 10%
    }

    public function test_gets_commission_rate_by_tour_type()
    {
        $this->assertEquals(0.10, $this->commissionService->getCommissionRate());
        $this->assertEquals(0.15, $this->commissionService->getCommissionRate('premium'));
        $this->assertEquals(0.12, $this->commissionService->getCommissionRate('adventure'));
        $this->assertEquals(0.08, $this->commissionService->getCommissionRate('cultural'));
        $this->assertEquals(0.10, $this->commissionService->getCommissionRate('nature'));
    }

    public function test_records_commission()
    {
        $booking = $this->createBooking();
        $commissionAmount = 15.00;
        
        $commission = $this->commissionService->recordCommission($booking, $commissionAmount);
        
        $this->assertInstanceOf(Commission::class, $commission);
        $this->assertEquals($booking->id, $commission->booking_id);
        $this->assertEquals($booking->tour_id, $commission->tour_id);
        $this->assertEquals($commissionAmount, $commission->amount);
        $this->assertEquals('pending', $commission->status);
        $this->assertEquals(now()->month, $commission->period_month);
        $this->assertEquals(now()->year, $commission->period_year);
    }

    public function test_generates_monthly_commission_report()
    {
        $year = 2024;
        $month = 1;
        
        // Create commissions for the month
        $commissions = Commission::factory()->count(3)->create([
            'period_year' => $year,
            'period_month' => $month,
            'amount' => 10.00
        ]);
        
        $report = $this->commissionService->getCommissionReport($year, $month);
        
        $this->assertEquals($year, $report['period']['year']);
        $this->assertEquals($month, $report['period']['month']);
        $this->assertEquals(30.00, $report['summary']['total_commissions']);
        $this->assertEquals(3, $report['summary']['total_bookings']);
        $this->assertEquals(10.00, $report['summary']['average_commission']);
    }

    public function test_generates_yearly_commission_report()
    {
        $year = 2024;
        
        // Create commissions for different months
        for ($month = 1; $month <= 3; $month++) {
            Commission::factory()->count(2)->create([
                'period_year' => $year,
                'period_month' => $month,
                'amount' => 10.00
            ]);
        }
        
        $report = $this->commissionService->getYearlyCommissionReport($year);
        
        $this->assertEquals($year, $report['year']);
        $this->assertEquals(60.00, $report['total_commissions']); // 6 commissions * 10.00
        $this->assertEquals(6, $report['total_bookings']);
        $this->assertCount(12, $report['monthly_breakdown']); // All 12 months
    }

    public function test_marks_commissions_as_paid()
    {
        $commissions = Commission::factory()->count(3)->create(['status' => 'pending']);
        $commissionIds = $commissions->pluck('id')->toArray();
        
        $updatedCount = $this->commissionService->markCommissionsAsPaid($commissionIds);
        
        $this->assertEquals(3, $updatedCount);
        
        foreach ($commissions as $commission) {
            $commission->refresh();
            $this->assertEquals('paid', $commission->status);
            $this->assertNotNull($commission->paid_at);
        }
    }

    public function test_gets_pending_commissions()
    {
        Commission::factory()->count(3)->create(['status' => 'pending']);
        Commission::factory()->count(2)->create(['status' => 'paid']);
        
        $pendingCommissions = $this->commissionService->getPendingCommissions();
        
        $this->assertCount(3, $pendingCommissions);
        
        foreach ($pendingCommissions as $commission) {
            $this->assertEquals('pending', $commission->status);
        }
    }

    public function test_gets_commissions_by_date_range()
    {
        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-01-31');
        
        // Create commissions within range
        Commission::factory()->count(2)->create([
            'created_at' => $startDate->addDays(5),
            'amount' => 15.00
        ]);
        
        // Create commission outside range
        Commission::factory()->create([
            'created_at' => $startDate->subDays(10),
            'amount' => 20.00
        ]);
        
        $report = $this->commissionService->getCommissionsByDateRange(
            Carbon::parse('2024-01-01'),
            Carbon::parse('2024-01-31')
        );
        
        $this->assertEquals(30.00, $report['total_amount']); // Only commissions within range
        $this->assertEquals(2, $report['total_bookings']);
        $this->assertCount(2, $report['commissions']);
    }

    public function test_only_marks_pending_commissions_as_paid()
    {
        $pendingCommissions = Commission::factory()->count(2)->create(['status' => 'pending']);
        $paidCommissions = Commission::factory()->count(1)->create(['status' => 'paid']);
        
        $allIds = $pendingCommissions->concat($paidCommissions)->pluck('id')->toArray();
        
        $updatedCount = $this->commissionService->markCommissionsAsPaid($allIds);
        
        $this->assertEquals(2, $updatedCount); // Only pending ones were updated
    }

    private function createBooking(): Booking
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();
        $attraction = Attraction::factory()->create(['department_id' => $department->id]);
        $tour = Tour::factory()->create(['attraction_id' => $attraction->id]);
        
        return Booking::factory()->create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'total_amount' => 100.00
        ]);
    }
}