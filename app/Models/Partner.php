<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partner extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the product templates for this partner.
     */
    public function productTemplates(): HasMany
    {
        return $this->hasMany(ProductTemplate::class);
    }

    /**
     * Get the daily consignments for this partner.
     */
    public function dailyConsignments(): HasMany
    {
        return $this->hasMany(DailyConsignment::class);
    }
}