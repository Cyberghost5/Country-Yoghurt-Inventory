<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLogRecipient extends Model
{
    protected $fillable = [
        'sms_log_id',
        'user_id',
        'name',
        'phone',
        'status',
    ];

    public function smsLog(): BelongsTo
    {
        return $this->belongsTo(SmsLog::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
