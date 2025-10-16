<?php

namespace App\Features\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Attractions\Models\Attraction;
use App\Features\Departments\Models\Department;
use App\Features\Tours\Models\Booking;
use App\Models\User;
use App\Features\Reviews\Models\Review;
use App\Features\Payments\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['admin']);
    }

    /**
     * Display the admin dashboard with key metrics
     */
    public function dashboard()
    {
        $metrics = $this->getDashboardMetrics();
        $recentActivity = $this->getRecentActivity();
        $chartData = $this->getChartData();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => $metrics,
            'recentActivity' => $recentActivity,
            'chartData' => $chartData
        ]);
    }

    /**
     * Get key dashboard metrics
     */
    private function getDashboardMetrics()
    {
        $currentMonth = Carbon::now()->startOfMonth();

        return [
            'total_users' => User::count(),
            'new_users_this_month' => User::where('created_at', '>=', $currentMonth)->count(),
            'total_attractions' => Attraction::where('is_active', true)->count(),
            'total_bookings' => 0, // Placeholder until booking system is implemented
            'bookings_this_month' => 0, // Placeholder
            'confirmed_bookings' => 0, // Placeholder  
            'pending_bookings' => 0, // Placeholder
            'total_departments' => Department::count(),
            'active_departments' => Department::where('is_active', true)->count(),
            'inactive_departments' => Department::where('is_active', false)->count(),
            'inactive_attractions' => Attraction::where('is_active', false)->count(),
        ];
    }

    /**
     * Get recent activity for the dashboard
     */
    private function getRecentActivity()
    {
        return [
            'recent_bookings' => [], // Placeholder until booking system is implemented
            'recent_reviews' => [], // Placeholder until review system is implemented
            'recent_users' => User::latest()
                ->limit(5)
                ->get(['id', 'name', 'email', 'created_at']),
        ];
    }

    /**
     * Get chart data for dashboard visualizations
     */
    /**
     * Get chart data for dashboard visualizations
     */
    private function getChartData()
    {
        $last30Days = Carbon::now()->subDays(30);
        
        // Daily user registrations for the last 30 days
        $dailyUsers = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $last30Days)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Monthly user growth for the last 12 months
        $monthlyUsers = User::select(
                DB::raw('EXTRACT(YEAR FROM created_at) as year'),
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at)'), DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Departments vs Attractions comparison
        $departmentStats = [
            'total_departments' => Department::count(),
            'active_departments' => Department::where('is_active', true)->count(),
            'total_attractions' => Attraction::count(),
            'active_attractions' => Attraction::where('is_active', true)->count(),
        ];

        return [
            'daily_users' => $dailyUsers,
            'monthly_users' => $monthlyUsers,
            'department_stats' => $departmentStats,
            'total_revenue' => 0, // Placeholder until booking system is implemented
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
            Cache::put('health_check', 'ok', 60);
            return Cache::get('health_check') === 'ok';
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