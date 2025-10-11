<?php

namespace App\Features\Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Booking;
use App\Models\Tour;

class Commission extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\CommissionFactory::new();
    }

    protected $fillable = [
        'booking_id',
        'tour_id',
        'amount',
        'rate',
        'status',
        'period_month',
        'period_year',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rate' => 'decimal:4',
        'paid_at' => 'datetime'
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Bs. ' . number_format($this->amount, 2);
    }

    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate * 100, 2) . '%';
    }
}