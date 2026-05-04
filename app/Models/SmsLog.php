<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsLog extends Model
{
    protected $fillable = [
        'sender_id',
        'recipient_type',
        'message',
        'recipient_count',
        'sent_count',
        'failed_count',
        'status',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(SmsLogRecipient::class);
    }

    public function recipientTypeLabel(): string
    {
        return match ($this->recipient_type) {
            'all'       => 'All Users',
            'customers' => 'All Customers',
            'staff'     => 'All Staff',
            'custom'    => 'Custom Selection',
            default     => ucfirst($this->recipient_type),
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'completed' => 'Completed',
            'partial'   => 'Partial',
            'failed'    => 'Failed',
            'sending'   => 'Sending…',
            default     => ucfirst($this->status),
        };
    }
}
