<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    protected $fillable = [
        'delivery_number',
        'staff_id',
        'scheduled_at',
        'notes',
        'status',
        'dispatched_at',
        'completed_at',
    ];

    protected $casts = [
        'staff_id'      => 'integer',
        'scheduled_at'  => 'date',
        'dispatched_at' => 'datetime',
        'completed_at'  => 'datetime',
    ];

    /* ── Relationships ── */

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(DeliveryAllocation::class);
    }

    /* ── Helpers ── */

    public function totalAmount(): float
    {
        return (float) $this->allocations->sum('total_amount');
    }

    public function customerCount(): int
    {
        return $this->allocations->count();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Pending',
            'dispatched' => 'Dispatched',
            'completed'  => 'Completed',
            default      => ucfirst($this->status),
        };
    }

    public function getStatusCssAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'status-pending',
            'dispatched' => 'status-approved',
            'completed'  => 'status-delivered',
            default      => '',
        };
    }
}
