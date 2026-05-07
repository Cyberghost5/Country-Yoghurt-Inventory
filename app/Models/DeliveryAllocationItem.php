<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryAllocationItem extends Model
{
    protected $fillable = [
        'delivery_allocation_id',
        'product_name',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(DeliveryAllocation::class, 'delivery_allocation_id');
    }
}
