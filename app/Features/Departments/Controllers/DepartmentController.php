<?php

namespace App\Features\Departments\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index(): JsonResponse
    {
        try {
            $departments = Department::active()
                ->ordered()
                ->with(['media', 'activeAttractions'])
                ->get()
                ->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'slug' => $department->slug,
                        'capital' => $department->capital,
                        'short_description' => $department->short_description,
                        'image_url' => $department->image_url,
                        'attractions_count' => $department->attractions_count,
                        'average_rating' => $department->average_rating,
                        'coordinates' => $department->getCoordinates(),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Departamentos obtenidos correctamente',
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los departamentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified department.
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $department = Department::where('slug', $slug)
                ->active()
                ->with(['media', 'activeAttractions.media', 'reviews' => function ($query) {
                    $query->where('status', 'approved')->latest()->limit(5);
                }])
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
                'average_rating' => $department->average_rating,
                'attractions' => $department->activeAttractions->map(function ($attraction) {
                    return [
                        'id' => $attraction->id,
                        'name' => $attraction->name,
                        'slug' => $attraction->slug,
                        'short_description' => $attraction->short_description,
                        'image_url' => $attraction->image_url,
                        'tourism_type' => $attraction->tourism_type,
                        'average_rating' => $attraction->average_rating,
                    ];
                }),
                'recent_reviews' => $department->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'title' => $review->title,
                        'comment' => $review->comment,
                        'user_name' => $review->user->name,
                        'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Departamento encontrado',
                'data' => $departmentData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}