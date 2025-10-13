<?php

namespace App\Features\Tours\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Features\Tours\Requests\StoreTourRequest;
use App\Features\Tours\Requests\UpdateTourRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourController extends Controller
{
    /**
     * Display a listing of tours (Admin)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tour::with(['attractions:id,name,slug', 'schedules', 'media']);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('difficulty')) {
            $query->byDifficulty($request->difficulty);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('featured')) {
            $query->featured();
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSorts = ['name', 'price_per_person', 'rating', 'created_at', 'bookings_count'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $tours = $query->paginate($perPage);

        $toursArray = $tours->toArray();
        
        return response()->json([
            'success' => true,
            'data' => $toursArray,
            'meta' => [
                'total_tours' => Tour::count(),
                'active_tours' => Tour::active()->count(),
                'featured_tours' => Tour::featured()->count(),
            ]
        ]);
    }

    /**
     * Store a newly created tour
     */
    public function store(StoreTourRequest $request): JsonResponse
    {
        $tour = Tour::create($request->validated());

        // Attach attractions if provided
        if ($request->has('attractions')) {
            $attractions = collect($request->attractions)->mapWithKeys(function ($attraction) {
                return [$attraction['id'] => [
                    'visit_order' => $attraction['visit_order'] ?? 1,
                    'duration_minutes' => $attraction['duration_minutes'] ?? null,
                    'notes' => $attraction['notes'] ?? null,
                    'is_optional' => $attraction['is_optional'] ?? false,
                    'arrival_time' => $attraction['arrival_time'] ?? null,
                    'departure_time' => $attraction['departure_time'] ?? null,
                ]];
            });
            
            $tour->attractions()->attach($attractions);
        }

        $tour->load(['attractions', 'schedules', 'media']);

        return response()->json([
            'success' => true,
            'message' => 'Tour creado exitosamente',
            'data' => $tour
        ], 201);
    }

    /**
     * Display the specified tour
     */
    public function show(Tour $tour): JsonResponse
    {
        $tour->load([
            'attractions' => function ($query) {
                $query->with(['department', 'media']);
            },
            'schedules' => function ($query) {
                $query->upcoming()->with('bookings');
            },
            'media',
            'reviews' => function ($query) {
                $query->approved()->with('user:id,name')->latest();
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $tour
        ]);
    }

    /**
     * Update the specified tour
     */
    public function update(UpdateTourRequest $request, Tour $tour): JsonResponse
    {
        $tour->update($request->validated());

        // Update attractions if provided
        if ($request->has('attractions')) {
            $attractions = collect($request->attractions)->mapWithKeys(function ($attraction) {
                return [$attraction['id'] => [
                    'visit_order' => $attraction['visit_order'] ?? 1,
                    'duration_minutes' => $attraction['duration_minutes'] ?? null,
                    'notes' => $attraction['notes'] ?? null,
                    'is_optional' => $attraction['is_optional'] ?? false,
                    'arrival_time' => $attraction['arrival_time'] ?? null,
                    'departure_time' => $attraction['departure_time'] ?? null,
                ]];
            });
            
            $tour->attractions()->sync($attractions);
        }

        $tour->load(['attractions', 'schedules', 'media']);

        return response()->json([
            'success' => true,
            'message' => 'Tour actualizado exitosamente',
            'data' => $tour
        ]);
    }

    /**
     * Remove the specified tour
     */
    public function destroy(Tour $tour): JsonResponse
    {
        // Check if tour has active bookings
        // Note: Temporarily disabled until booking system is implemented
        // $activeBookings = $tour->bookings()
        //                       ->whereIn('status', ['pending', 'confirmed', 'paid'])
        //                       ->count();

        // if ($activeBookings > 0) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'No se puede eliminar el tour porque tiene reservas activas'
        //     ], 422);
        // }

        $tour->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tour eliminado exitosamente'
        ]);
    }

    /**
     * Toggle tour active status
     */
    public function toggleStatus(Tour $tour): JsonResponse
    {
        $tour->update(['is_active' => !$tour->is_active]);

        return response()->json([
            'success' => true,
            'message' => $tour->is_active ? 'Tour activado' : 'Tour desactivado',
            'data' => ['is_active' => $tour->is_active]
        ]);
    }

    /**
     * Toggle tour featured status
     */
    public function toggleFeatured(Tour $tour): JsonResponse
    {
        $tour->update(['is_featured' => !$tour->is_featured]);

        return response()->json([
            'success' => true,
            'message' => $tour->is_featured ? 'Tour marcado como destacado' : 'Tour removido de destacados',
            'data' => ['is_featured' => $tour->is_featured]
        ]);
    }

    /**
     * Get tour statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_tours' => Tour::count(),
            'active_tours' => Tour::active()->count(),
            'featured_tours' => Tour::featured()->count(),
            'tours_by_type' => Tour::selectRaw('type, COUNT(*) as count')
                                  ->groupBy('type')
                                  ->pluck('count', 'type'),
            'tours_by_difficulty' => Tour::selectRaw('difficulty_level, COUNT(*) as count')
                                        ->groupBy('difficulty_level')
                                        ->pluck('count', 'difficulty_level'),
            'average_price' => Tour::avg('price_per_person'),
            'price_range' => [
                'min' => Tour::min('price_per_person'),
                'max' => Tour::max('price_per_person'),
            ],
            'total_bookings' => Tour::sum('bookings_count'),
            'average_rating' => Tour::whereNotNull('rating')->avg('rating'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Duplicate a tour
     */
    public function duplicate(Tour $tour): JsonResponse
    {
        $newTour = $tour->replicate();
        $newTour->name = $tour->name . ' (Copia)';
        $newTour->slug = $tour->slug . '-copia-' . time();
        $newTour->is_active = false;
        $newTour->is_featured = false;
        $newTour->save();

        // Copy attractions relationship
        $attractions = $tour->attractions()->get();
        foreach ($attractions as $attraction) {
            $newTour->attractions()->attach($attraction->id, [
                'visit_order' => $attraction->pivot->visit_order,
                'duration_minutes' => $attraction->pivot->duration_minutes,
                'notes' => $attraction->pivot->notes,
                'is_optional' => $attraction->pivot->is_optional,
                'arrival_time' => $attraction->pivot->arrival_time,
                'departure_time' => $attraction->pivot->departure_time,
            ]);
        }

        $newTour->load(['attractions', 'schedules', 'media']);

        return response()->json([
            'success' => true,
            'message' => 'Tour duplicado exitosamente',
            'data' => $newTour
        ], 201);
    }

    /**
     * Display a listing of active tours for public viewing
     */
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $query = Tour::with([
                'attractions:id,name,slug',
                'schedules' => function($q) {
                    $q->where('date', '>=', now()->toDateString())
                      ->where('status', 'available')
                      ->orderBy('date')
                      ->orderBy('start_time');
                }
            ])->where('is_active', true);

            // Apply filters
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('difficulty')) {
                $query->where('difficulty_level', $request->difficulty);
            }

            if ($request->filled('min_price')) {
                $query->where('price_per_person', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price_per_person', '<=', $request->max_price);
            }

            if ($request->filled('duration_days')) {
                $query->where('duration_days', $request->duration_days);
            }

            if ($request->filled('featured')) {
                $query->where('is_featured', true);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            $allowedSorts = ['name', 'price_per_person', 'rating', 'created_at', 'bookings_count'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $perPage = min($request->get('per_page', 12), 24);
            $tours = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading tours: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}