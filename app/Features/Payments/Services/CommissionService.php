<?php

namespace App\Features\Payments\Services;

use App\Features\Payments\Models\Commission;
use App\Models\Booking;
use Carbon\Carbon;

class CommissionService
{
    private const DEFAULT_COMMISSION_RATE = 0.10; // 10%
    private const MIN_COMMISSION_RATE = 0.05; // 5%
    private const MAX_COMMISSION_RATE = 0.20; // 20%

    public function calculateCommission(float $amount, ?string $tourType = null): float
    {
        $rate = $this->getCommissionRate($tourType);
        return round($amount * $rate, 2);
    }

    public function getCommissionRate(?string $tourType = null): float
    {
        // Diferentes tasas según el tipo de tour
        return match($tourType) {
            'premium' => 0.15,
            'adventure' => 0.12,
            'cultural' => 0.08,
            'nature' => 0.10,
            default => self::DEFAULT_COMMISSION_RATE
        };
    }

    public function recordCommission(Booking $booking, float $commissionAmount): Commission
    {
        return Commission::create([
            'booking_id' => $booking->id,
            'tour_id' => $booking->tour_id,
            'amount' => $commissionAmount,
            'rate' => $commissionAmount / $booking->total_amount,
            'status' => 'pending',
            'period_month' => now()->month,
            'period_year' => now()->year
        ]);
    }

    public function getCommissionReport(int $year, int $month): array
    {
        $commissions = Commission::where('period_year', $year)
            ->where('period_month', $month)
            ->with(['booking.tour.attraction'])
            ->get();

        $totalCommissions = $commissions->sum('amount');
        $totalBookings = $commissions->count();
        $averageCommission = $totalBookings > 0 ? $totalCommissions / $totalBookings : 0;

        $byTour = $commissions->groupBy('tour_id')->map(function ($tourCommissions) {
            return [
                'tour_name' => $tourCommissions->first()->booking->tour->name ?? 'N/A',
                'total_amount' => $tourCommissions->sum('amount'),
                'booking_count' => $tourCommissions->count(),
                'average_rate' => $tourCommissions->avg('rate')
            ];
        });

        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'month_name' => Carbon::create($year, $month)->format('F')
            ],
            'summary' => [
                'total_commissions' => $totalCommissions,
                'total_bookings' => $totalBookings,
                'average_commission' => round($averageCommission, 2)
            ],
            'by_tour' => $byTour->toArray()
        ];
    }

    public function getYearlyCommissionReport(int $year): array
    {
        $commissions = Commission::where('period_year', $year)
            ->with(['booking.tour.attraction'])
            ->get();

        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthCommissions = $commissions->where('period_month', $month);
            $monthlyData[] = [
                'month' => $month,
                'month_name' => Carbon::create($year, $month)->format('F'),
                'total_amount' => $monthCommissions->sum('amount'),
                'booking_count' => $monthCommissions->count()
            ];
        }

        return [
            'year' => $year,
            'total_commissions' => $commissions->sum('amount'),
            'total_bookings' => $commissions->count(),
            'monthly_breakdown' => $monthlyData
        ];
    }

    public function markCommissionsAsPaid(array $commissionIds): int
    {
        return Commission::whereIn('id', $commissionIds)
            ->where('status', 'pending')
            ->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);
    }

    public function getPendingCommissions(): \Illuminate\Database\Eloquent\Collection
    {
        return Commission::where('status', 'pending')
            ->with(['booking.tour.attraction', 'booking.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCommissionsByDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $commissions = Commission::whereBetween('created_at', [$startDate, $endDate])
            ->with(['booking.tour.attraction'])
            ->get();

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ],
            'total_amount' => $commissions->sum('amount'),
            'total_bookings' => $commissions->count(),
            'commissions' => $commissions->map(function ($commission) {
                return [
                    'id' => $commission->id,
                    'amount' => $commission->amount,
                    'rate' => $commission->rate,
                    'booking_id' => $commission->booking_id,
                    'tour_name' => $commission->booking->tour->name ?? 'N/A',
                    'attraction_name' => $commission->booking->tour->attraction->name ?? 'N/A',
                    'created_at' => $commission->created_at->format('Y-m-d H:i:s')
                ];
            })
        ];
    }
}