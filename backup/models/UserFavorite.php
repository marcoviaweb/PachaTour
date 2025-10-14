<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attraction_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User who favorited the attraction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Favorited attraction
     */
    public function attraction(): BelongsTo
    {
        return $this->belongsTo(Attraction::class);
    }

    /**
     * Scope to get favorites for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get favorites for a specific attraction
     */
    public function scopeForAttraction($query, int $attractionId)
    {
        return $query->where('attraction_id', $attractionId);
    }
}