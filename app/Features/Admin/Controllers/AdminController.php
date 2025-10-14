<?php

namespace App\Features\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Attractions\Models\Attraction;
use App\Features\Tours\Models\Booking;
use App\Models\User;
use App\Features\Reviews\Models\Review;
use App\Features\Payments\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display the admin dashboard with key metrics
     */
    public function dashboard()
    {
        $metrics = $this->getDashboardMetrics();
        $recentActivity = $this->getRecentActivity();
        $chartData = $this->getChartData();

        return view('admin.dashboard', compact('metrics', 'recentActivity', 'chartData'));
    }

    /**
     * Get key dashboard metrics
     */
    private function getDashboardMetrics()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'total_users' => User::count(),
            'new_users_this_month' => User::where('created_at', '>=', $currentMonth)->count(),
            'total_attractions' => Attraction::where('is_active', true)->count(),
            'total_bookings' => Booking::count(),
            'bookings_this_month' => Booking::where('created_at', '>=', $currentMonth)->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'total_revenue' => Booking::where('payment_status', 'paid')->sum('total_amount'),
            'revenue_this_month' => Booking::where('payment_status', 'paid')
                ->where('created_at', '>=', $currentMonth)
                ->sum('total_amount'),
            'total_commissions' => Commission::sum('amount'),
            'commissions_this_month' => Commission::where('created_at', '>=', $currentMonth)->sum('amount'),
            'pending_reviews' => Review::where('status', 'pending')->count(),
            'approved_reviews' => Review::where('status', 'approved')->count(),
            'average_rating' => Review::where('status', 'approved')->avg('rating'),
        ];
    }

    /**
     * Get recent activity for the dashboard
     */
    private function getRecentActivity()
    {
        return [
            'recent_bookings' => Booking::with(['user', 'tourSchedule'])
                ->latest()
                ->limit(5)
                ->get(),
            'recent_users' => User::where('role', '!=', 'admin')
                ->latest()
                ->limit(5)
                ->get(),
            'recent_reviews' => Review::with(['user', 'reviewable'])
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Get chart data for dashboard visualizations
     */
    private function getChartData()
    {
        $last30Days = Carbon::now()->subDays(30);
        
        // Daily bookings for the last 30 days
        $dailyBookings = Booking::select(
                DB::raw('date(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->where('created_at', '>=', $last30Days)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Monthly revenue for the last 12 months
        $monthlyRevenue = Booking::select(
                DB::raw('strftime("%Y", created_at) as year'),
                DB::raw('strftime("%m", created_at) as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as bookings')
            )
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Bookings by status
        $bookingsByStatus = Booking::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Top attractions by bookings - using reviews as proxy for popularity
        $topAttractions = Attraction::withCount('reviews')
            ->orderByDesc('reviews_count')
            ->limit(10)
            ->get()
            ->map(function ($attraction) {
                return [
                    'name' => $attraction->name,
                    'booking_count' => $attraction->reviews_count // Using reviews as proxy for popularity
                ];
            });

        return [
            'daily_bookings' => $dailyBookings,
            'monthly_revenue' => $monthlyRevenue,
            'bookings_by_status' => $bookingsByStatus,
            'top_attractions' => $topAttractions,
        ];
    }

    /**
     * Get system overview data
     */
    public function overview()
    {
        $overview = [
            'system_health' => $this->getSystemHealth(),
            'performance_metrics' => $this->getPerformanceMetrics(),
            'storage_usage' => $this->getStorageUsage(),
        ];

        return response()->json($overview);
    }

    /**
     * Get system health indicators
     */
    private function getSystemHealth()
    {
        return [
            'database_connection' => $this->checkDatabaseConnection(),
            'storage_writable' => is_writable(storage_path()),
            'cache_working' => $this->checkCacheWorking(),
        ];
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if cache is working
     */
    private function checkCacheWorking()
    {
        try {
            \Cache::put('health_check', 'ok', 60);
            return \Cache::get('health_check') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics()
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'execution_time' => defined('LARAVEL_START') ? microtime(true) - LARAVEL_START : 0,
        ];
    }

    /**
     * Get storage usage information
     */
    private function getStorageUsage()
    {
        $storagePath = storage_path('app/public');
        $totalSize = 0;
        $fileCount = 0;

        try {
            if (is_dir($storagePath)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($storagePath, \RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $totalSize += $file->getSize();
                        $fileCount++;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently handle any filesystem errors
        }

        return [
            'total_size' => $totalSize,
            'file_count' => $fileCount,
            'formatted_size' => $this->formatBytes($totalSize),
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}