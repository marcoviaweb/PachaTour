<?php

namespace App\Features\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class TourSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'date',
        'start_time',
        'end_time',
        'available_spots',
        'booked_spots',
        'price_override',
        'status',
        'notes',
        'special_conditions',
        'guide_name',
        'guide_contact',
        'is_private',
        'weather_forecast',
        'weather_conditions',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'available_spots' => 'integer',
        'booked_spots' => 'integer',
        'price_override' => 'decimal:2',
        'special_conditions' => 'array',
        'is_private' => 'boolean',
        'weather_forecast' => 'decimal:1',
    ];

    // Constantes para estados
    const STATUS_AVAILABLE = 'available';
    const STATUS_FULL = 'full';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_AVAILABLE => 'Disponible',
        self::STATUS_FULL => 'Completo',
        self::STATUS_CANCELLED => 'Cancelado',
        self::STATUS_COMPLETED => 'Completado',
    ];

    // Relaciones

    /**
     * Tour al que pertenece este horario
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Reservas para este horario específico
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Reservas confirmadas
     */
    public function confirmedBookings(): HasMany
    {
        return $this->hasMany(Booking::class)
                    ->whereIn('status', ['confirmed', 'paid', 'completed']);
    }

    // Scopes

    /**
     * Solo horarios disponibles
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE)
                     ->where('date', '>=', now()->toDateString());
    }

    /**
     * Solo horarios con cupos disponibles
     */
    public function scopeWithSpots(Builder $query): Builder
    {
        return $query->whereRaw('available_spots > booked_spots');
    }

    /**
     * Filtrar por fecha
     */
    public function scopeOnDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('date', $date->toDateString());
    }

    /**
     * Filtrar por rango de fechas
     */
    public function scopeBetweenDates(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
    }

    /**
     * Próximos horarios
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('date', '>=', now()->toDateString())
                     ->orderBy('date')
                     ->orderBy('start_time');
    }

    /**
     * Horarios del día
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->where('date', now()->toDateString());
    }

    /**
     * Horarios de la semana
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('date', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString()
        ]);
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
     * Cupos restantes
     */
    public function getRemainingSpots(int $excludeBookingId = null): int
    {
        $bookedSpots = $this->booked_spots;
        
        // If excluding a specific booking, subtract its participants from booked spots
        if ($excludeBookingId) {
            $excludedBooking = $this->bookings()->find($excludeBookingId);
            if ($excludedBooking) {
                $bookedSpots -= $excludedBooking->participants_count;
            }
        }
        
        return max(0, $this->available_spots - $bookedSpots);
    }

    /**
     * Porcentaje de ocupación
     */
    public function getOccupancyPercentage(): float
    {
        if ($this->available_spots == 0) {
            return 0;
        }
        
        return round(($this->booked_spots / $this->available_spots) * 100, 2);
    }

    /**
     * Precio efectivo (con override si existe)
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->price_override ?? $this->tour->price_per_person;
    }

    /**
     * Fecha y hora de inicio combinadas
     */
    public function getStartDateTimeAttribute(): Carbon
    {
        return Carbon::parse($this->date->toDateString() . ' ' . $this->start_time->format('H:i:s'));
    }

    /**
     * Fecha y hora de fin combinadas
     */
    public function getEndDateTimeAttribute(): ?Carbon
    {
        if (!$this->end_time) {
            return null;
        }
        
        return Carbon::parse($this->date->toDateString() . ' ' . $this->end_time->format('H:i:s'));
    }

    /**
     * Verificar si está en el pasado
     */
    public function getIsPastAttribute(): bool
    {
        return $this->start_date_time->isPast();
    }

    /**
     * Verificar si es hoy
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Verificar si es mañana
     */
    public function getIsTomorrowAttribute(): bool
    {
        return $this->date->isTomorrow();
    }

    // Métodos auxiliares

    /**
     * Verificar si se puede reservar
     */
    public function canBeBooked(): bool
    {
        return $this->status === self::STATUS_AVAILABLE 
               && $this->getRemainingSpots() > 0 
               && !$this->is_past;
    }

    /**
     * Reservar cupos
     */
    public function reserveSpots(int $spots): bool
    {
        if ($this->getRemainingSpots() < $spots) {
            return false;
        }
        
        $this->increment('booked_spots', $spots);
        
        // Actualizar estado si se llenó
        if ($this->getRemainingSpots() == 0) {
            $this->update(['status' => self::STATUS_FULL]);
        }
        
        return true;
    }

    /**
     * Liberar cupos (por cancelación)
     */
    public function releaseSpots(int $spots): void
    {
        $this->decrement('booked_spots', $spots);
        
        // Actualizar estado si volvió a tener cupos
        if ($this->status === self::STATUS_FULL && $this->getRemainingSpots() > 0) {
            $this->update(['status' => self::STATUS_AVAILABLE]);
        }
    }

    /**
     * Cancelar horario
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $reason ? "Cancelado: {$reason}" : 'Cancelado'
        ]);
        
        // Notificar a las reservas existentes
        $this->bookings()->whereIn('status', ['pending', 'confirmed'])
                         ->update(['status' => 'cancelled']);
    }

    /**
     * Marcar como completado
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
        
        // Marcar reservas como completadas
        $this->confirmedBookings()
             ->where('status', '!=', 'completed')
             ->update(['status' => 'completed']);
    }

    /**
     * Verificar si necesita guía
     */
    public function needsGuide(): bool
    {
        return empty($this->guide_name) && $this->canBeBooked();
    }

    /**
     * Asignar guía
     */
    public function assignGuide(string $name, string $contact = null): void
    {
        $this->update([
            'guide_name' => $name,
            'guide_contact' => $contact
        ]);
    }

    /**
     * Obtener información del clima
     */
    public function getWeatherInfo(): array
    {
        return [
            'forecast' => $this->weather_forecast,
            'conditions' => $this->weather_conditions,
            'is_favorable' => $this->weather_forecast >= 7.0,
        ];
    }

    /**
     * Actualizar información del clima
     */
    public function updateWeather(float $forecast, string $conditions): void
    {
        $this->update([
            'weather_forecast' => $forecast,
            'weather_conditions' => $conditions
        ]);
    }
}
