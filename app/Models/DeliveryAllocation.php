<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryAllocation extends Model
{
    protected $fillable = [
        'delivery_id',
        'customer_id',
        'total_amount',
        'notes',
        'allocation_date',
    ];

    protected $casts = [
        'total_amount'    => 'decimal:2',
        'allocation_date' => 'date',
    ];

    /* ── Relationships ── */

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryAllocationItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'delivery_allocation_id');
    }

    /* ── Helpers ── */

    public function paidAmount(): float
    {
        return (float) $this->payments()->where('status', 'approved')->sum('amount');
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->total_amount - $this->paidAmount());
    }

    public function isFullyPaid(): bool
    {
        return $this->remainingAmount() <= 0;
    }
}
