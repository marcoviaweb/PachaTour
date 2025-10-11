<?php

namespace App\Features\Departments\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentApiController extends Controller
{
    /**
     * Display a listing of departments for public API.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Department::active()->ordered();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $query->search($request->search);
            }

            // Include attractions count
            $query->withCount('activeAttractions');

            // Load relationships
            $query->with(['media']);

            $departments = $query->get()->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'slug' => $department->slug,
                    'capital' => $department->capital,
                    'short_description' => $department->short_description,
                    'image_url' => $department->image_url,
                    'attractions_count' => $department->active_attractions_count,
                    'coordinates' => $department->getCoordinates(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $departments,
                'meta' => [
                    'total' => $departments->count(),
                    'search' => $request->search ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los departamentos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified department for public API.
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $department = Department::where('slug', $slug)
                ->active()
                ->with([
                    'media',
                    'activeAttractions' => function ($query) {
                        $query->with('media')->orderBy('name');
                    },
                    'reviews' => function ($query) {
                        $query->where('status', 'approved')
                            ->with('user:id,name')
                            ->latest()
                            ->limit(10);
                    }
                ])
                ->first();

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamento no encontrado'
                ], 404);
            }

            $departmentData = [
                'id' => $department->id,
                'name' => $department->name,
                'slug' => $department->slug,
                'capital' => $department->capital,
                'description' => $department->description,
                'short_description' => $department->short_description,
                'image_url' => $department->image_url,
                'gallery' => $department->gallery,
                'population' => $department->population,
                'area_km2' => $department->area_km2,
                'climate' => $department->climate,
                'languages' => $department->languages,
                'coordinates' => $department->getCoordinates(),
                'attractions_count' => $department->attractions_count,
                'average_rating' => round($department->average_rating, 1),
                'attractions' => $department->activeAttractions->map(function ($attraction) {
                    return [
                        'id' => $attraction->id,
                        'name' => $attraction->name,
                        'slug' => $attraction->slug,
                        'short_description' => $attraction->short_description,
                        'image_url' => $attraction->image_url,
                        'tourism_type' => $attraction->tourism_type,
                        'average_rating' => round($attraction->average_rating, 1),
                        'coordinates' => $attraction->getCoordinates(),
                    ];
                }),
                'reviews' => $department->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'title' => $review->title,
                        'comment' => $review->comment,
                        'user_name' => $review->user->name,
                        'created_at' => $review->created_at->format('Y-m-d'),
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $departmentData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el departamento',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get basic department information for dropdowns/selects.
     */
    public function list(): JsonResponse
    {
        try {
            $departments = Department::active()
                ->ordered()
                ->select('id', 'name', 'slug', 'capital')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de departamentos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}