<?php

namespace App\Http\Controllers\Api\Courier;

use App\Events\Courier\CourierLocationUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Courier\UpdateLocationRequest;
use App\Models\CourierCurrentLocation;
use Illuminate\Http\JsonResponse;

final class CourierLocationController extends Controller
{
    /**
     * به‌روزرسانی مداوم موقعیت لحظه‌ای پیک (HTTP Polling) - رکورد courier_id ثابت می‌ماند
     * و صرفا location/updated_at آن به‌روز می‌شود (بدون درج رکورد جدید).
     *
     * هم‌زمان با ذخیره‌سازی، موقعیت جدید از طریق Reverb به مشتریان
     * متصل به کانال presence-courier.{courierId} ارسال می‌شود.
     */
    public function update(UpdateLocationRequest $request): JsonResponse
    {
        $courierId = $request->user()->id;
        $latitude = (float) $request->input('lat');
        $longitude = (float) $request->input('lng');

        CourierCurrentLocation::upsertLocation(
            courierId: $courierId,
            latitude: $latitude,
            longitude: $longitude,
        );
        // ---- اطلاع‌رسانی بلادرنگ موقعیت پیک (Reverb) ----
        CourierLocationUpdated::dispatch([
            'courier_id' => $courierId,
            'lat' => $latitude,
            'lng' => $longitude,
            'timestamp' => now()->toIso8601String(),
        ], $courierId);

        return response()->json(['status' => true]);
    }
}
