<?php

namespace App\Features\Reviews\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Reviews\Models\Review;
use App\Features\Attractions\Models\Attraction;
use App\Features\Reviews\Requests\StoreReviewRequest;
use App\Features\Reviews\Requests\UpdateReviewRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * Display a listing of reviews for a specific attraction
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user:id,name', 'booking:id,created_at'])
            ->approved()
            ->recent();

        // Filter by reviewable (attraction, tour, etc.)
        if ($request->has('reviewable_type') && $request->has('reviewable_id')) {
            $query->where('reviewable_type', $request->reviewable_type)
                  ->where('reviewable_id', $request->reviewable_id);
        }

        // Filter by minimum rating
        if ($request->has('min_rating')) {
            $query->minRating($request->min_rating);
        }

        // Filter by language
        if ($request->has('language')) {
            $query->inLanguage($request->language);
        }

        // Filter by verified reviews only
        if ($request->boolean('verified_only')) {
            $query->verified();
        }

        // Search in comments
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'recent');
        switch ($sortBy) {
            case 'helpful':
                $query->byHelpfulness();
                break;
            case 'rating_high':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc');
                break;
            default:
                $query->recent();
        }

        $reviews = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'meta' => [
                'total_reviews' => $reviews->total(),
                'average_rating' => $this->calculateAverageRating($request),
                'rating_distribution' => $this->getRatingDistribution($request)
            ]
        ]);
    }

    /**
     * Store a newly created review
     */
    public function store(Request $request): JsonResponse
    {
        // Basic validation
        $request->validate([
            'reviewable_type' => 'required|string',
            'reviewable_id' => 'required|integer',
            'rating' => 'required|numeric|min:1|max:5',
            'title' => 'required|string|min:5|max:255',
            'comment' => 'required|string|min:10|max:2000',
            'booking_id' => 'nullable|integer|exists:bookings,id',
            'detailed_ratings' => 'nullable|array',
            'pros' => 'nullable|array|max:5',
            'cons' => 'nullable|array|max:5',
            'would_recommend' => 'nullable|boolean',
            'visit_date' => 'nullable|date|before_or_equal:today',
            'travel_type' => 'nullable|string',
            'language' => 'nullable|string|size:2'
        ]);

        try {
            DB::beginTransaction();

            $review = Review::create([
                'user_id' => Auth::id(),
                'reviewable_type' => $request->reviewable_type,
                'reviewable_id' => $request->reviewable_id,
                'booking_id' => $request->booking_id,
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'detailed_ratings' => $request->detailed_ratings,
                'pros' => $request->pros,
                'cons' => $request->cons,
                'would_recommend' => $request->would_recommend,
                'visit_date' => $request->visit_date,
                'travel_type' => $request->travel_type,
                'language' => $request->get('language', 'es'),
                'status' => Review::STATUS_PENDING
            ]);

            // Mark as verified if user has a confirmed booking
            if ($request->booking_id) {
                $booking = \App\Features\Tours\Models\Booking::find($request->booking_id);
                if ($booking && $booking->user_id === Auth::id() && $booking->status === 'completed') {
                    $review->verify();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reseña creada exitosamente. Será revisada antes de publicarse.',
                'data' => $review->load(['user:id,name'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified review
     */
    public function show(Review $review): JsonResponse
    {
        // Only show approved reviews to non-owners
        if ($review->status !== Review::STATUS_APPROVED && 
            (!Auth::check() || Auth::id() !== $review->user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Reseña no encontrada'
            ], 404);
        }

        $review->load([
            'user:id,name',
            'reviewable',
            'booking:id,booking_date,number_of_people'
        ]);

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    /**
     * Update the specified review
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        // Check if user can edit this review
        if (!$review->canBeEditedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para editar esta reseña'
            ], 403);
        }

        // Basic validation
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'title' => 'required|string|min:5|max:255',
            'comment' => 'required|string|min:10|max:2000',
            'detailed_ratings' => 'nullable|array',
            'pros' => 'nullable|array|max:5',
            'cons' => 'nullable|array|max:5',
            'would_recommend' => 'nullable|boolean',
            'travel_type' => 'nullable|string'
        ]);

        try {
            $review->update([
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'detailed_ratings' => $request->detailed_ratings,
                'pros' => $request->pros,
                'cons' => $request->cons,
                'would_recommend' => $request->would_recommend,
                'travel_type' => $request->travel_type,
                'status' => Review::STATUS_PENDING // Reset to pending after edit
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reseña actualizada exitosamente',
                'data' => $review->load(['user:id,name'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified review
     */
    public function destroy(Review $review): JsonResponse
    {
        // Only the author or admin can delete
        if (Auth::id() !== $review->user_id && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar esta reseña'
            ], 403);
        }

        try {
            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reseña eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's own reviews
     */
    public function myReviews(Request $request): JsonResponse
    {
        $reviews = Review::where('user_id', Auth::id())
            ->with(['reviewable:id,name', 'booking:id,booking_date'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Vote a review as helpful
     */
    public function voteHelpful(Review $review): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para votar'
            ], 401);
        }

        if ($review->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes votar tu propia reseña'
            ], 403);
        }

        $review->voteHelpful();

        return response()->json([
            'success' => true,
            'message' => 'Voto registrado',
            'data' => [
                'helpful_votes' => $review->helpful_votes,
                'helpfulness_percentage' => $review->helpfulness_percentage
            ]
        ]);
    }

    /**
     * Vote a review as not helpful
     */
    public function voteNotHelpful(Review $review): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para votar'
            ], 401);
        }

        if ($review->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes votar tu propia reseña'
            ], 403);
        }

        $review->voteNotHelpful();

        return response()->json([
            'success' => true,
            'message' => 'Voto registrado',
            'data' => [
                'not_helpful_votes' => $review->not_helpful_votes,
                'helpfulness_percentage' => $review->helpfulness_percentage
            ]
        ]);
    }

    /**
     * Calculate average rating for given filters
     */
    private function calculateAverageRating(Request $request): float
    {
        $query = Review::approved();

        if ($request->has('reviewable_type') && $request->has('reviewable_id')) {
            $query->where('reviewable_type', $request->reviewable_type)
                  ->where('reviewable_id', $request->reviewable_id);
        }

        return round($query->avg('rating') ?? 0, 2);
    }

    /**
     * Get rating distribution for given filters
     */
    private function getRatingDistribution(Request $request): array
    {
        $query = Review::approved();

        if ($request->has('reviewable_type') && $request->has('reviewable_id')) {
            $query->where('reviewable_type', $request->reviewable_type)
                  ->where('reviewable_id', $request->reviewable_id);
        }

        // Get all reviews and calculate distribution in PHP to be database-agnostic
        $reviews = $query->get(['rating']);
        
        $distribution = [];
        foreach ($reviews as $review) {
            $ratingFloor = floor($review->rating);
            $distribution[$ratingFloor] = ($distribution[$ratingFloor] ?? 0) + 1;
        }

        // Fill missing ratings with 0
        $result = [];
        for ($i = 5; $i >= 1; $i--) {
            $result[$i] = $distribution[$i] ?? 0;
        }

        return $result;
    }
}