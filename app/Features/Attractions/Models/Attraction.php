<?php

namespace App\Features\Attractions\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use App\Features\Departments\Models\Department;
use App\Features\Tours\Models\Tour;
use App\Models\Media;
use App\Features\Reviews\Models\Review;

class Attraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'slug',
        'description',
        'short_description',
        'type',
        'latitude',
        'longitude',
        'address',
        'city',
        'entry_price',
        'currency',
        'opening_hours',
        'contact_info',
        'difficulty_level',
        'estimated_duration',
        'amenities',
        'restrictions',
        'best_season',
        'rating',
        'reviews_count',
        'visits_count',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'entry_price' => 'decimal:2',
        'opening_hours' => 'array',
        'contact_info' => 'array',
        'amenities' => 'array',
        'restrictions' => 'array',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'visits_count' => 'integer',
        'estimated_duration' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Constantes para tipos de atractivos
    const TYPE_NATURAL = 'natural';
    const TYPE_CULTURAL = 'cultural';
    const TYPE_HISTORICAL = 'historical';
    const TYPE_ADVENTURE = 'adventure';
    const TYPE_RELIGIOUS = 'religious';
    const TYPE_ARCHAEOLOGICAL = 'archaeological';
    const TYPE_URBAN = 'urban';
    const TYPE_GASTRONOMIC = 'gastronomic';

    const TYPES = [
        self::TYPE_NATURAL => 'Natural',
        self::TYPE_CULTURAL => 'Cultural',
        self::TYPE_HISTORICAL => 'Histórico',
        self::TYPE_ADVENTURE => 'Aventura',
        self::TYPE_RELIGIOUS => 'Religioso',
        self::TYPE_ARCHAEOLOGICAL => 'Arqueológico',
        self::TYPE_URBAN => 'Urbano',
        self::TYPE_GASTRONOMIC => 'Gastronómico',
    ];

    // Relaciones

    /**
     * Departamento al que pertenece el atractivo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Tours que incluyen este atractivo
     */
    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'tour_attraction')
                    ->withPivot(['visit_order', 'duration_minutes', 'notes', 'is_optional', 'arrival_time', 'departure_time'])
                    ->withTimestamps()
                    ->orderByPivot('visit_order');
    }

    /**
     * Archivos multimedia del atractivo
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Imágenes del atractivo
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->where('type', 'image');
    }

    /**
     * Videos del atractivo
     */
    public function videos(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->where('type', 'video');
    }

    /**
     * Reseñas del atractivo
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * Reseñas aprobadas del atractivo
     */
    public function approvedReviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable')->where('status', 'approved');
    }

    // Scopes

    /**
     * Solo atractivos activos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Solo atractivos destacados
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Filtrar por tipo
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Filtrar por departamento
     */
    public function scopeInDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Buscar por nombre o descripción
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';
        
        return $query->where(function ($q) use ($search, $operator) {
            $q->where('name', $operator, "%{$search}%")
              ->orWhere('description', $operator, "%{$search}%")
              ->orWhere('city', $operator, "%{$search}%");
        });
    }

    /**
     * Ordenar por calificación
     */
    public function scopeByRating(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('rating', $direction);
    }

    /**
     * Filtrar por rango de precios
     */
    public function scopePriceRange(Builder $query, ?float $min = null, ?float $max = null): Builder
    {
        if ($min !== null) {
            $query->where('entry_price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('entry_price', '<=', $max);
        }
        return $query;
    }

    /**
     * Cerca de coordenadas (radio en km)
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, float $radius = 50): Builder
    {
        return $query->whereRaw(
            "ST_DWithin(ST_MakePoint(longitude, latitude)::geography, ST_MakePoint(?, ?)::geography, ? * 1000)",
            [$lng, $lat, $radius]
        );
    }

    // Accessors & Mutators

    /**
     * Nombre del tipo en español
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * URL de la imagen principal
     */
    public function getMainImageAttribute(): ?string
    {
        $image = $this->images()->where('is_featured', true)->first() 
                ?? $this->images()->first();
        
        return $image ? $image->url : null;
    }

    /**
     * Precio formateado
     */
    public function getFormattedPriceAttribute(): string
    {
        if (!$this->entry_price) {
            return 'Gratis';
        }
        
        return number_format($this->entry_price, 2) . ' ' . $this->currency;
    }

    /**
     * Duración formateada
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->estimated_duration) {
            return 'No especificado';
        }
        
        $hours = intval($this->estimated_duration / 60);
        $minutes = $this->estimated_duration % 60;
        
        if ($hours > 0) {
            return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'm' : '');
        }
        
        return $minutes . ' minutos';
    }

    // Métodos auxiliares

    /**
     * Obtener la ruta del atractivo
     */
    public function getRouteKeyName(): string
    {
        // Use id for admin routes, slug for public routes
        if (request()->is('admin/*') || request()->is('api/admin/*')) {
            return 'id';
        }
        return 'slug';
    }

    /**
     * Incrementar contador de visitas
     */
    public function incrementVisits(): void
    {
        $this->increment('visits_count');
    }

    /**
     * Actualizar calificación promedio
     */
    public function updateRating(): void
    {
        $avgRating = $this->approvedReviews()->avg('rating') ?? 0;
        $reviewsCount = $this->approvedReviews()->count();
        
        $this->update([
            'rating' => round($avgRating, 2),
            'reviews_count' => $reviewsCount,
        ]);
    }

    /**
     * Verificar si está abierto ahora
     */
    public function isOpenNow(): bool
    {
        if (!$this->opening_hours) {
            return true; // Asumimos que está abierto si no hay horarios definidos
        }
        
        $now = now();
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc.
        
        if (!isset($this->opening_hours[$dayOfWeek])) {
            return false;
        }
        
        $hours = $this->opening_hours[$dayOfWeek];
        if ($hours === 'closed') {
            return false;
        }
        
        $currentTime = $now->format('H:i');
        return $currentTime >= $hours['open'] && $currentTime <= $hours['close'];
    }

    /**
     * Obtener coordenadas como array
     */
    public function getCoordinates(): ?array
    {
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }
        return null;
    }
}
