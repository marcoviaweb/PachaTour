<?php

namespace App\Features\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Attraction;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    /**
     * Get dashboard statistics for the authenticated user
     */
    public function dashboardStats(): JsonResponse
    {
        $user = Auth::user();
        
        $stats = [
            'activeBookings' => $user->bookings()
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
            
            'completedBookings' => $user->bookings()
                ->where('status', 'completed')
                ->count(),
            
            'reviewsCount' => $user->reviews()->where('status', 'approved')->count(),
            
            'visitedDestinations' => $user->bookings()
                ->where('status', 'completed')
                ->with('tourSchedule.tour.attractions')
                ->get()
                ->flatMap(function ($booking) {
                    // Handle planning bookings (without tour schedule)
                    if (!$booking->tourSchedule) {
                        // Extract attraction ID from planning notes
                        preg_match('/Atracción ID: (\\d+)/', $booking->notes ?? '', $matches);
                        return $matches[1] ? collect([$matches[1]]) : collect();
                    }
                    
                    // Handle regular tour bookings
                    return optional($booking->tourSchedule->tour)->attractions->pluck('id') ?? collect();
                })
                ->unique()
                ->count()
        ];
        
        return response()->json($stats);
    }

    /**
     * Get upcoming bookings for the authenticated user
     */
    public function upcomingBookings(): JsonResponse
    {
        $user = Auth::user();
        
        try {
            // Get all active bookings including planifications
            $allBookings = $user->bookings()
                ->with([
                    'tourSchedule.tour.attractions.department',
                    'tourSchedule.tour.attractions.media'
                ])
                ->whereIn('status', ['pending', 'confirmed'])
                ->orderBy('created_at', 'desc')
                ->get();

            $bookings = $allBookings->map(function ($booking) {
                // Check if it's a planning booking (no tour_schedule_id and notes contain "PLANIFICACIÓN")
                if (!$booking->tour_schedule_id && str_contains($booking->notes ?? '', 'PLANIFICACIÓN')) {
                    // Extract planning data from notes
                    preg_match('/Atracción ID: (\\d+)/', $booking->notes, $attractionMatches);
                    preg_match('/Fecha: ([\\d-]+)/', $booking->notes, $dateMatches);
                    
                    $attractionId = $attractionMatches[1] ?? null;
                    $visitDate = $dateMatches[1] ?? null;
                    
                    // Get attraction name if possible
                    $attractionName = 'Atracción planificada';
                    $departmentName = null;
                    if ($attractionId) {
                        $attraction = \App\Models\Attraction::with('department')->find($attractionId);
                        if ($attraction) {
                            $attractionName = $attraction->name;
                            $departmentName = $attraction->department?->name;
                        }
                    }
                    
                    return [
                        'id' => $booking->id,
                        'booking_number' => $booking->booking_number,
                        'status' => $booking->status,
                        'status_name' => 'Planificada',
                        'payment_status' => $booking->payment_status,
                        'payment_status_name' => $booking->payment_status_name,
                        'tour_name' => 'Visita planificada',
                        'tour_date' => $visitDate,
                        'tour_time' => null,
                        'tour_duration' => null,
                        'participants_count' => $booking->participants_count,
                        'total_amount' => $booking->total_amount,
                        'currency' => $booking->currency,
                        'attraction_name' => $attractionName,
                        'attraction_slug' => null,
                        'department_name' => $departmentName,
                        'contact_name' => $booking->contact_name,
                        'contact_email' => $booking->contact_email,
                        'contact_phone' => $booking->contact_phone,
                        'special_requests' => $booking->special_requests,
                        'created_at' => $booking->created_at,
                        'confirmed_at' => $booking->confirmed_at,
                        'is_planning' => true,
                    ];
                }
                
                // Regular tour booking
                $schedule = $booking->tourSchedule;
                if (!$schedule) {
                    return null;
                }
                
                $tour = $schedule->tour;
                if (!$tour) {
                    return null;
                }
                
                // Only show future bookings for regular tours
                if ($schedule->date < now()->toDateString()) {
                    return null;
                }
                
                $attraction = $tour->attractions->first();
                
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'status_name' => $booking->status_name,
                    'payment_status' => $booking->payment_status,
                    'payment_status_name' => $booking->payment_status_name,
                    'tour_name' => $tour->name,
                    'tour_date' => $schedule->date,
                    'tour_time' => $schedule->start_time->format('H:i'),
                    'tour_duration' => $tour->formatted_duration ?? null,
                    'participants_count' => $booking->participants_count,
                    'total_amount' => $booking->total_amount,
                    'currency' => $booking->currency,
                    'attraction_name' => $attraction?->name,
                    'attraction_slug' => $attraction?->slug,
                    'department_name' => $attraction?->department?->name,
                    'contact_name' => $booking->contact_name,
                    'contact_email' => $booking->contact_email,
                    'contact_phone' => $booking->contact_phone,
                    'special_requests' => $booking->special_requests,
                    'created_at' => $booking->created_at,
                    'confirmed_at' => $booking->confirmed_at,
                    'is_planning' => false,
                ];
            })
            ->filter()
            ->values();
            
            return response()->json(['data' => $bookings]);
            
        } catch (\Exception $e) {
            // Log the error and return empty array to prevent 500
            \Log::error('Error in upcomingBookings: ' . $e->getMessage());
            
            return response()->json([
                'data' => [],
                'error' => 'Error loading bookings',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get booking history for the authenticated user
     */
    public function bookingHistory(Request $request): JsonResponse
    {
        $user = Auth::user();
        $page = $request->get('page', 1);
        $perPage = 10;
        
        try {
            $bookings = $user->bookings()
                ->with([
                    'tourSchedule.tour.attractions.department',
                    'tourSchedule.tour.attractions.media',
                    'reviews'
                ])
                ->whereIn('status', ['completed', 'cancelled', 'refunded', 'no_show'])
                ->orderBy('created_at', 'desc')  // Usar created_at que sí existe
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function ($booking) {
                    // Handle bookings without tour schedule (planning bookings)
                    if (!$booking->tourSchedule) {
                        // Extract planning data from notes if available
                        preg_match('/Atracción ID: (\\d+)/', $booking->notes ?? '', $attractionMatches);
                        preg_match('/Fecha: ([\\d-]+)/', $booking->notes ?? '', $dateMatches);
                        
                        $attractionId = $attractionMatches[1] ?? null;
                        $visitDate = $dateMatches[1] ?? null;
                        
                        $attractionName = 'Atracción planificada';
                        $departmentName = null;
                        if ($attractionId) {
                            $attraction = \App\Models\Attraction::with('department')->find($attractionId);
                            if ($attraction) {
                                $attractionName = $attraction->name;
                                $departmentName = $attraction->department?->name;
                            }
                        }
                        
                        return [
                            'id' => $booking->id,
                            'booking_number' => $booking->booking_number,
                            'status' => $booking->status,
                            'status_name' => $booking->status_name,
                            'payment_status' => $booking->payment_status,
                            'payment_status_name' => $booking->payment_status_name,
                            'tour_name' => 'Visita planificada',
                            'tour_date' => $visitDate, // Fecha extraída de notes
                            'tour_time' => null,
                            'participants_count' => $booking->participants_count,
                            'total_amount' => $booking->total_amount,
                            'currency' => $booking->currency,
                            'attraction_name' => $attractionName,
                            'attraction_id' => $attractionId,
                            'attraction_slug' => null,
                            'department_name' => $departmentName,
                            'has_review' => false,
                            'review_rating' => null,
                            'cancellation_reason' => $booking->cancellation_reason,
                            'refund_amount' => $booking->refund_amount,
                            'created_at' => $booking->created_at,
                            'cancelled_at' => $booking->cancelled_at,
                            'refunded_at' => $booking->refunded_at,
                        ];
                    }

                    $schedule = $booking->tourSchedule;
                    $tour = $schedule->tour;
                    $attraction = $tour->attractions->first(); // Get the first attraction
                    $review = $booking->reviews->first();
                    
                    return [
                        'id' => $booking->id,
                        'booking_number' => $booking->booking_number,
                        'status' => $booking->status,
                        'status_name' => $booking->status_name,
                        'payment_status' => $booking->payment_status,
                        'payment_status_name' => $booking->payment_status_name,
                        'tour_name' => $tour->name,
                        'tour_date' => $schedule->date,
                        'tour_time' => $schedule->start_time->format('H:i'),
                        'participants_count' => $booking->participants_count,
                        'total_amount' => $booking->total_amount,
                        'currency' => $booking->currency,
                        'attraction_name' => $attraction?->name,
                        'attraction_id' => $attraction?->id,
                        'attraction_slug' => $attraction?->slug,
                        'department_name' => $attraction?->department?->name,
                        'has_review' => $review !== null,
                        'review_rating' => $review?->rating,
                        'cancellation_reason' => $booking->cancellation_reason,
                        'refund_amount' => $booking->refund_amount,
                        'created_at' => $booking->created_at,
                        'cancelled_at' => $booking->cancelled_at,
                        'refunded_at' => $booking->refunded_at,
                    ];
                });
            
            return response()->json(['data' => $bookings]);
            
        } catch (\Exception $e) {
            \Log::error('Error in bookingHistory: ' . $e->getMessage());
            
            return response()->json([
                'data' => [],
                'error' => 'Error loading booking history',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get user reviews
     */
    public function userReviews(): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $reviews = Review::where('user_id', $user->id)
                ->with(['reviewable'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            $mappedReviews = $reviews->map(function ($review) {
                $reviewable = $review->reviewable;
                
                return [
                    'id' => $review->id,
                    'rating' => (float) $review->rating,
                    'title' => $review->title,
                    'comment' => $review->comment,
                    'status' => $review->status,
                    'status_name' => ucfirst($review->status),
                    'attraction_name' => $reviewable?->name ?? 'Atracción no disponible',
                    'attraction_slug' => $reviewable?->slug,
                    'department_name' => $reviewable?->department?->name,
                    'helpful_count' => $review->helpful_votes ?? 0,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                ];
            });
            
            return response()->json(['data' => $mappedReviews]);
            
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'error' => 'Error loading reviews',
                'message' => $e->getMessage()
            ]);
        }
    }    /**
     * Get user favorites
     */
    public function userFavorites(): JsonResponse
    {
        $user = Auth::user();
        
        // Check if UserFavorite model exists, if not create a simple implementation
        $favorites = collect();
        
        if (class_exists(UserFavorite::class)) {
            $favorites = UserFavorite::where('user_id', $user->id)
                ->with(['attraction.department', 'attraction.media'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($favorite) {
                    $attraction = $favorite->attraction;
                    return [
                        'id' => $favorite->id,
                        'created_at' => $favorite->created_at,
                        'attraction' => [
                            'id' => $attraction->id,
                            'name' => $attraction->name,
                            'slug' => $attraction->slug,
                            'description' => $attraction->description,
                            'tourism_type' => $attraction->tourism_type,
                            'department_name' => $attraction->department->name,
                            'main_image' => $attraction->media->where('type', 'image')->first()?->file_path,
                            'average_rating' => $attraction->average_rating ?? 0,
                            'reviews_count' => $attraction->reviews_count ?? 0,
                            'price_range' => $attraction->price_range,
                            'has_tours' => $attraction->tours()->exists(),
                        ]
                    ];
                });
        }
        
        // Get recommendations based on favorites
        $recommendations = collect();
        if ($favorites->isNotEmpty()) {
            $favoriteAttractionIds = $favorites->pluck('attraction.id');
            $favoriteDepartments = $favorites->pluck('attraction.department_name')->unique();
            
            $recommendations = Attraction::whereNotIn('id', $favoriteAttractionIds)
                ->whereHas('department', function ($query) use ($favoriteDepartments) {
                    $query->whereIn('name', $favoriteDepartments);
                })
                ->inRandomOrder()
                ->limit(5)
                ->get(['id', 'name', 'slug']);
        }
        
        return response()->json([
            'data' => $favorites,
            'recommendations' => $recommendations
        ]);
    }

    /**
     * Add attraction to favorites
     */
    public function addFavorite(Request $request): JsonResponse
    {
        $request->validate([
            'attraction_id' => 'required|exists:attractions,id'
        ]);
        
        $user = Auth::user();
        
        // Check if UserFavorite model exists
        if (!class_exists(UserFavorite::class)) {
            return response()->json([
                'message' => 'Favorites functionality not yet implemented'
            ], 501);
        }
        
        $favorite = UserFavorite::firstOrCreate([
            'user_id' => $user->id,
            'attraction_id' => $request->attraction_id
        ]);
        
        return response()->json([
            'message' => 'Attraction added to favorites',
            'favorite' => $favorite
        ]);
    }

    /**
     * Remove attraction from favorites
     */
    public function removeFavorite($favoriteId): JsonResponse
    {
        $user = Auth::user();
        
        if (!class_exists(UserFavorite::class)) {
            return response()->json([
                'message' => 'Favorites functionality not yet implemented'
            ], 501);
        }
        
        $favorite = UserFavorite::where('id', $favoriteId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$favorite) {
            return response()->json([
                'message' => 'Favorite not found'
            ], 404);
        }
        
        $favorite->delete();
        
        return response()->json([
            'message' => 'Attraction removed from favorites'
        ]);
    }

    /**
     * Get user profile
     */
    public function profile(): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date ? $user->birth_date->format('Y-m-d') : null,
                'gender' => $user->gender,
                'nationality' => $user->nationality,
                'country' => $user->country,
                'city' => $user->city,
                'preferred_language' => $user->preferred_language,
                'interests' => $user->interests,
                'bio' => $user->bio,
                'avatar_path' => $user->avatar_path,
                'newsletter_subscription' => (bool) $user->newsletter_subscription,
                'marketing_emails' => (bool) $user->marketing_emails,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'nationality' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'preferred_language' => 'nullable|in:es,en',
            'interests' => 'nullable|array',
            'interests.*' => 'string|max:50',
            'bio' => 'nullable|string|max:500',
            'newsletter_subscription' => 'nullable|boolean',
            'marketing_emails' => 'nullable|boolean',
        ]);
        
        // Exclude name, last_name and email from updates (read-only fields)
        $user->update($request->only([
            'phone', 'birth_date', 'gender', 'nationality', 'country', 'city', 
            'preferred_language', 'interests', 'bio', 'newsletter_subscription', 'marketing_emails'
        ]));
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.']
            ]);
        }
        
        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
        ]);
        
        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }
}