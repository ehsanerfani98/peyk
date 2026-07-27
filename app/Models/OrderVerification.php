<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderVerification extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'type',
        'delivery_channel',
        'mobile',
        'code',
        'token',
        'attempts',
        'max_attempts',
        'locked_at',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'locked_at' => 'datetime',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /**
     * ثبت یک تلاش ناموفق برای تایید کد. در صورت رسیدن به حداکثر مجاز، رکورد قفل می‌شود.
     */
    public function registerFailedAttempt(): void
    {
        $this->increment('attempts');

        if ($this->attempts >= $this->max_attempts) {
            $this->update(['locked_at' => now()]);
        }
    }
}
