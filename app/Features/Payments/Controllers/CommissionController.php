<?php

namespace App\Features\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Payments\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class CommissionController extends Controller
{
    public function __construct(
        private CommissionService $commissionService
    ) {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function getMonthlyReport(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'month' => 'required|integer|min:1|max:12'
        ]);

        $report = $this->commissionService->getCommissionReport(
            $request->year,
            $request->month
        );

        return response()->json($report);
    }

    public function getYearlyReport(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1)
        ]);

        $report = $this->commissionService->getYearlyCommissionReport($request->year);

        return response()->json($report);
    }

    public function getDateRangeReport(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $report = $this->commissionService->getCommissionsByDateRange($startDate, $endDate);

        return response()->json($report);
    }

    public function getPendingCommissions(): JsonResponse
    {
        $commissions = $this->commissionService->getPendingCommissions();

        return response()->json([
            'pending_commissions' => $commissions->map(function ($commission) {
                return [
                    'id' => $commission->id,
                    'amount' => $commission->amount,
                    'rate' => $commission->rate,
                    'booking_id' => $commission->booking_id,
                    'tour_name' => $commission->booking->tour->name ?? 'N/A',
                    'attraction_name' => $commission->booking->tour->attraction->name ?? 'N/A',
                    'user_name' => $commission->booking->user->name ?? 'N/A',
                    'created_at' => $commission->created_at->format('Y-m-d H:i:s')
                ];
            })
        ]);
    }

    public function markAsPaid(Request $request): JsonResponse
    {
        $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'integer|exists:commissions,id'
        ]);

        $updatedCount = $this->commissionService->markCommissionsAsPaid($request->commission_ids);

        return response()->json([
            'success' => true,
            'updated_count' => $updatedCount,
            'message' => "Se marcaron {$updatedCount} comisiones como pagadas"
        ]);
    }

    public function getCommissionRates(): JsonResponse
    {
        $rates = [
            'default' => $this->commissionService->getCommissionRate(),
            'premium' => $this->commissionService->getCommissionRate('premium'),
            'adventure' => $this->commissionService->getCommissionRate('adventure'),
            'cultural' => $this->commissionService->getCommissionRate('cultural'),
            'nature' => $this->commissionService->getCommissionRate('nature')
        ];

        return response()->json([
            'commission_rates' => $rates
        ]);
    }
}