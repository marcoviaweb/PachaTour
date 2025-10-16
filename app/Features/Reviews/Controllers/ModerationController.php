<?php

namespace App\Features\Reviews\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Reviews\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ReviewRejectedNotification;
use App\Notifications\ReviewApprovedNotification;

class ModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'auth.api', 'role:admin']);
    }

    /**
     * Get reviews pending moderation
     */
    public function pending(Request $request): JsonResponse
    {
        $query = Review::with(['user:id,name,email', 'reviewable', 'booking:id,booking_date'])
            ->pending()
            ->orderBy('created_at', 'asc');

        // Filter by reviewable type
        if ($request->has('reviewable_type')) {
            $query->where('reviewable_type', $request->reviewable_type);
        }

        // Filter by language
        if ($request->has('language')) {
            $query->inLanguage($request->language);
        }

        // Search in content
        if ($request->has('search')) {
            $query->search($request->search);
        }

        $reviews = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'meta' => [
                'total_pending' => Review::pending()->count(),
                'total_approved_today' => Review::approved()
                    ->whereDate('moderated_at', today())
                    ->count(),
                'total_rejected_today' => Review::where('status', Review::STATUS_REJECTED)
                    ->whereDate('moderated_at', today())
                    ->count()
            ]
        ]);
    }

    /**
     * Get all reviews for moderation (all statuses)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user:id,name,email', 'reviewable', 'moderator:id,name'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by reviewable type
        if ($request->has('reviewable_type')) {
            $query->where('reviewable_type', $request->reviewable_type);
        }

        // Filter by moderator
        if ($request->has('moderated_by')) {
            $query->where('moderated_by', $request->moderated_by);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search in content
        if ($request->has('search')) {
            $query->search($request->search);
        }

        $reviews = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'meta' => $this->getModerationStats()
        ]);
    }

    /**
     * Show review details for moderation
     */
    public function show(Review $review): JsonResponse
    {
        $review->load([
            'user:id,name,email,created_at',
            'reviewable',
            'booking:id,booking_date,status,number_of_people',
            'moderator:id,name'
        ]);

        // Get user's review history
        $userReviewStats = [
            'total_reviews' => Review::where('user_id', $review->user_id)->count(),
            'approved_reviews' => Review::where('user_id', $review->user_id)
                ->approved()->count(),
            'rejected_reviews' => Review::where('user_id', $review->user_id)
                ->where('status', Review::STATUS_REJECTED)->count(),
            'average_rating' => Review::where('user_id', $review->user_id)
                ->approved()->avg('rating')
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'review' => $review,
                'moderation_summary' => $review->getModerationSummary(),
                'user_stats' => $userReviewStats,
                'can_moderate' => $review->canBeModerated()
            ]
        ]);
    }

    /**
     * Approve a review
     */
    public function approve(Request $request, Review $review): JsonResponse
    {
        if (!$review->canBeModerated()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta reseña no puede ser moderada en su estado actual'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $review->approve(Auth::id());

            // Log moderation action
            $this->logModerationAction('approved', $review, $request->get('notes'));

            // Send notification to user (optional)
            if ($request->boolean('notify_user', true)) {
                $this->notifyUserOfApproval($review);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reseña aprobada exitosamente',
                'data' => $review->fresh(['user:id,name', 'moderator:id,name'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a review
     */
    public function reject(Request $request, Review $review): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500'
        ]);

        if (!$review->canBeModerated()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta reseña no puede ser moderada en su estado actual'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $review->reject($request->reason, Auth::id());

            // Log moderation action
            $this->logModerationAction('rejected', $review, $request->reason);

            // Send notification to user (optional)
            if ($request->boolean('notify_user')) {
                $this->notifyUserOfRejection($review, $request->reason);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reseña rechazada exitosamente',
                'data' => $review->fresh(['user:id,name', 'moderator:id,name'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hide a review (for approved reviews that need to be hidden)
     */
    public function hide(Request $request, Review $review): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500'
        ]);

        if ($review->status !== Review::STATUS_APPROVED) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden ocultar reseñas aprobadas'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $review->hide($request->reason);

            // Log moderation action
            $this->logModerationAction('hidden', $review, $request->reason);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reseña ocultada exitosamente',
                'data' => $review->fresh(['user:id,name', 'moderator:id,name'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al ocultar la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve reviews
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $request->validate([
            'review_ids' => 'required|array|min:1|max:50',
            'review_ids.*' => 'integer|exists:reviews,id'
        ]);

        try {
            DB::beginTransaction();

            $reviews = Review::whereIn('id', $request->review_ids)
                ->where('status', Review::STATUS_PENDING)
                ->get();

            $approved = 0;
            $errors = [];

            foreach ($reviews as $review) {
                try {
                    if ($review->canBeModerated()) {
                        $review->approve(Auth::id());
                        $this->logModerationAction('approved', $review, 'Aprobación masiva');
                        $approved++;
                    } else {
                        $errors[] = "Reseña ID {$review->id} no puede ser moderada";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error en reseña ID {$review->id}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se aprobaron {$approved} reseñas exitosamente",
                'data' => [
                    'approved_count' => $approved,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error en la aprobación masiva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get moderation statistics
     */
    public function stats(): JsonResponse
    {
        $stats = $this->getModerationStats();

        // Add additional detailed stats
        $stats['reviews_by_status'] = [
            'pending' => Review::pending()->count(),
            'approved' => Review::approved()->count(),
            'rejected' => Review::where('status', Review::STATUS_REJECTED)->count(),
            'hidden' => Review::where('status', Review::STATUS_HIDDEN)->count()
        ];

        $stats['moderation_activity'] = [
            'today' => Review::whereDate('moderated_at', today())->count(),
            'this_week' => Review::whereBetween('moderated_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month' => Review::whereMonth('moderated_at', now()->month)
                ->whereYear('moderated_at', now()->year)
                ->count()
        ];

        // Get top moderators this month
        $topModerators = User::where('role', 'admin')
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                $count = Review::where('moderated_by', $user->id)
                    ->whereMonth('moderated_at', now()->month)
                    ->count();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'moderated_reviews_count' => $count
                ];
            })
            ->filter(function ($user) {
                return $user['moderated_reviews_count'] > 0;
            })
            ->sortByDesc('moderated_reviews_count')
            ->take(5)
            ->values();

        $stats['top_moderators'] = $topModerators;

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get reviews that need attention (flagged, reported, etc.)
     */
    public function flagged(Request $request): JsonResponse
    {
        $query = Review::with(['user:id,name,email', 'reviewable'])
            ->where(function ($q) {
                $q->whereRaw('helpful_votes < not_helpful_votes')
                  ->orWhere('not_helpful_votes', '>', 10)
                  ->orWhere(function ($subQ) {
                      $subQ->where('status', Review::STATUS_APPROVED)
                           ->where('rating', '<=', 2)
                           ->whereRaw('LENGTH(comment) < 50');
                  });
            })
            ->orderBy('not_helpful_votes', 'desc');

        $reviews = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'meta' => [
                'total_flagged' => $query->count(),
                'criteria' => [
                    'More not helpful than helpful votes',
                    'More than 10 not helpful votes',
                    'Low rating with very short comment'
                ]
            ]
        ]);
    }

    /**
     * Get moderation statistics
     */
    private function getModerationStats(): array
    {
        return [
            'total_reviews' => Review::count(),
            'pending_reviews' => Review::pending()->count(),
            'approved_reviews' => Review::approved()->count(),
            'rejected_reviews' => Review::where('status', Review::STATUS_REJECTED)->count(),
            'hidden_reviews' => Review::where('status', Review::STATUS_HIDDEN)->count(),
            'average_rating' => round(Review::approved()->avg('rating') ?? 0, 2),
            'moderated_today' => Review::whereDate('moderated_at', today())->count()
        ];
    }

    /**
     * Log moderation action for audit trail
     */
    private function logModerationAction(string $action, Review $review, string $notes = null): void
    {
        // This could be expanded to use a dedicated audit log table
        Log::info('Review moderation action', [
            'action' => $action,
            'review_id' => $review->id,
            'moderator_id' => Auth::id(),
            'moderator_name' => Auth::user()->name,
            'user_id' => $review->user_id,
            'reviewable_type' => $review->reviewable_type,
            'reviewable_id' => $review->reviewable_id,
            'notes' => $notes,
            'timestamp' => now()
        ]);
    }

    /**
     * Send notification to user about review rejection
     */
    private function notifyUserOfRejection(Review $review, string $reason): void
    {
        try {
            $review->user->notify(new ReviewRejectedNotification($review, $reason));
            
            Log::info('Review rejection notification sent', [
                'user_id' => $review->user_id,
                'review_id' => $review->id,
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send review rejection notification', [
                'user_id' => $review->user_id,
                'review_id' => $review->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification to user about review approval
     */
    private function notifyUserOfApproval(Review $review): void
    {
        try {
            $review->user->notify(new ReviewApprovedNotification($review));
            
            Log::info('Review approval notification sent', [
                'user_id' => $review->user_id,
                'review_id' => $review->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send review approval notification', [
                'user_id' => $review->user_id,
                'review_id' => $review->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
