<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'order_id',
        'staff_id',
        'delivery_address',
        'scheduled_at',
        'notes',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'delivered_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'approved_at'  => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /* ── Relationships ── */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /* ── Computed ── */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pending Approval',
            'approved'  => 'Out for Delivery',
            'delivered' => 'Delivered',
            'rejected'  => 'Rejected',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusCssAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'dlv-status-pending',
            'approved'  => 'dlv-status-approved',
            'delivered' => 'dlv-status-delivered',
            'rejected'  => 'dlv-status-rejected',
            default     => '',
        };
    }
}
