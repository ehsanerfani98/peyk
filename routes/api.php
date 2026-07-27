<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Courier\CourierLocationController;
use App\Http\Controllers\Api\Order\CourierOfferController;
use App\Http\Controllers\Api\Order\OrderCancellationController;
use App\Http\Controllers\Api\Order\OrderController;
use App\Http\Controllers\Api\Order\OrderFulfillmentController;
use App\Http\Controllers\Api\Order\OrderRestartSearchController;
use App\Http\Controllers\Api\Order\ReviewController;
use App\Http\Controllers\Api\Profile\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Broadcast::routes([
    'middleware' => ['auth:sanctum'],
]);

Route::prefix('auth')->group(function () {

    // بدون نیاز به احراز هویت
    Route::post('otp/send', [AuthController::class, 'sendOtp']);
    Route::post('otp/verify', [AuthController::class, 'verifyOtp']);

    // نیازمند توکن sanctum معتبر
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/user', function (Request $request) {
            $user = $request->user();

            return [
                ...$user->toArray(),
                'profile_completed' => $user->isProfileCompleted(),
                'role' => $user->getRoleNames()->first(),
            ];
        });

        Route::post('logout', [AuthController::class, 'logout']);

        Route::prefix('profile')->group(function () {
            Route::put('/', [ProfileController::class, 'update']);
        });

    });

});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('orders')->group(function () {
        Route::post('/price-estimation', [OrderController::class, 'priceEstimation']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'orderList']);
        Route::get('/courier', [OrderController::class, 'orderCourierList']);
        Route::get('/{order_id}', [OrderController::class, 'orderDetail']);

        Route::prefix('{order}')->group(function () {
            // پذیرش/رد پیشنهاد سفارش توسط پیک
            Route::post('accept', [CourierOfferController::class, 'accept']);
            Route::post('reject', [CourierOfferController::class, 'reject']);

            // لغو سفارش
            Route::post('cancel/customer', [OrderCancellationController::class, 'cancelByCustomer']);
            Route::post('cancel/courier', [OrderCancellationController::class, 'cancelByCourier']);

            // جستجوی مجدد پیک (پس از COURIER_NOT_FOUND)
            Route::post('restart-courier-search', OrderRestartSearchController::class);

            // تحویل گرفتن بسته از فرستنده / تحویل دادن بسته به گیرنده
            Route::post('confirm-pickup', [OrderFulfillmentController::class, 'confirmPickup']);
            Route::post('confirm-delivery', [OrderFulfillmentController::class, 'confirmDelivery']);

            // اسنپ‌شات موقعیت در طول مسیر (چندبار فراخوانی در IN_TRANSIT)
            Route::post('location-snapshot/midpoint', [OrderFulfillmentController::class, 'recordMidpoint']);

            // ثبت نظر توسط مشتری (احراز هویت شده، داخل اپ)
            Route::post('review', [ReviewController::class, 'submitCustomerReview']);

            // تایید پرداخت نقدی توسط پیک (بعد از دریافت وجه نقد)
            Route::post('confirm-cash-payment', [OrderFulfillmentController::class, 'confirmCashPayment']);
        });
    });

    // موقعیت لحظه‌ای پیک - HTTP Polling
    Route::prefix('courier')->group(function () {
        Route::put('location', [CourierLocationController::class, 'update']);
    });

});
