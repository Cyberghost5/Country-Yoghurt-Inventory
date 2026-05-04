<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'total_amount',
    ];

    protected $casts = [
        'approved_at'  => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    /* ── Relationships ── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /* ── Computed ── */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pending',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
            'delivered' => 'Delivered',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusCssAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'ord-status-pending',
            'approved'  => 'ord-status-approved',
            'rejected'  => 'ord-status-rejected',
            'delivered' => 'ord-status-delivered',
            default     => '',
        };
    }

    /* ── Payment helpers ── */

    public function paidAmount(): float
    {
        return (float) $this->payments()->where('status', 'approved')->sum('amount');
    }

    public function remainingAmount(): float
    {
        return round(max(0, (float) $this->total_amount - $this->paidAmount()), 2);
    }

    public function isFullyPaid(): bool
    {
        return $this->remainingAmount() <= 0;
    }
}
