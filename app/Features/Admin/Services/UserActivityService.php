<?php

namespace App\Features\Admin\Services;

use App\Models\UserActivity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserActivityService
{
    /**
     * Log user activity
     */
    public function logActivity(
        int $userId,
        string $action,
        string $description,
        array $metadata = []
    ): UserActivity {
        return UserActivity::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now()
        ]);
    }

    /**
     * Get user activity logs
     */
    public function getUserActivity(int $userId, int $limit = 20): Collection
    {
        return UserActivity::where('user_id', $userId)
            ->orderBy('performed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity statistics for a user
     */
    public function getUserActivityStats(int $userId): array
    {
        $stats = UserActivity::where('user_id', $userId)
            ->selectRaw('action, count(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        $recentActivity = UserActivity::where('user_id', $userId)
            ->where('performed_at', '>=', now()->subDays(30))
            ->count();

        return [
            'actions_by_type' => $stats,
            'recent_activity_count' => $recentActivity,
            'last_activity' => UserActivity::where('user_id', $userId)
                ->orderBy('performed_at', 'desc')
                ->first()?->performed_at
        ];
    }

    /**
     * Get system-wide activity statistics
     */
    public function getSystemActivityStats(): array
    {
        return [
            'total_activities' => UserActivity::count(),
            'activities_today' => UserActivity::whereDate('performed_at', today())->count(),
            'activities_this_week' => UserActivity::where('performed_at', '>=', now()->subWeek())->count(),
            'activities_this_month' => UserActivity::where('performed_at', '>=', now()->subMonth())->count(),
            'most_common_actions' => UserActivity::selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count', 'action'),
            'active_users_today' => UserActivity::whereDate('performed_at', today())
                ->distinct('user_id')
                ->count('user_id')
        ];
    }

    /**
     * Clean old activity logs
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        return UserActivity::where('performed_at', '<', now()->subDays($daysToKeep))
            ->delete();
    }

    /**
     * Log authentication events
     */
    public function logAuthEvent(int $userId, string $event, array $metadata = []): UserActivity
    {
        $descriptions = [
            'login' => 'Usuario inició sesión',
            'logout' => 'Usuario cerró sesión',
            'login_failed' => 'Intento de inicio de sesión fallido',
            'password_changed' => 'Usuario cambió su contraseña',
            'email_verified' => 'Usuario verificó su correo electrónico'
        ];

        return $this->logActivity(
            $userId,
            $event,
            $descriptions[$event] ?? $event,
            $metadata
        );
    }

    /**
     * Log booking events
     */
    public function logBookingEvent(int $userId, string $event, int $bookingId, array $metadata = []): UserActivity
    {
        $descriptions = [
            'booking_created' => 'Nueva reserva creada',
            'booking_updated' => 'Reserva modificada',
            'booking_cancelled' => 'Reserva cancelada',
            'booking_completed' => 'Reserva completada'
        ];

        $metadata['booking_id'] = $bookingId;

        return $this->logActivity(
            $userId,
            $event,
            $descriptions[$event] ?? $event,
            $metadata
        );
    }

    /**
     * Log review events
     */
    public function logReviewEvent(int $userId, string $event, int $reviewId, array $metadata = []): UserActivity
    {
        $descriptions = [
            'review_created' => 'Nueva valoración creada',
            'review_updated' => 'Valoración modificada',
            'review_deleted' => 'Valoración eliminada'
        ];

        $metadata['review_id'] = $reviewId;

        return $this->logActivity(
            $userId,
            $event,
            $descriptions[$event] ?? $event,
            $metadata
        );
    }
}