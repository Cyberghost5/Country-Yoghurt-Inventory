<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'payment_number',
        'amount',
        'payment_method',
        'reference',
        'proof_path',
        'notes',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    /* ── Relationships ── */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /* ── Computed ── */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default    => ucfirst($this->status),
        };
    }

    public function getStatusCssAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'pay-status-pending',
            'approved' => 'pay-status-approved',
            'rejected' => 'pay-status-rejected',
            default    => '',
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'bank_transfer' => 'Bank Transfer',
            'cash'          => 'Cash',
            'pos'           => 'POS',
            'mobile_money'  => 'Mobile Money',
            default         => ucwords(str_replace('_', ' ', $this->payment_method)),
        };
    }
}
