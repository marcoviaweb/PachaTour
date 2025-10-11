<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'booking_id',
        'rating',
        'title',
        'comment',
        'detailed_ratings',
        'pros',
        'cons',
        'would_recommend',
        'visit_date',
        'travel_type',
        'status',
        'moderation_notes',
        'moderated_at',
        'moderated_by',
        'helpful_votes',
        'not_helpful_votes',
        'is_verified',
        'language',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'detailed_ratings' => 'array',
        'pros' => 'array',
        'cons' => 'array',
        'would_recommend' => 'boolean',
        'moderated_at' => 'datetime',
        'helpful_votes' => 'integer',
        'not_helpful_votes' => 'integer',
        'is_verified' => 'boolean',
    ];

    // Constantes para estados
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_HIDDEN = 'hidden';

    const STATUSES = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_APPROVED => 'Aprobada',
        self::STATUS_REJECTED => 'Rechazada',
        self::STATUS_HIDDEN => 'Oculta',
    ];

    // Constantes para tipos de viaje
    const TRAVEL_SOLO = 'solo';
    const TRAVEL_COUPLE = 'couple';
    const TRAVEL_FAMILY = 'family';
    const TRAVEL_FRIENDS = 'friends';
    const TRAVEL_BUSINESS = 'business';

    const TRAVEL_TYPES = [
        self::TRAVEL_SOLO => 'Solo',
        self::TRAVEL_COUPLE => 'En pareja',
        self::TRAVEL_FAMILY => 'En familia',
        self::TRAVEL_FRIENDS => 'Con amigos',
        self::TRAVEL_BUSINESS => 'Viaje de negocios',
    ];

    // Relaciones

    /**
     * Usuario que escribió la reseña
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Entidad reseñada (polimórfica)
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Reserva relacionada (opcional)
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Usuario que moderó la reseña
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    // Scopes

    /**
     * Solo reseñas aprobadas
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Solo reseñas pendientes
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Solo reseñas verificadas
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * Filtrar por calificación mínima
     */
    public function scopeMinRating(Builder $query, float $rating): Builder
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Filtrar por idioma
     */
    public function scopeInLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    /**
     * Ordenar por utilidad (votos positivos)
     */
    public function scopeByHelpfulness(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('helpful_votes', $direction);
    }

    /**
     * Ordenar por fecha
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Buscar en comentarios
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('comment', 'LIKE', "%{$search}%");
        });
    }

    // Accessors & Mutators

    /**
     * Nombre del estado en español
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Nombre del tipo de viaje en español
     */
    public function getTravelTypeNameAttribute(): string
    {
        return self::TRAVEL_TYPES[$this->travel_type] ?? $this->travel_type;
    }

    /**
     * Calificación con estrellas
     */
    public function getStarsAttribute(): string
    {
        $fullStars = floor($this->rating);
        $halfStar = ($this->rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;
        
        return str_repeat('★', $fullStars) . 
               str_repeat('☆', $halfStar) . 
               str_repeat('☆', $emptyStars);
    }

    /**
     * Porcentaje de utilidad
     */
    public function getHelpfulnessPercentageAttribute(): float
    {
        $totalVotes = $this->helpful_votes + $this->not_helpful_votes;
        
        if ($totalVotes == 0) {
            return 0;
        }
        
        return round(($this->helpful_votes / $totalVotes) * 100, 2);
    }

    /**
     * Verificar si es una reseña reciente
     */
    public function getIsRecentAttribute(): bool
    {
        return $this->created_at->gt(now()->subDays(30));
    }

    /**
     * Verificar si es una reseña larga
     */
    public function getIsDetailedAttribute(): bool
    {
        return strlen($this->comment) > 200;
    }

    /**
     * Obtener resumen del comentario
     */
    public function getSummaryAttribute(): string
    {
        if (strlen($this->comment) <= 150) {
            return $this->comment;
        }
        
        return substr($this->comment, 0, 147) . '...';
    }

    // Métodos auxiliares

    /**
     * Aprobar reseña
     */
    public function approve(int $moderatorId = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId,
        ]);
        
        // Actualizar calificación de la entidad reseñada
        $this->updateReviewableRating();
    }

    /**
     * Rechazar reseña
     */
    public function reject(string $reason, int $moderatorId = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'moderation_notes' => $reason,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId,
        ]);
    }

    /**
     * Ocultar reseña
     */
    public function hide(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_HIDDEN,
            'moderation_notes' => $reason,
        ]);
        
        // Actualizar calificación de la entidad reseñada
        $this->updateReviewableRating();
    }

    /**
     * Marcar como verificada
     */
    public function verify(): void
    {
        $this->update(['is_verified' => true]);
    }

    /**
     * Votar como útil
     */
    public function voteHelpful(): void
    {
        $this->increment('helpful_votes');
    }

    /**
     * Votar como no útil
     */
    public function voteNotHelpful(): void
    {
        $this->increment('not_helpful_votes');
    }

    /**
     * Actualizar calificación de la entidad reseñada
     */
    protected function updateReviewableRating(): void
    {
        $reviewable = $this->reviewable;
        
        if (method_exists($reviewable, 'updateRating')) {
            $reviewable->updateRating();
        }
    }

    /**
     * Verificar si el usuario puede editar esta reseña
     */
    public function canBeEditedBy(User $user): bool
    {
        // Solo el autor puede editar, y solo si está pendiente o en las primeras 24 horas
        if ($this->user_id !== $user->id) {
            return false;
        }
        
        if ($this->status === self::STATUS_PENDING) {
            return true;
        }
        
        return $this->created_at->gt(now()->subHours(24));
    }

    /**
     * Verificar si se puede moderar
     */
    public function canBeModerated(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    /**
     * Obtener calificaciones detalladas formateadas
     */
    public function getDetailedRatingsFormatted(): array
    {
        if (!$this->detailed_ratings) {
            return [];
        }
        
        $labels = [
            'service' => 'Servicio',
            'value' => 'Relación calidad-precio',
            'cleanliness' => 'Limpieza',
            'location' => 'Ubicación',
            'facilities' => 'Instalaciones',
            'staff' => 'Personal',
            'food' => 'Comida',
            'activities' => 'Actividades',
        ];
        
        $formatted = [];
        foreach ($this->detailed_ratings as $key => $rating) {
            $formatted[] = [
                'aspect' => $labels[$key] ?? ucfirst($key),
                'rating' => $rating,
                'stars' => str_repeat('★', floor($rating)) . str_repeat('☆', 5 - floor($rating)),
            ];
        }
        
        return $formatted;
    }

    /**
     * Generar resumen de la reseña para moderación
     */
    public function getModerationSummary(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user->name,
            'rating' => $this->rating,
            'title' => $this->title,
            'comment_length' => strlen($this->comment),
            'language' => $this->language,
            'is_verified' => $this->is_verified,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'reviewable_type' => class_basename($this->reviewable_type),
            'reviewable_name' => $this->reviewable->name ?? 'N/A',
        ];
    }
}
