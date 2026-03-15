<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'master_pin',
        'store_pin',
        'is_active',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'master_pin',
        'store_pin',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Auto-hash Master PIN using SHA-256 on set.
     */
    protected function masterPin(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? hash('sha256', $value) : null,
        );
    }

    /**
     * Auto-hash Store PIN using SHA-256 on set.
     */
    protected function storePin(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? hash('sha256', $value) : null,
        );
    }


    /**
     * Get the shop sessions for this user.
     */
    public function shopSessions(): HasMany
    {
        return $this->hasMany(ShopSession::class);
    }
}