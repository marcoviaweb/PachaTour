<?php

namespace App\Features\Search\Services;

use App\Features\Attractions\Models\Attraction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FilterService
{
    /**
     * Aplicar filtros avanzados a atractivos
     */
    public function applyFilters(array $filters): Collection
    {
        $query = Attraction::query()
            ->with(['department', 'media', 'reviews', 'tours'])
            ->where('is_active', true);

        // Filtro por texto de búsqueda
        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('short_description', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        // Filtro por departamento
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // Filtro por tipo de turismo
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filtro por rango de precios (buscar en tours relacionados)
        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $tourIds = \DB::table('tours')
                ->where('is_active', true);
            
            if (!empty($filters['min_price'])) {
                $tourIds->where('price_per_person', '>=', $filters['min_price']);
            }
            if (!empty($filters['max_price'])) {
                $tourIds->where('price_per_person', '<=', $filters['max_price']);
            }
            
            $tourIds = $tourIds->pluck('id');
            
            if ($tourIds->isNotEmpty()) {
                $attractionIds = \DB::table('tour_attraction')
                    ->whereIn('tour_id', $tourIds)
                    ->pluck('attraction_id');
                
                $query->whereIn('id', $attractionIds);
            } else {
                // Si no hay tours en el rango de precio, no mostrar atractivos
                $query->whereRaw('1 = 0');
            }
        }

        // Filtro por valoración mínima
        if (!empty($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        // Filtro por nivel de dificultad
        if (!empty($filters['difficulty_level'])) {
            $query->where('difficulty_level', $filters['difficulty_level']);
        }

        // Filtro por duración estimada
        if (!empty($filters['min_duration']) || !empty($filters['max_duration'])) {
            if (!empty($filters['min_duration'])) {
                $query->where('estimated_duration', '>=', $filters['min_duration']);
            }
            if (!empty($filters['max_duration'])) {
                $query->where('estimated_duration', '<=', $filters['max_duration']);
            }
        }

        // Filtro por servicios/amenidades
        if (!empty($filters['amenities'])) {
            $amenities = is_array($filters['amenities']) ? $filters['amenities'] : [$filters['amenities']];
            foreach ($amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        // Filtro por destacados
        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $query->where('is_featured', (bool) $filters['is_featured']);
        }

        // Filtro por ubicación geográfica (distancia)
        if (!empty($filters['latitude']) && !empty($filters['longitude'])) {
            $radius = $filters['radius'] ?? 50; // Radio por defecto 50km
            $query = $this->applyLocationFilter($query, $filters['latitude'], $filters['longitude'], $radius);
        }

        // Aplicar ordenamiento
        $this->applySorting($query, $filters);

        return $query->get();
    }

    /**
     * Aplicar filtro de ubicación geográfica
     */
    private function applyLocationFilter(Builder $query, float $latitude, float $longitude, float $radiusKm): Builder
    {
        // Para SQLite (testing) usamos una aproximación simple
        if (config('database.default') === 'sqlite') {
            $latDiff = $radiusKm / 111;
            $lonDiff = $radiusKm / (111 * cos(deg2rad($latitude)));
            
            return $query->whereBetween('latitude', [$latitude - $latDiff, $latitude + $latDiff])
                        ->whereBetween('longitude', [$longitude - $lonDiff, $longitude + $lonDiff]);
        }
        
        // Para PostgreSQL usamos la fórmula de Haversine
        return $query->selectRaw("
                *,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude)))) AS distance
            ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radiusKm);
    }

    /**
     * Aplicar ordenamiento
     */
    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'rating':
                $query->orderBy('rating', $sortOrder);
                break;
            case 'price':
                // Ordenar por precio mínimo de tours relacionados
                $query->leftJoin('tour_attraction', 'attractions.id', '=', 'tour_attraction.attraction_id')
                      ->leftJoin('tours', 'tour_attraction.tour_id', '=', 'tours.id')
                      ->where('tours.is_active', true)
                      ->orderBy('tours.price_per_person', $sortOrder)
                      ->select('attractions.*')
                      ->distinct();
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortOrder);
                break;
            case 'popularity':
                $query->orderBy('visits_count', $sortOrder);
                break;
            case 'reviews_count':
                $query->orderBy('reviews_count', $sortOrder);
                break;
            case 'distance':
                if (isset($filters['latitude']) && isset($filters['longitude'])) {
                    // El ordenamiento por distancia se maneja en applyLocationFilter
                    $query->orderBy('distance', $sortOrder);
                } else {
                    $query->orderBy('name', 'asc');
                }
                break;
            default:
                $query->orderBy('name', 'asc');
        }
    }

    /**
     * Obtener opciones de filtros disponibles
     */
    public function getFilterOptions(): array
    {
        return [
            'types' => $this->getAvailableTypes(),
            'difficulty_levels' => $this->getAvailableDifficultyLevels(),
            'amenities' => $this->getAvailableAmenities(),
            'price_range' => $this->getPriceRange(),
            'rating_range' => $this->getRatingRange(),
            'duration_range' => $this->getDurationRange()
        ];
    }

    /**
     * Obtener tipos de turismo disponibles
     */
    private function getAvailableTypes(): array
    {
        return Attraction::distinct()
            ->whereNotNull('type')
            ->where('is_active', true)
            ->pluck('type')
            ->toArray();
    }

    /**
     * Obtener niveles de dificultad disponibles
     */
    private function getAvailableDifficultyLevels(): array
    {
        return Attraction::distinct()
            ->whereNotNull('difficulty_level')
            ->where('is_active', true)
            ->pluck('difficulty_level')
            ->toArray();
    }

    /**
     * Obtener amenidades disponibles
     */
    private function getAvailableAmenities(): array
    {
        $amenities = Attraction::where('is_active', true)
            ->whereNotNull('amenities')
            ->pluck('amenities')
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        return array_filter($amenities);
    }

    /**
     * Obtener rango de precios
     */
    private function getPriceRange(): array
    {
        $prices = \DB::table('tours')
            ->join('tour_attraction', 'tours.id', '=', 'tour_attraction.tour_id')
            ->join('attractions', 'tour_attraction.attraction_id', '=', 'attractions.id')
            ->where('attractions.is_active', true)
            ->where('tours.is_active', true)
            ->selectRaw('MIN(tours.price_per_person) as min_price, MAX(tours.price_per_person) as max_price')
            ->first();

        return [
            'min' => $prices->min_price ?? 0,
            'max' => $prices->max_price ?? 1000
        ];
    }

    /**
     * Obtener rango de valoraciones
     */
    private function getRatingRange(): array
    {
        $ratings = Attraction::where('is_active', true)
            ->selectRaw('MIN(rating) as min_rating, MAX(rating) as max_rating')
            ->first();

        return [
            'min' => $ratings->min_rating ?? 0,
            'max' => $ratings->max_rating ?? 5
        ];
    }

    /**
     * Obtener rango de duración
     */
    private function getDurationRange(): array
    {
        $durations = Attraction::where('is_active', true)
            ->whereNotNull('estimated_duration')
            ->selectRaw('MIN(estimated_duration) as min_duration, MAX(estimated_duration) as max_duration')
            ->first();

        return [
            'min' => $durations->min_duration ?? 0,
            'max' => $durations->max_duration ?? 480 // 8 horas por defecto
        ];
    }

    /**
     * Aplicar filtros dinámicos basados en parámetros de consulta
     */
    public function applyDynamicFilters(array $queryParams): Collection
    {
        $filters = [];

        // Mapear parámetros de consulta a filtros
        $filterMappings = [
            'q' => 'search',
            'search' => 'search',
            'department' => 'department_id',
            'type' => 'type',
            'min_price' => 'min_price',
            'max_price' => 'max_price',
            'rating' => 'min_rating',
            'difficulty' => 'difficulty_level',
            'amenities' => 'amenities',
            'featured' => 'is_featured',
            'lat' => 'latitude',
            'lng' => 'longitude',
            'radius' => 'radius',
            'sort' => 'sort_by',
            'order' => 'sort_order'
        ];

        foreach ($filterMappings as $param => $filter) {
            if (isset($queryParams[$param]) && $queryParams[$param] !== '') {
                $filters[$filter] = $queryParams[$param];
            }
        }

        return $this->applyFilters($filters);
    }
}