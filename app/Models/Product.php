<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'category',
        'flavor',
        'size_label',
        'unit',
        'cost_price',
        'selling_price',
        'quantity',
        'reorder_level',
        'supplier_name',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'cost_price'    => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity'      => 'integer',
        'reorder_level' => 'integer',
    ];

    /* ── Relationships ── */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ── Computed attributes ── */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->quantity <= $this->reorder_level) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStockValueAttribute(): float
    {
        return round((float) $this->cost_price * $this->quantity, 2);
    }
}
