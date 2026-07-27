<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CourierLocationSnapshot extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'courier_id',
        'snapshot_type',
        'location',
    ];

    protected $hidden = [
        'location',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
}
