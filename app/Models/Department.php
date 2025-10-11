<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'capital',
        'description',
        'short_description',
        'latitude',
        'longitude',
        'image_path',
        'gallery',
        'population',
        'area_km2',
        'climate',
        'languages',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'gallery' => 'array',
        'languages' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'area_km2' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'population' => 'integer',
    ];

    // Relaciones

    /**
     * Atractivos turísticos del departamento
     */
    public function attractions(): HasMany
    {
        return $this->hasMany(Attraction::class);
    }

    /**
     * Atractivos activos del departamento
     */
    public function activeAttractions(): HasMany
    {
        return $this->hasMany(Attraction::class)->where('is_active', true);
    }

    /**
     * Archivos multimedia del departamento
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Imágenes del departamento
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->where('type', 'image');
    }

    /**
     * Reseñas del departamento
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    // Scopes

    /**
     * Solo departamentos activos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Ordenar por sort_order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Buscar por nombre o capital
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
              ->orWhere('capital', 'ILIKE', "%{$search}%")
              ->orWhere('description', 'ILIKE', "%{$search}%");
        });
    }

    // Accessors & Mutators

    /**
     * URL de la imagen principal
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    /**
     * Número de atractivos activos
     */
    public function getAttractionsCountAttribute(): int
    {
        return $this->activeAttractions()->count();
    }

    /**
     * Calificación promedio
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->where('status', 'approved')->avg('rating') ?? 0;
    }

    // Métodos auxiliares

    /**
     * Obtener la ruta del departamento
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Verificar si tiene atractivos
     */
    public function hasAttractions(): bool
    {
        return $this->attractions()->exists();
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
