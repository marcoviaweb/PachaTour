<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Tour extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'type',
        'duration_days',
        'duration_hours',
        'price_per_person',
        'currency',
        'min_participants',
        'max_participants',
        'difficulty_level',
        'included_services',
        'excluded_services',
        'requirements',
        'what_to_bring',
        'meeting_point',
        'departure_time',
        'return_time',
        'itinerary',
        'guide_language',
        'available_languages',
        'rating',
        'reviews_count',
        'bookings_count',
        'is_featured',
        'is_active',
        'available_from',
        'available_until',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'duration_hours' => 'integer',
        'price_per_person' => 'decimal:2',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
        'included_services' => 'array',
        'excluded_services' => 'array',
        'requirements' => 'array',
        'what_to_bring' => 'array',
        'departure_time' => 'datetime:H:i',
        'return_time' => 'datetime:H:i',
        'itinerary' => 'array',
        'available_languages' => 'array',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'bookings_count' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'available_from' => 'date',
        'available_until' => 'date',
    ];

    // Constantes para tipos de tours
    const TYPE_DAY_TRIP = 'day_trip';
    const TYPE_MULTI_DAY = 'multi_day';
    const TYPE_ADVENTURE = 'adventure';
    const TYPE_CULTURAL = 'cultural';
    const TYPE_NATURE = 'nature';
    const TYPE_HISTORICAL = 'historical';
    const TYPE_GASTRONOMIC = 'gastronomic';
    const TYPE_PHOTOGRAPHY = 'photography';

    const TYPES = [
        self::TYPE_DAY_TRIP => 'Tour de un día',
        self::TYPE_MULTI_DAY => 'Tour de varios días',
        self::TYPE_ADVENTURE => 'Aventura',
        self::TYPE_CULTURAL => 'Cultural',
        self::TYPE_NATURE => 'Naturaleza',
        self::TYPE_HISTORICAL => 'Histórico',
        self::TYPE_GASTRONOMIC => 'Gastronómico',
        self::TYPE_PHOTOGRAPHY => 'Fotografía',
    ];

    // Constantes para niveles de dificultad
    const DIFFICULTY_EASY = 'easy';
    const DIFFICULTY_MODERATE = 'moderate';
    const DIFFICULTY_DIFFICULT = 'difficult';
    const DIFFICULTY_EXTREME = 'extreme';

    const DIFFICULTIES = [
        self::DIFFICULTY_EASY => 'Fácil',
        self::DIFFICULTY_MODERATE => 'Moderado',
        self::DIFFICULTY_DIFFICULT => 'Difícil',
        self::DIFFICULTY_EXTREME => 'Extremo',
    ];

    // Relaciones

    /**
     * Horarios del tour
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(TourSchedule::class);
    }

    /**
     * Horarios disponibles del tour
     */
    public function availableSchedules(): HasMany
    {
        return $this->hasMany(TourSchedule::class)
                    ->where('status', 'available')
                    ->where('date', '>=', now()->toDateString());
    }

    /**
     * Atractivos incluidos en el tour
     */
    public function attractions(): BelongsToMany
    {
        return $this->belongsToMany(Attraction::class, 'tour_attraction')
                    ->withPivot(['visit_order', 'duration_minutes', 'notes', 'is_optional', 'arrival_time', 'departure_time'])
                    ->withTimestamps()
                    ->orderByPivot('visit_order');
    }

    /**
     * Reservas del tour (a través de schedules)
     */
    public function bookings(): HasMany
    {
        return $this->hasManyThrough(Booking::class, TourSchedule::class);
    }

    /**
     * Archivos multimedia del tour
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Imágenes del tour
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->where('type', 'image');
    }

    /**
     * Reseñas del tour
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * Reseñas aprobadas del tour
     */
    public function approvedReviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable')->where('status', 'approved');
    }

    // Scopes

    /**
     * Solo tours activos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Solo tours destacados
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
     * Filtrar por nivel de dificultad
     */
    public function scopeByDifficulty(Builder $query, string $difficulty): Builder
    {
        return $query->where('difficulty_level', $difficulty);
    }

    /**
     * Filtrar por duración
     */
    public function scopeByDuration(Builder $query, int $days): Builder
    {
        return $query->where('duration_days', $days);
    }

    /**
     * Filtrar por rango de precios
     */
    public function scopePriceRange(Builder $query, ?float $min = null, ?float $max = null): Builder
    {
        if ($min !== null) {
            $query->where('price_per_person', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price_per_person', '<=', $max);
        }
        return $query;
    }

    /**
     * Tours disponibles en fechas específicas
     */
    public function scopeAvailableOn(Builder $query, Carbon $date): Builder
    {
        return $query->whereHas('availableSchedules', function ($q) use ($date) {
            $q->where('date', $date->toDateString());
        });
    }

    /**
     * Tours con disponibilidad
     */
    public function scopeWithAvailability(Builder $query): Builder
    {
        return $query->whereHas('availableSchedules');
    }

    /**
     * Buscar por nombre o descripción
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
              ->orWhere('description', 'ILIKE', "%{$search}%")
              ->orWhere('meeting_point', 'ILIKE', "%{$search}%");
        });
    }

    /**
     * Ordenar por calificación
     */
    public function scopeByRating(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('rating', $direction);
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
     * Nombre de la dificultad en español
     */
    public function getDifficultyNameAttribute(): string
    {
        return self::DIFFICULTIES[$this->difficulty_level] ?? $this->difficulty_level;
    }

    /**
     * Precio formateado
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_per_person, 2) . ' ' . $this->currency;
    }

    /**
     * Duración formateada
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_days > 1) {
            return $this->duration_days . ' días';
        }
        
        if ($this->duration_hours) {
            return $this->duration_hours . ' horas';
        }
        
        return '1 día';
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
     * Próximo horario disponible
     */
    public function getNextAvailableScheduleAttribute(): ?TourSchedule
    {
        return $this->availableSchedules()
                    ->where('available_spots', '>', 0)
                    ->orderBy('date')
                    ->orderBy('start_time')
                    ->first();
    }

    // Métodos auxiliares

    /**
     * Obtener la ruta del tour
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Verificar si está disponible en una fecha
     */
    public function isAvailableOn(Carbon $date): bool
    {
        if ($this->available_from && $date->lt($this->available_from)) {
            return false;
        }
        
        if ($this->available_until && $date->gt($this->available_until)) {
            return false;
        }
        
        return $this->availableSchedules()
                    ->where('date', $date->toDateString())
                    ->where('available_spots', '>', 0)
                    ->exists();
    }

    /**
     * Obtener disponibilidad para un rango de fechas
     */
    public function getAvailabilityForRange(Carbon $startDate, Carbon $endDate): array
    {
        $schedules = $this->availableSchedules()
                          ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                          ->get();
        
        $availability = [];
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            $daySchedules = $schedules->where('date', $current->toDateString());
            $availability[$current->toDateString()] = [
                'available' => $daySchedules->isNotEmpty(),
                'schedules' => $daySchedules->values(),
                'total_spots' => $daySchedules->sum('available_spots'),
            ];
            $current->addDay();
        }
        
        return $availability;
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
     * Actualizar contador de reservas
     */
    public function updateBookingsCount(): void
    {
        $bookingsCount = $this->bookings()
                              ->whereIn('status', ['confirmed', 'paid', 'completed'])
                              ->count();
        
        $this->update(['bookings_count' => $bookingsCount]);
    }

    /**
     * Verificar si tiene cupos disponibles
     */
    public function hasAvailableSpots(): bool
    {
        return $this->availableSchedules()
                    ->where('available_spots', '>', 0)
                    ->exists();
    }

    /**
     * Obtener departamentos que visita el tour
     */
    public function getDepartments(): array
    {
        return $this->attractions()
                    ->with('department')
                    ->get()
                    ->pluck('department')
                    ->unique('id')
                    ->values()
                    ->toArray();
    }
}
