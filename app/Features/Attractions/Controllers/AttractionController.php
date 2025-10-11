<?php

namespace App\Features\Attractions\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attraction;
use App\Features\Attractions\Requests\StoreAttractionRequest;
use App\Features\Attractions\Requests\UpdateAttractionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AttractionController extends Controller
{
    /**
     * Display a listing of attractions (Admin)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attraction::with(['department', 'media']);

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->inDepartment($request->department_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if ($sortBy === 'rating') {
            $query->byRating($sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $attractions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'message' => 'Attractions retrieved successfully'
        ]);
    }

    /**
     * Store a newly created attraction
     */
    public function store(StoreAttractionRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Generate slug from name
        $data['slug'] = Str::slug($data['name']);
        
        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Attraction::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $attraction = Attraction::create($data);

        // Load relationships for response
        $attraction->load(['department', 'media']);

        return response()->json([
            'success' => true,
            'data' => $attraction,
            'message' => 'Attraction created successfully'
        ], 201);
    }

    /**
     * Display the specified attraction
     */
    public function show(Attraction $attraction): JsonResponse
    {
        $attraction->load([
            'department',
            'media' => function ($query) {
                $query->orderBy('sort_order')->orderBy('created_at');
            },
            'tours.schedules',
            'approvedReviews' => function ($query) {
                $query->with('user:id,name')->latest()->limit(10);
            }
        ]);

        // Increment visit count
        $attraction->incrementVisits();

        return response()->json([
            'success' => true,
            'data' => $attraction,
            'message' => 'Attraction retrieved successfully'
        ]);
    }

    /**
     * Update the specified attraction
     */
    public function update(UpdateAttractionRequest $request, Attraction $attraction): JsonResponse
    {
        $data = $request->validated();
        
        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $attraction->name) {
            $newSlug = Str::slug($data['name']);
            
            // Ensure slug is unique (excluding current attraction)
            $originalSlug = $newSlug;
            $counter = 1;
            while (Attraction::where('slug', $newSlug)->where('id', '!=', $attraction->id)->exists()) {
                $newSlug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $newSlug;
        }

        $attraction->update($data);

        // Load relationships for response
        $attraction->load(['department', 'media']);

        return response()->json([
            'success' => true,
            'data' => $attraction,
            'message' => 'Attraction updated successfully'
        ]);
    }

    /**
     * Remove the specified attraction
     */
    public function destroy(Attraction $attraction): JsonResponse
    {
        try {
            // For now, just delete the attraction
            // TODO: Add booking validation when tours are implemented
            $attraction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attraction deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting attraction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle attraction status (active/inactive)
     */
    public function toggleStatus(Attraction $attraction): JsonResponse
    {
        $attraction->update([
            'is_active' => !$attraction->is_active
        ]);

        return response()->json([
            'success' => true,
            'data' => $attraction,
            'message' => 'Attraction status updated successfully'
        ]);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Attraction $attraction): JsonResponse
    {
        $attraction->update([
            'is_featured' => !$attraction->is_featured
        ]);

        return response()->json([
            'success' => true,
            'data' => $attraction,
            'message' => 'Attraction featured status updated successfully'
        ]);
    }

    /**
     * Get attraction statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => Attraction::count(),
            'active' => Attraction::active()->count(),
            'inactive' => Attraction::where('is_active', false)->count(),
            'featured' => Attraction::featured()->count(),
            'by_type' => Attraction::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'by_department' => Attraction::with('department:id,name')
                ->selectRaw('department_id, COUNT(*) as count')
                ->groupBy('department_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->department->name => $item->count];
                }),
            'top_rated' => Attraction::active()
                ->where('rating', '>', 0)
                ->orderBy('rating', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'rating', 'reviews_count']),
            'most_visited' => Attraction::active()
                ->where('visits_count', '>', 0)
                ->orderBy('visits_count', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'visits_count'])
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistics retrieved successfully'
        ]);
    }
}