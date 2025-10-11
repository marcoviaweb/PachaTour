<?php

namespace App\Features\Search\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Search\Services\FilterService;
use App\Features\Search\Requests\FilterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    protected FilterService $filterService;

    public function __construct(FilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Aplicar filtros avanzados
     */
    public function filter(FilterRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $attractions = $this->filterService->applyFilters($filters);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'total' => $attractions->count(),
            'filters_applied' => array_filter($filters),
            'message' => 'Filtros aplicados exitosamente'
        ]);
    }

    /**
     * Filtros dinámicos basados en query parameters
     */
    public function dynamicFilter(Request $request): JsonResponse
    {
        $queryParams = $request->all();
        $attractions = $this->filterService->applyDynamicFilters($queryParams);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'total' => $attractions->count(),
            'query_params' => $queryParams,
            'message' => 'Filtros dinámicos aplicados exitosamente'
        ]);
    }

    /**
     * Obtener opciones de filtros disponibles
     */
    public function options(): JsonResponse
    {
        $options = $this->filterService->getFilterOptions();

        return response()->json([
            'success' => true,
            'data' => $options,
            'message' => 'Opciones de filtros obtenidas exitosamente'
        ]);
    }

    /**
     * Filtrar por precio
     */
    public function filterByPrice(Request $request): JsonResponse
    {
        $request->validate([
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price'
        ]);

        $filters = [
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price')
        ];

        $attractions = $this->filterService->applyFilters($filters);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'total' => $attractions->count(),
            'price_range' => [
                'min' => $request->get('min_price'),
                'max' => $request->get('max_price')
            ],
            'message' => 'Filtro por precio aplicado exitosamente'
        ]);
    }

    /**
     * Filtrar por valoración
     */
    public function filterByRating(Request $request): JsonResponse
    {
        $request->validate([
            'min_rating' => 'required|numeric|min:0|max:5'
        ]);

        $filters = [
            'min_rating' => $request->get('min_rating')
        ];

        $attractions = $this->filterService->applyFilters($filters);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'total' => $attractions->count(),
            'min_rating' => $request->get('min_rating'),
            'message' => 'Filtro por valoración aplicado exitosamente'
        ]);
    }

    /**
     * Filtrar por distancia desde una ubicación
     */
    public function filterByDistance(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:500'
        ]);

        $filters = [
            'latitude' => $request->get('latitude'),
            'longitude' => $request->get('longitude'),
            'radius' => $request->get('radius', 50)
        ];

        $attractions = $this->filterService->applyFilters($filters);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'total' => $attractions->count(),
            'location' => [
                'latitude' => $request->get('latitude'),
                'longitude' => $request->get('longitude'),
                'radius_km' => $filters['radius']
            ],
            'message' => 'Filtro por distancia aplicado exitosamente'
        ]);
    }

    /**
     * Filtrar por amenidades
     */
    public function filterByAmenities(Request $request): JsonResponse
    {
        $amenities = $request->get('amenities', []);
        
        // Si viene como string, convertir a array
        if (is_string($amenities)) {
            $amenities = [$amenities];
        }

        $request->validate([
            'amenities' => 'required|array',
            'amenities.*' => 'string|max:100'
        ]);

        $filters = [
            'amenities' => $amenities
        ];

        $attractions = $this->filterService->applyFilters($filters);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'total' => $attractions->count(),
            'amenities' => $amenities,
            'message' => 'Filtro por amenidades aplicado exitosamente'
        ]);
    }

    /**
     * Filtrar por nivel de dificultad
     */
    public function filterByDifficulty(Request $request): JsonResponse
    {
        $request->validate([
            'difficulty_level' => 'required|in:Fácil,Moderado,Difícil'
        ]);

        $filters = [
            'difficulty_level' => $request->get('difficulty_level')
        ];

        $attractions = $this->filterService->applyFilters($filters);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'total' => $attractions->count(),
            'difficulty_level' => $request->get('difficulty_level'),
            'message' => 'Filtro por dificultad aplicado exitosamente'
        ]);
    }
}