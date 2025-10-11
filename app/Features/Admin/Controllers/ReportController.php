<?php

namespace App\Features\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attraction;
use App\Models\Booking;
use App\Models\User;
use App\Models\Review;
use App\Models\Department;
use App\Features\Payments\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Generate booking reports
     */
    public function bookings(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:pending,confirmed,cancelled,completed',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $query = Booking::with(['user', 'tour.attraction.department']);

        // Apply date filters
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Apply status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Apply department filter
        if ($request->department_id) {
            $query->whereHas('tourSchedule.tour.attractions', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(50);

        // Calculate summary statistics
        $summary = [
            'total_bookings' => $query->count(),
            'total_revenue' => $query->where('payment_status', 'paid')->sum('total_amount'),
            'average_booking_value' => $query->where('payment_status', 'paid')->avg('total_amount'),
            'bookings_by_status' => $query->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];

        return response()->json([
            'bookings' => $bookings,
            'summary' => $summary,
        ]);
    }

    /**
     * Generate revenue reports
     */
    public function revenue(Request $request)
    {
        $request->validate([
            'period' => 'nullable|in:daily,weekly,monthly,yearly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $period = $request->period ?? 'monthly';
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subYear();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $dateFormat = $this->getDateFormat($period);
        $groupBy = $this->getGroupBy($period);

        $revenueData = Booking::select(
                DB::raw("$dateFormat as period"),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(commission_amount) as total_commission'),
                DB::raw('COUNT(*) as booking_count')
            )
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw($groupBy))
            ->orderBy('period')
            ->get();

        // Revenue by department - simplified approach
        $revenueByDepartment = Department::select(
                'departments.name',
                DB::raw('COALESCE(SUM(bookings.total_amount), 0) as revenue'),
                DB::raw('COUNT(bookings.id) as booking_count')
            )
            ->leftJoin('attractions', 'departments.id', '=', 'attractions.department_id')
            ->leftJoin('tour_attraction', 'attractions.id', '=', 'tour_attraction.attraction_id')
            ->leftJoin('tours', 'tour_attraction.tour_id', '=', 'tours.id')
            ->leftJoin('tour_schedules', 'tours.id', '=', 'tour_schedules.tour_id')
            ->leftJoin('bookings', function ($join) use ($startDate, $endDate) {
                $join->on('tour_schedules.id', '=', 'bookings.tour_schedule_id')
                     ->where('bookings.payment_status', 'paid')
                     ->whereBetween('bookings.created_at', [$startDate, $endDate]);
            })
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('revenue')
            ->get();

        // Top performing attractions - simplified approach
        $topAttractions = Attraction::select(
                'attractions.name',
                'departments.name as department_name',
                DB::raw('COALESCE(SUM(bookings.total_amount), 0) as revenue'),
                DB::raw('COUNT(bookings.id) as booking_count'),
                DB::raw('COALESCE(AVG(reviews.rating), 0) as average_rating')
            )
            ->leftJoin('departments', 'attractions.department_id', '=', 'departments.id')
            ->leftJoin('tour_attraction', 'attractions.id', '=', 'tour_attraction.attraction_id')
            ->leftJoin('tours', 'tour_attraction.tour_id', '=', 'tours.id')
            ->leftJoin('tour_schedules', 'tours.id', '=', 'tour_schedules.tour_id')
            ->leftJoin('bookings', function ($join) use ($startDate, $endDate) {
                $join->on('tour_schedules.id', '=', 'bookings.tour_schedule_id')
                     ->where('bookings.payment_status', 'paid')
                     ->whereBetween('bookings.created_at', [$startDate, $endDate]);
            })
            ->leftJoin('reviews', function ($join) {
                $join->on('attractions.id', '=', 'reviews.reviewable_id')
                     ->where('reviews.reviewable_type', 'App\\Models\\Attraction')
                     ->where('reviews.status', 'approved');
            })
            ->groupBy('attractions.id', 'attractions.name', 'departments.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return response()->json([
            'revenue_data' => $revenueData,
            'revenue_by_department' => $revenueByDepartment,
            'top_attractions' => $topAttractions,
            'summary' => [
                'total_revenue' => $revenueData->sum('total_revenue'),
                'total_commission' => $revenueData->sum('total_commission'),
                'total_bookings' => $revenueData->sum('booking_count'),
                'average_booking_value' => $revenueData->sum('booking_count') > 0 
                    ? $revenueData->sum('total_revenue') / $revenueData->sum('booking_count') 
                    : 0,
            ],
        ]);
    }

    /**
     * Generate user reports
     */
    public function users(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'role' => 'nullable|in:visitor,tourist,admin',
        ]);

        $query = User::query();

        // Apply date filters
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Apply role filter
        if ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->withCount(['bookings', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // User registration trends
        $registrationTrends = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as registrations')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Users by role
        $usersByRole = User::select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role');

        // Most active users
        $activeUsers = User::select('users.*')
            ->withCount(['bookings', 'reviews'])
            ->orderByDesc('bookings_count')
            ->limit(10)
            ->get();

        return response()->json([
            'users' => $users,
            'registration_trends' => $registrationTrends,
            'users_by_role' => $usersByRole,
            'active_users' => $activeUsers,
        ]);
    }

    /**
     * Generate attraction performance reports
     */
    public function attractions(Request $request)
    {
        $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'tourism_type' => 'nullable|string',
        ]);

        $query = Attraction::with(['department', 'tours', 'reviews']);

        // Apply filters
        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->tourism_type) {
            $query->where('tourism_type', $request->tourism_type);
        }

        $attractions = $query->withCount(['reviews'])
            ->withAvg('reviews as average_rating', 'rating')
            ->get();

        // Add booking statistics
        $attractions->each(function ($attraction) {
            $bookingStats = Booking::whereHas('tourSchedule.tour.attractions', function ($q) use ($attraction) {
                $q->where('attractions.id', $attraction->id);
            })->selectRaw('
                COUNT(*) as total_bookings,
                SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as total_revenue,
                AVG(CASE WHEN payment_status = "paid" THEN total_amount ELSE NULL END) as average_booking_value
            ')->first();

            $attraction->booking_stats = $bookingStats ?: (object)[
                'total_bookings' => 0,
                'total_revenue' => 0,
                'average_booking_value' => 0
            ];
        });

        return response()->json([
            'attractions' => $attractions,
            'summary' => [
                'total_attractions' => $attractions->count(),
                'average_rating' => $attractions->avg('average_rating'),
                'total_reviews' => $attractions->sum('reviews_count'),
            ],
        ]);
    }

    /**
     * Generate commission reports
     */
    public function commissions(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Commission::with(['booking.tour.attraction']);

        // Apply date filters
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Commission trends
        $commissionTrends = Commission::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total_commission'),
                DB::raw('COUNT(*) as commission_count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'commissions' => $commissions,
            'commission_trends' => $commissionTrends,
            'summary' => [
                'total_commission' => $query->sum('amount'),
                'average_commission' => $query->avg('amount'),
                'commission_count' => $query->count(),
            ],
        ]);
    }

    /**
     * Export report data to CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:bookings,revenue,users,attractions,commissions',
            'format' => 'nullable|in:csv,json',
        ]);

        $format = $request->format ?? 'csv';
        $type = $request->type;

        // Generate the report data based on type
        $data = $this->getExportData($type, $request);

        if ($format === 'csv') {
            return $this->exportToCsv($data, $type);
        }

        return response()->json($data);
    }

    /**
     * Get data for export based on report type
     */
    private function getExportData($type, $request)
    {
        switch ($type) {
            case 'bookings':
                return $this->bookings($request)->getData();
            case 'revenue':
                return $this->revenue($request)->getData();
            case 'users':
                return $this->users($request)->getData();
            case 'attractions':
                return $this->attractions($request)->getData();
            case 'commissions':
                return $this->commissions($request)->getData();
            default:
                return [];
        }
    }

    /**
     * Export data to CSV format
     */
    private function exportToCsv($data, $type)
    {
        $filename = $type . '_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data, $type) {
            $file = fopen('php://output', 'w');
            
            // Write CSV headers based on report type
            $this->writeCsvHeaders($file, $type);
            
            // Write data rows
            $this->writeCsvData($file, $data, $type);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Write CSV headers based on report type
     */
    private function writeCsvHeaders($file, $type)
    {
        $headers = [
            'bookings' => ['ID', 'User', 'Tour', 'Date', 'Status', 'Amount', 'Created At'],
            'revenue' => ['Period', 'Revenue', 'Commission', 'Bookings'],
            'users' => ['ID', 'Name', 'Email', 'Role', 'Bookings', 'Reviews', 'Created At'],
            'attractions' => ['ID', 'Name', 'Department', 'Rating', 'Reviews', 'Revenue'],
            'commissions' => ['ID', 'Booking ID', 'Amount', 'Rate', 'Created At'],
        ];

        fputcsv($file, $headers[$type] ?? []);
    }

    /**
     * Write CSV data based on report type
     */
    private function writeCsvData($file, $data, $type)
    {
        // Implementation would depend on the specific data structure
        // This is a simplified version
        if (isset($data->bookings)) {
            foreach ($data->bookings->items() as $booking) {
                fputcsv($file, [
                    $booking->id,
                    $booking->user->name,
                    $booking->tour->name ?? 'N/A',
                    $booking->booking_date,
                    $booking->status,
                    $booking->total_amount,
                    $booking->created_at,
                ]);
            }
        }
    }

    /**
     * Get date format for SQL based on period
     */
    private function getDateFormat($period)
    {
        switch ($period) {
            case 'daily':
                return 'DATE(created_at)';
            case 'weekly':
                return 'YEARWEEK(created_at)';
            case 'monthly':
                return 'DATE_FORMAT(created_at, "%Y-%m")';
            case 'yearly':
                return 'YEAR(created_at)';
            default:
                return 'DATE_FORMAT(created_at, "%Y-%m")';
        }
    }

    /**
     * Get GROUP BY clause based on period
     */
    private function getGroupBy($period)
    {
        switch ($period) {
            case 'daily':
                return 'DATE(created_at)';
            case 'weekly':
                return 'YEARWEEK(created_at)';
            case 'monthly':
                return 'YEAR(created_at), MONTH(created_at)';
            case 'yearly':
                return 'YEAR(created_at)';
            default:
                return 'YEAR(created_at), MONTH(created_at)';
        }
    }
}