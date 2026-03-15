<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class ProductTemplate extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'partner_id',
        'name',
        'base_price',
        'default_selling_price',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'default_selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the partner that owns this template.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}