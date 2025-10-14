<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'role',
        'phone',
        'birth_date',
        'gender',
        'nationality',
        'country',
        'city',
        'preferred_language',
        'interests',
        'bio',
        'avatar_path',
        'newsletter_subscription',
        'marketing_emails',
        'last_login_at',
        'last_login_ip',
        'is_active',
        'password_changed_at',
        'social_provider',
        'social_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'interests' => 'array',
        'birth_date' => 'date',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'newsletter_subscription' => 'boolean',
        'marketing_emails' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * User roles
     */
    const ROLE_VISITOR = 'visitor';
    const ROLE_TOURIST = 'tourist';
    const ROLE_ADMIN = 'admin';

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is tourist
     */
    public function isTourist(): bool
    {
        return $this->role === self::ROLE_TOURIST;
    }

    // Relaciones

    /**
     * Reservas del usuario
     */
    public function bookings()
    {
        return $this->hasMany(\App\Features\Tours\Models\Booking::class);
    }

    /**
     * Reseñas escritas por el usuario
     */
    public function reviews()
    {
        return $this->hasMany(\App\Features\Reviews\Models\Review::class);
    }

    /**
     * Reseñas moderadas por el usuario (si es admin)
     */
    public function moderatedReviews()
    {
        return $this->hasMany(\App\Features\Reviews\Models\Review::class, 'moderated_by');
    }

    /**
     * Actividades del usuario
     */
    public function activities()
    {
        return $this->hasMany(\App\Features\Users\Models\UserActivity::class);
    }

    // Scopes

    /**
     * Solo usuarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filtrar por rol
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Usuarios administradores
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * Usuarios turistas
     */
    public function scopeTourists($query)
    {
        return $query->where('role', self::ROLE_TOURIST);
    }
}