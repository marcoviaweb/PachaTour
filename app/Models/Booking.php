<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'user_id',
        'tour_schedule_id',
        'participants_count',
        'total_amount',
        'currency',
        'commission_rate',
        'commission_amount',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'participant_details',
        'special_requests',
        'notes',
        'contact_name',
        'contact_email',
        'contact_phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
        'refund_amount',
        'refunded_at',
    ];

    protected $casts = [
        'participants_count' => 'integer',
        'total_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'participant_details' => 'array',
        'special_requests' => 'array',
        'refund_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Constantes para estados de reserva
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_NO_SHOW = 'no_show';

    const STATUSES = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_CONFIRMED => 'Confirmada',
        self::STATUS_PAID => 'Pagada',
        self::STATUS_CANCELLED => 'Cancelada',
        self::STATUS_COMPLETED => 'Completada',
        self::STATUS_REFUNDED => 'Reembolsada',
        self::STATUS_NO_SHOW => 'No se presentó',
    ];

    // Constantes para estados de pago
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PARTIAL = 'partial';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_REFUNDED = 'refunded';
    const PAYMENT_FAILED = 'failed';

    const PAYMENT_STATUSES = [
        self::PAYMENT_PENDING => 'Pendiente',
        self::PAYMENT_PARTIAL => 'Parcial',
        self::PAYMENT_PAID => 'Pagado',
        self::PAYMENT_REFUNDED => 'Reembolsado',
        self::PAYMENT_FAILED => 'Fallido',
    ];

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = $booking->generateBookingNumber();
            }
        });
    }

    // Relaciones

    /**
     * Usuario que hizo la reserva
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Horario del tour reservado
     */
    public function tourSchedule(): BelongsTo
    {
        return $this->belongsTo(TourSchedule::class);
    }

    /**
     * Tour reservado (a través del horario)
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_schedule_id', 'id')
                    ->through('tourSchedule');
    }

    /**
     * Reseñas de esta reserva
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Scopes

    /**
     * Filtrar por estado
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Reservas confirmadas
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_PAID, self::STATUS_COMPLETED]);
    }

    /**
     * Reservas activas (no canceladas ni reembolsadas)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_REFUNDED]);
    }

    /**
     * Reservas pendientes de pago
     */
    public function scopePendingPayment(Builder $query): Builder
    {
        return $query->where('payment_status', self::PAYMENT_PENDING)
                     ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Reservas de un usuario específico
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Reservas por rango de fechas
     */
    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereHas('tourSchedule', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate]);
        });
    }

    /**
     * Reservas de hoy
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereHas('tourSchedule', function ($q) {
            $q->where('date', now()->toDateString());
        });
    }

    /**
     * Reservas próximas (siguientes 7 días)
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereHas('tourSchedule', function ($q) {
            $q->whereBetween('date', [now()->toDateString(), now()->addDays(7)->toDateString()]);
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
     * Nombre del estado de pago en español
     */
    public function getPaymentStatusNameAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Monto total formateado
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 2) . ' ' . $this->currency;
    }

    /**
     * Comisión formateada
     */
    public function getFormattedCommissionAttribute(): string
    {
        return number_format($this->commission_amount, 2) . ' ' . $this->currency;
    }

    /**
     * Verificar si se puede cancelar
     */
    public function getCanBeCancelledAttribute(): bool
    {
        if (in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_COMPLETED, self::STATUS_REFUNDED])) {
            return false;
        }
        
        // No se puede cancelar si el tour es en menos de 24 horas
        $tourDateTime = $this->tourSchedule->start_date_time;
        return $tourDateTime->gt(now()->addHours(24));
    }

    /**
     * Verificar si se puede reembolsar
     */
    public function getCanBeRefundedAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_CONFIRMED]) 
               && $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * Verificar si se puede revisar
     */
    public function getCanBeReviewedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED 
               && !$this->reviews()->exists();
    }

    // Métodos auxiliares

    /**
     * Generar número de reserva único
     */
    public function generateBookingNumber(): string
    {
        do {
            $number = 'PT' . now()->format('Ymd') . strtoupper(Str::random(6));
        } while (self::where('booking_number', $number)->exists());
        
        return $number;
    }

    /**
     * Confirmar reserva
     */
    public function confirm(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }
        
        // Verificar disponibilidad
        if (!$this->tourSchedule->canBeBooked() || 
            $this->tourSchedule->getRemainingSpots() < $this->participants_count) {
            return false;
        }
        
        // Reservar cupos
        $this->tourSchedule->reserveSpots($this->participants_count);
        
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
        
        return true;
    }

    /**
     * Marcar como pagada
     */
    public function markAsPaid(string $paymentMethod, string $reference = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'payment_status' => self::PAYMENT_PAID,
            'payment_method' => $paymentMethod,
            'payment_reference' => $reference,
        ]);
    }

    /**
     * Cancelar reserva
     */
    public function cancel(string $reason = null): bool
    {
        if (!$this->can_be_cancelled) {
            return false;
        }
        
        // Liberar cupos si estaba confirmada
        if (in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PAID])) {
            $this->tourSchedule->releaseSpots($this->participants_count);
        }
        
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
        
        return true;
    }

    /**
     * Procesar reembolso
     */
    public function refund(float $amount = null): bool
    {
        if (!$this->can_be_refunded) {
            return false;
        }
        
        $refundAmount = $amount ?? $this->total_amount;
        
        $this->update([
            'status' => self::STATUS_REFUNDED,
            'payment_status' => self::PAYMENT_REFUNDED,
            'refund_amount' => $refundAmount,
            'refunded_at' => now(),
        ]);
        
        // Liberar cupos
        $this->tourSchedule->releaseSpots($this->participants_count);
        
        return true;
    }

    /**
     * Marcar como completada
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Marcar como no show
     */
    public function markAsNoShow(): void
    {
        $this->update(['status' => self::STATUS_NO_SHOW]);
    }

    /**
     * Calcular comisión
     */
    public function calculateCommission(): void
    {
        $commissionAmount = ($this->total_amount * $this->commission_rate) / 100;
        $this->update(['commission_amount' => $commissionAmount]);
    }

    /**
     * Obtener información del tour
     */
    public function getTourInfo(): array
    {
        $schedule = $this->tourSchedule;
        $tour = $schedule->tour;
        
        return [
            'tour_name' => $tour->name,
            'date' => $schedule->date->format('d/m/Y'),
            'time' => $schedule->start_time->format('H:i'),
            'duration' => $tour->formatted_duration,
            'meeting_point' => $tour->meeting_point,
            'guide' => $schedule->guide_name,
        ];
    }

    /**
     * Enviar confirmación por email
     */
    public function sendConfirmationEmail(): void
    {
        // TODO: Implementar envío de email
        // Mail::to($this->contact_email)->send(new BookingConfirmation($this));
    }

    /**
     * Enviar recordatorio
     */
    public function sendReminder(): void
    {
        // TODO: Implementar envío de recordatorio
        // Mail::to($this->contact_email)->send(new BookingReminder($this));
    }
}
