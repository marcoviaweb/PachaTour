<?php

namespace App\Features\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Features\Admin\Requests\UpdateUserRequest;
use App\Features\Admin\Services\UserActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected UserActivityService $activityService;

    public function __construct(UserActivityService $activityService)
    {
        $this->middleware(['auth', 'role:admin']);
        $this->activityService = $activityService;
    }

    /**
     * Display a listing of users with pagination and filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $operator) {
                $q->where('name', $operator, "%{$search}%")
                  ->orWhere('last_name', $operator, "%{$search}%")
                  ->orWhere('email', $operator, "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        if ($request->filled('status')) {
            $isActive = $request->get('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $users = $query->withCount(['bookings', 'reviews'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(15);

        return response()->json($users);
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['bookings.tour.attraction', 'reviews.attraction']);
        
        // Get recent activity
        $recentActivity = $this->activityService->getUserActivity($user->id, 10);

        return response()->json([
            'user' => $user,
            'recent_activity' => $recentActivity
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Log the update activity
        $this->activityService->logActivity(
            $user->id,
            'user_updated',
            'User profile updated by admin',
            ['updated_by' => auth()->id()]
        );

        $user->update($data);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Activate a user account
     */
    public function activate(User $user): JsonResponse
    {
        if ($user->is_active) {
            return response()->json([
                'message' => 'El usuario ya está activo'
            ], 400);
        }

        $user->update(['is_active' => true]);

        $this->activityService->logActivity(
            $user->id,
            'account_activated',
            'Account activated by admin',
            ['activated_by' => auth()->id()]
        );

        return response()->json([
            'message' => 'Usuario activado exitosamente',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Deactivate a user account
     */
    public function deactivate(User $user): JsonResponse
    {
        if (!$user->is_active) {
            return response()->json([
                'message' => 'El usuario ya está inactivo'
            ], 400);
        }

        // Prevent deactivating admin users
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'No se puede desactivar un usuario administrador'
            ], 403);
        }

        $user->update(['is_active' => false]);

        $this->activityService->logActivity(
            $user->id,
            'account_deactivated',
            'Account deactivated by admin',
            ['deactivated_by' => auth()->id()]
        );

        return response()->json([
            'message' => 'Usuario desactivado exitosamente',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user): JsonResponse
    {
        // Generate a temporary password
        $temporaryPassword = Str::random(12);
        
        $user->update([
            'password' => Hash::make($temporaryPassword),
            'password_changed_at' => null // Force password change on next login
        ]);

        $this->activityService->logActivity(
            $user->id,
            'password_reset',
            'Password reset by admin',
            ['reset_by' => auth()->id()]
        );

        // In a real application, you would send this via email
        // For now, we'll return it in the response (not recommended for production)
        return response()->json([
            'message' => 'Contraseña restablecida exitosamente',
            'temporary_password' => $temporaryPassword,
            'note' => 'El usuario deberá cambiar esta contraseña en su próximo inicio de sesión'
        ]);
    }

    /**
     * Send password reset link to user
     */
    public function sendPasswordResetLink(User $user): JsonResponse
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->activityService->logActivity(
                $user->id,
                'password_reset_link_sent',
                'Password reset link sent by admin',
                ['sent_by' => auth()->id()]
            );

            return response()->json([
                'message' => 'Enlace de restablecimiento enviado exitosamente'
            ]);
        }

        return response()->json([
            'message' => 'Error al enviar el enlace de restablecimiento'
        ], 500);
    }

    /**
     * Get user activity logs
     */
    public function getActivityLogs(User $user, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $activities = $this->activityService->getUserActivity($user->id, $limit);

        return response()->json($activities);
    }

    /**
     * Get user statistics
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'users_by_role' => User::selectRaw('role, count(*) as count')
                                  ->groupBy('role')
                                  ->pluck('count', 'role'),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'users_with_bookings' => User::has('bookings')->count(),
        ];

        return response()->json($stats);
    }
}