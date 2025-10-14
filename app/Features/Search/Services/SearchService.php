<?php

namespace App\Features\Search\Services;

use App\Features\Attractions\Models\Attraction;
use App\Features\Departments\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Buscar atractivos por múltiples criterios
     */
    public function searchAttractions(array $criteria): Collection
    {
        $query = Attraction::query()
            ->with(['department', 'media', 'reviews'])
            ->where('is_active', true);

        // Búsqueda por nombre
        if (!empty($criteria['name'])) {
            $query->where('name', 'LIKE', '%' . $criteria['name'] . '%');
        }

        // Búsqueda por departamento
        if (!empty($criteria['department_id'])) {
            $query->where('department_id', $criteria['department_id']);
        }

        // Búsqueda por tipo de turismo
        if (!empty($criteria['tourism_type'])) {
            $query->where('type', $criteria['tourism_type']);
        }

        // Búsqueda por texto general (nombre, descripción)
        if (!empty($criteria['query'])) {
            $query->where(function (Builder $q) use ($criteria) {
                $q->where('name', 'LIKE', '%' . $criteria['query'] . '%')
                  ->orWhere('description', 'LIKE', '%' . $criteria['query'] . '%')
                  ->orWhere('short_description', 'LIKE', '%' . $criteria['query'] . '%');
            });
        }

        return $query->get();
    }

    /**
     * Obtener sugerencias para autocompletado
     */
    public function getSuggestions(string $term, int $limit = 10): array
    {
        $suggestions = [];

        // Sugerencias de atractivos
        $attractions = Attraction::active()
            ->search($term)
            ->with('department')
            ->limit(ceil($limit * 0.6)) // 60% para atractivos
            ->get()
            ->map(function ($attraction) {
                return [
                    'id' => $attraction->id,
                    'slug' => $attraction->slug,
                    'name' => $attraction->name,
                    'type' => 'attraction',
                    'department' => $attraction->department->name ?? null
                ];
            })
            ->toArray();

        // Sugerencias de departamentos
        $departments = Department::where('name', 'LIKE', '%' . $term . '%')
            ->limit(ceil($limit * 0.3)) // 30% para departamentos
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'type' => 'department',
                    'department' => null
                ];
            })
            ->toArray();

        // Sugerencias de tipos de turismo
        $tourismTypes = [
            'cultural' => 'Turismo Cultural',
            'natural' => 'Turismo Natural',
            'adventure' => 'Turismo de Aventura',
            'historical' => 'Turismo Histórico',
            'religious' => 'Turismo Religioso',
            'gastronomic' => 'Turismo Gastronómico',
            'ecological' => 'Turismo Ecológico'
        ];

        $typesSuggestions = [];
        foreach ($tourismTypes as $key => $label) {
            if (stripos($label, $term) !== false || stripos($key, $term) !== false) {
                $typesSuggestions[] = [
                    'id' => $key,
                    'name' => $label,
                    'type' => 'tourism_type',
                    'department' => null
                ];
            }
        }

        // Combinar todas las sugerencias
        $allSuggestions = array_merge($attractions, $departments, $typesSuggestions);
        
        return array_slice($allSuggestions, 0, $limit);
    }

    /**
     * Búsqueda avanzada con múltiples filtros
     */
    public function advancedSearch(array $filters): Collection
    {
        $query = Attraction::query()
            ->with(['department', 'media', 'reviews', 'tours'])
            ->where('is_active', true);

        // Filtro por texto
        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        // Filtro por departamento
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // Filtro por tipo de turismo
        if (!empty($filters['tourism_type'])) {
            $query->where('type', $filters['tourism_type']);
        }

        // Ordenamiento
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortOrder);
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')
                      ->orderBy('reviews_avg_rating', $sortOrder);
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        return $query->get();
    }

    /**
     * Obtener tipos de turismo disponibles
     */
    public function getTourismTypes(): array
    {
        return Attraction::distinct()
            ->whereNotNull('type')
            ->pluck('type')
            ->toArray();
    }

    /**
     * Búsqueda por coordenadas geográficas (proximidad)
     */
    public function searchByLocation(float $latitude, float $longitude, float $radiusKm = 50): Collection
    {
        // Para SQLite (testing) usamos una aproximación simple
        if (config('database.default') === 'sqlite') {
            // Aproximación simple usando diferencias de coordenadas
            $latDiff = $radiusKm / 111; // Aproximadamente 111 km por grado de latitud
            $lonDiff = $radiusKm / (111 * cos(deg2rad($latitude))); // Ajustado por latitud
            
            return Attraction::query()
                ->with(['department', 'media'])
                ->where('is_active', true)
                ->whereBetween('latitude', [$latitude - $latDiff, $latitude + $latDiff])
                ->whereBetween('longitude', [$longitude - $lonDiff, $longitude + $lonDiff])
                ->get();
        }
        
        // Para PostgreSQL usamos la fórmula de Haversine
        $query = Attraction::query()
            ->with(['department', 'media'])
            ->where('is_active', true)
            ->selectRaw("
                *,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude)))) AS distance
            ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');

        return $query->get();
    }
}