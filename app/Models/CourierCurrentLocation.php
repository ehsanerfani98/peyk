<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[Fillable(['courier_id', 'order_id', 'location'])]
class CourierCurrentLocation extends Model
{
    const CREATED_AT = null;

    protected $primaryKey = 'courier_id';

    public $incrementing = false;

    protected $hidden = [
        'location',
    ];

    public function courier()
    {
        return $this->belongsTo(CourierProfile::class, 'courier_id', 'user_id');
    }

    public function scopeAvailable($query)
    {
        return $query
            ->whereNull('order_id')
            ->whereHas('courier', function ($q) {
                $q->where('status', 'online');
            });
    }

    public function scopeNearestTo($query, $latitude, $longitude, $maxDistance = null)
    {
        $point = "POINT($longitude $latitude)";

        $query->select('*')
            ->selectRaw(
                'ST_Distance_Sphere(
                    location,
                    ST_GeomFromText(?, 4326)
                ) AS distance',
                [$point]
            )
            ->orderBy('distance');

        if ($maxDistance) {
            $query->having('distance', '<=', $maxDistance);
        }

        return $query;
    }

    /**
     * به‌روزرسانی (یا درج در اولین بار) موقعیت لحظه‌ای پیک - الگوی UPSERT روی کلید اصلی courier_id.
     * این متود برای HTTP Polling مداوم اپ پیک استفاده می‌شود و رکورد جدید درج نمی‌کند.
     */
    public static function upsertLocation(int $courierId, float $latitude, float $longitude): void
    {
        DB::statement(
            'INSERT INTO courier_current_locations (courier_id, location, updated_at)
             VALUES (?, ST_GeomFromText(?, 4326), ?)
             ON DUPLICATE KEY UPDATE location = VALUES(location), updated_at = VALUES(updated_at)',
            [
                $courierId,
                sprintf('POINT(%F %F)', $longitude, $latitude),
                now(),
            ]
        );
    }
}
