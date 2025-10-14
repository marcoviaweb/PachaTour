<?php

namespace App\Features\Users\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'performed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime'
    ];

    /**
     * Get the user that performed this activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get activities for a specific action
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get recent activities
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted metadata for display
     */
    public function getFormattedMetadataAttribute(): string
    {
        if (empty($this->metadata)) {
            return '';
        }

        $formatted = [];
        foreach ($this->metadata as $key => $value) {
            $formatted[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
        }

        return implode(', ', $formatted);
    }
}