<?php

namespace App\Features\Attractions\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attraction;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttractionApiController extends Controller
{
    /**
     * Display a listing of active attractions for public consumption
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attraction::active()->with(['department:id,name,slug', 'media' => function ($query) {
            $query->where('type', 'image')->orderBy('sort_order')->limit(3);
        }]);

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by department
        if ($request->filled('department')) {
            if (is_numeric($request->department)) {
                $query->inDepartment($request->department);
            } else {
                // Search by department slug
                $department = Department::where('slug', $request->department)->first();
                if ($department) {
                    $query->inDepartment($department->id);
                }
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            $types = is_array($request->type) ? $request->type : [$request->type];
            $query->whereIn('type', $types);
        }

        // Filter by price range
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        // Filter by rating
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        // Filter by location (nearby)
        if ($request->filled('lat') && $request->filled('lng')) {
            $radius = $request->get('radius', 50); // Default 50km
            $query->nearby($request->lat, $request->lng, $radius);
        }

        // Featured attractions first
        if ($request->boolean('featured_first')) {
            $query->orderBy('is_featured', 'desc');
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        switch ($sortBy) {
            case 'rating':
                $query->byRating($sortDirection);
                break;
            case 'price':
                $query->orderBy('entry_price', $sortDirection);
                break;
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'visits':
                $query->orderBy('visits_count', $sortDirection);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
        }

        $perPage = min($request->get('per_page', 12), 50); // Max 50 per page
        $attractions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'filters' => [
                'types' => Attraction::TYPES,
                'departments' => Department::select('id', 'name', 'slug')->get(),
            ],
            'message' => 'Attractions retrieved successfully'
        ]);
    }

    /**
     * Display the specified attraction for public consumption
     */
    public function show(string $slug): JsonResponse
    {
        $attraction = Attraction::active()
            ->where('slug', $slug)
            ->with([
                'department:id,name,slug',
                'media' => function ($query) {
                    $query->orderBy('sort_order')->orderBy('created_at');
                },
                'tours' => function ($query) {
                    $query->where('status', 'active')
                        ->with(['schedules' => function ($scheduleQuery) {
                            $scheduleQuery->orderBy('day_of_week')->orderBy('start_time');
                        }]);
                },
                'approvedReviews' => function ($query) {
                    $query->with('user:id,name')
                        ->latest()
                        ->limit(10);
                }
            ])
            ->first();

        if (!$attraction) {
            return response()->json([
                'success' => false,
                'message' => 'Attraction not found'
            ], 404);
        }

        // Increment visit count
        $attraction->incrementVisits();

        // Get related attractions (same department, different attraction)
        $relatedAttractions = Attraction::active()
            ->where('department_id', $attraction->department_id)
            ->where('id', '!=', $attraction->id)
            ->with(['media' => function ($query) {
                $query->where('type', 'image')->orderBy('sort_order')->limit(1);
            }])
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'attraction' => $attraction,
                'related' => $relatedAttractions,
                'coordinates' => $attraction->getCoordinates(),
                'is_open_now' => $attraction->isOpenNow(),
            ],
            'message' => 'Attraction retrieved successfully'
        ]);
    }

    /**
     * Get featured attractions
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 6), 20); // Max 20 featured

        $attractions = Attraction::active()
            ->featured()
            ->with(['department:id,name,slug', 'media' => function ($query) {
                $query->where('type', 'image')->orderBy('sort_order')->limit(1);
            }])
            ->byRating('desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'message' => 'Featured attractions retrieved successfully'
        ]);
    }

    /**
     * Search attractions with autocomplete suggestions
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Query too short'
            ]);
        }

        $attractions = Attraction::active()
            ->search($query)
            ->with(['department:id,name'])
            ->select('id', 'name', 'slug', 'department_id', 'type', 'city')
            ->limit(10)
            ->get();

        // Also search departments
        $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';
        $departments = Department::where('name', $operator, "%{$query}%")
            ->select('id', 'name', 'slug')
            ->limit(5)
            ->get()
            ->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'slug' => $dept->slug,
                    'type' => 'department'
                ];
            });

        $suggestions = $attractions->map(function ($attraction) {
            return [
                'id' => $attraction->id,
                'name' => $attraction->name,
                'slug' => $attraction->slug,
                'department' => $attraction->department->name,
                'type' => 'attraction',
                'attraction_type' => $attraction->type,
                'city' => $attraction->city
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'attractions' => $suggestions,
                'departments' => $departments,
            ],
            'message' => 'Search results retrieved successfully'
        ]);
    }

    /**
     * Get attractions by department
     */
    public function byDepartment(string $departmentSlug, Request $request): JsonResponse
    {
        $department = Department::where('slug', $departmentSlug)->first();
        
        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found'
            ], 404);
        }

        $query = Attraction::active()
            ->inDepartment($department->id)
            ->with(['media' => function ($query) {
                $query->where('type', 'image')->orderBy('sort_order')->limit(2);
            }]);

        // Apply filters similar to index method
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        $sortBy = $request->get('sort_by', 'rating');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if ($sortBy === 'rating') {
            $query->byRating($sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $perPage = min($request->get('per_page', 12), 50);
        $attractions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'department' => $department,
                'attractions' => $attractions,
            ],
            'message' => 'Department attractions retrieved successfully'
        ]);
    }

    /**
     * Get attraction types with counts
     */
    public function types(): JsonResponse
    {
        $types = Attraction::active()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->type => [
                        'name' => Attraction::TYPES[$item->type] ?? $item->type,
                        'count' => $item->count,
                        'slug' => $item->type
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $types,
            'message' => 'Attraction types retrieved successfully'
        ]);
    }
}