<?php

namespace App\Features\Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Features\Tours\Models\Booking;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'amount',
        'commission_amount',
        'operator_amount',
        'payment_method',
        'status',
        'payment_reference',
        'gateway_transaction_id',
        'gateway_data',
        'processed_at',
        'refunded_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'operator_amount' => 'decimal:2',
        'gateway_data' => 'array',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime'
    ];

    /**
     * Get the booking that owns the payment
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(string $transactionId = null): void
    {
        $this->update([
            'status' => 'completed',
            'gateway_transaction_id' => $transactionId,
            'processed_at' => now()
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed'
        ]);
    }

    /**
     * Mark payment as refunded
     */
    public function markAsRefunded(): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now()
        ]);
    }
}