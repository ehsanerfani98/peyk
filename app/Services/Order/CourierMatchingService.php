<?php

namespace App\Services\Order;

use App\Models\CourierCurrentLocation;
use App\Models\Order;
use App\Models\Setting;

final class CourierMatchingService
{
    /**
     * نزدیک‌ترین پیک آنلاین و بدون سفارش جاری را (بر اساس موقعیت فرستنده) پیدا می‌کند.
     * پیک‌هایی که قبلا این سفارش را رد کرده‌اند از نتیجه حذف می‌شوند.
     */
    public function findCandidate(Order $order): ?CourierCurrentLocation
    {
        $maxDistance = (int) Setting::getValue('courier_search.max_distance_meters', config('courier_search.max_distance_meters', 5000));

        return CourierCurrentLocation::query()
            ->available()
            ->nearestTo((float) $order->sender_lat, (float) $order->sender_lng, $maxDistance)
            ->whereNotIn('courier_id', $order->excludedCourierIds())
            ->first();
    }
}
