<?php

namespace App\Features\Search\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Search\Services\SearchService;
use App\Features\Search\Requests\SearchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Búsqueda general de atractivos
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $criteria = $request->validated();
        $attractions = $this->searchService->searchAttractions($criteria);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'total' => $attractions->count(),
            'message' => 'Búsqueda realizada exitosamente'
        ]);
    }

    /**
     * Autocompletado para búsqueda
     */
    public function suggestions(Request $request): JsonResponse
    {
        $term = $request->get('q', ''); // SearchBar component sends 'q' parameter
        $limit = $request->get('limit', 8);

        if (strlen($term) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Término de búsqueda muy corto'
            ]);
        }

        $suggestions = $this->searchService->getSuggestions($term, $limit);

        return response()->json([
            'success' => true,
            'data' => $suggestions,
            'message' => 'Sugerencias obtenidas exitosamente'
        ]);
    }

    /**
     * Búsqueda avanzada con filtros
     */
    public function advancedSearch(Request $request): JsonResponse
    {
        $filters = $request->all();
        $attractions = $this->searchService->advancedSearch($filters);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'total' => $attractions->count(),
            'filters_applied' => array_filter($filters),
            'message' => 'Búsqueda avanzada realizada exitosamente'
        ]);
    }

    /**
     * Obtener tipos de turismo disponibles
     */
    public function tourismTypes(): JsonResponse
    {
        $types = $this->searchService->getTourismTypes();

        return response()->json([
            'success' => true,
            'data' => $types,
            'message' => 'Tipos de turismo obtenidos exitosamente'
        ]);
    }

    /**
     * Búsqueda por ubicación geográfica
     */
    public function searchByLocation(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:500'
        ]);

        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $radius = $request->get('radius', 50);

        $attractions = $this->searchService->searchByLocation($latitude, $longitude, $radius);

        return response()->json([
            'success' => true,
            'data' => $attractions,
            'total' => $attractions->count(),
            'search_center' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'radius_km' => $radius
            ],
            'message' => 'Búsqueda por ubicación realizada exitosamente'
        ]);
    }
}