<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierProfile extends Model
{
    protected $fillable = [
        'user_id',
        'national_code',
        'vehicle_type',
        'vehicle_number',
        'rating',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currentLocation()
    {
        return $this->hasOne(
            CourierCurrentLocation::class,
            'courier_id',
            'user_id'
        );
    }
}
