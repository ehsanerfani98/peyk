<?php

namespace App\Services\Order;

use App\Models\CourierCurrentLocation;
use App\Models\Order;
use App\Models\OrderVerification;
use App\Services\Order\Exceptions\OrderStateException;
use App\Services\Order\Exceptions\OrderVerificationException;
use Illuminate\Support\Facades\DB;

final class OrderFulfillmentService
{
    public function __construct(
        private readonly OrderNotificationService $notificationService,
        private readonly SurveyService $surveyService,
    ) {}

    /**
     * پیک، کد تحویل‌گیری بسته را از فرستنده می‌گیرد و وارد می‌کند.
     * همان رکورد order_verifications که در ابتدای سفارش برای فرستنده ساخته شده
     * (type: sender_verify یا pickup) به عنوان منبع کد استفاده می‌شود.
     *
     * @throws OrderStateException
     * @throws OrderVerificationException
     */
    public function confirmPickup(Order $order, int $courierId, string $code): void
    {
        $this->assertAssignedCourier($order, $courierId);

        if ($order->status !== 'WAITING_PICKUP') {
            throw OrderStateException::invalidTransition('سفارش در وضعیت انتظار تحویل گرفتن بسته نیست.');
        }

        $verification = $order->verifications()
            ->whereIn('type', ['sender_verify', 'pickup'])
            ->latest('id')
            ->first();

        $this->verifyCode($verification, $code);

        DB::transaction(function () use ($order, $courierId) {
            $order->update(['picked_up_at' => now()]);
            $order->changeStatus('PICKED_UP', $courierId);
            $order->changeStatusBySystem('IN_TRANSIT');

            $this->recordSnapshot($order, $courierId, 'pickup');
        });

        // ---- اطلاع‌رسانی پیامکی ----
        $freshOrder = $order->fresh()->load('customer');

        // به مشتری: بسته دریافت شد (PICKED_UP) + در مسیر مقصد (IN_TRANSIT)
        $this->notificationService->notifyCustomerStatusChange($freshOrder, 'PICKED_UP');
        $this->notificationService->notifyCustomerStatusChange($freshOrder, 'IN_TRANSIT');

        // به فرستنده/گیرنده غیرمشتری
        $this->notificationService->notifySenderStatusChange($freshOrder, 'PICKED_UP');
        $this->notificationService->notifyReceiverStatusChange($freshOrder, 'IN_TRANSIT');
    }

    /**
     * پیک، کد تحویل دادن بسته را از گیرنده می‌گیرد و وارد می‌کند.
     * همان رکورد order_verifications که در ابتدای سفارش برای گیرنده ساخته شده
     * (type: receiver_verify یا delivery) به عنوان منبع کد استفاده می‌شود.
     *
     * @throws OrderStateException
     * @throws OrderVerificationException
     */
    public function confirmDelivery(Order $order, int $courierId, string $code): void
    {
        $this->assertAssignedCourier($order, $courierId);

        if ($order->status !== 'IN_TRANSIT') {
            throw OrderStateException::invalidTransition('سفارش در وضعیت درحال ارسال نیست.');
        }

        $verification = $order->verifications()
            ->whereIn('type', ['receiver_verify', 'delivery'])
            ->latest('id')
            ->first();

        $this->verifyCode($verification, $code);

        DB::transaction(function () use ($order, $courierId) {
            $order->update(['delivered_at' => now()]);
            $order->changeStatus('DELIVERED', $courierId);

            $this->recordSnapshot($order, $courierId, 'delivery');

            // آزادسازی پیک: پس از تحویل موفق بسته، پیک برای سفارش‌های جدید در دسترس قرار می‌گیرد
            CourierCurrentLocation::query()
                ->where('courier_id', $courierId)
                ->update(['order_id' => null]);
        });

        // ---- اطلاع‌رسانی پیامکی ----
        $freshOrder = $order->fresh()->load('customer');

        // توجه: برای وضعیت DELIVERED به گیرنده پیامک ارسال نمی‌شود،
        // چون گیرنده خودش بسته را تحویل گرفته و از وضعیت سفارش مطلع است.
        // $this->notificationService->notifyReceiverStatusChange($freshOrder, 'DELIVERED');
        $this->notificationService->notifyCustomerStatusChange($freshOrder, 'DELIVERED');
        $this->notificationService->notifySenderStatusChange($freshOrder, 'DELIVERED');

        // ---- شروع نظرسنجی ----
        $this->surveyService->initiateSurvey($freshOrder);
    }

    /**
     * تایید پرداخت نقدی توسط پیک.
     *
     * وقتی payment_method = cash_on_delivery، پیک بعد از دریافت وجه نقد
     * این متد را صدا می‌زند تا payment_status به paid تغییر کند.
     * فقط در وضعیت‌های PICKED_UP، IN_TRANSIT یا DELIVERED مجاز است.
     *
     * @throws OrderStateException
     */
    public function confirmCashPayment(Order $order, int $courierId): void
    {
        $this->assertAssignedCourier($order, $courierId);

        if ($order->payment_method !== 'cash_on_delivery') {
            throw OrderStateException::invalidTransition('روش پرداخت این سفارش نقدی نیست.');
        }

        if ($order->payment_status === 'paid') {
            throw OrderStateException::invalidTransition('پرداخت این سفارش قبلاً ثبت شده است.');
        }

        if (! in_array($order->status, ['PICKED_UP', 'IN_TRANSIT', 'DELIVERED'], true)) {
            throw OrderStateException::invalidTransition('سفارش در وضعیت قابل قبول برای ثبت پرداخت نیست.');
        }

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * ثبت اسنپ‌شات موقعیت پیک در طول مسیر - اپ پیک می‌تواند در تمام طول مرحله IN_TRANSIT
     * به‌طور مکرر این متد را صدا بزند تا مسیر کامل سفر ثبت شود.
     *
     * @throws OrderStateException
     */
    public function recordMidpointSnapshot(Order $order, int $courierId): void
    {
        $this->assertAssignedCourier($order, $courierId);

        if ($order->status !== 'IN_TRANSIT') {
            throw OrderStateException::invalidTransition('سفارش در وضعیت درحال ارسال نیست.');
        }

        $this->recordSnapshot($order, $courierId, 'en_route');
    }

    /**
     * @throws OrderVerificationException
     */
    private function verifyCode(?OrderVerification $verification, string $code): void
    {
        if (! $verification) {
            throw OrderVerificationException::notFound();
        }

        if ($verification->isLocked()) {
            throw OrderVerificationException::locked();
        }

        if ($verification->isExpired()) {
            throw OrderVerificationException::expired();
        }

        if (! hash_equals((string) $verification->code, $code)) {
            $verification->registerFailedAttempt();

            throw $verification->isLocked()
                ? OrderVerificationException::locked()
                : OrderVerificationException::invalidCode();
        }
    }

    /**
     * درج رکورد تاریخچه‌ای در courier_location_snapshots با کپی مستقیم ستون location
     * (از نوع POINT) از courier_current_locations - بدون نیاز به رفت و برگشت PHP <-> WKT.
     */
    private function recordSnapshot(Order $order, int $courierId, string $type): void
    {
        DB::statement(
            'INSERT INTO courier_location_snapshots (order_id, courier_id, snapshot_type, location, created_at)
             SELECT ?, ?, ?, location, ? FROM courier_current_locations WHERE courier_id = ?',
            [$order->id, $courierId, $type, now(), $courierId]
        );
    }

    /**
     * @throws OrderStateException
     */
    private function assertAssignedCourier(Order $order, int $courierId): void
    {
        if ((int) $order->courier_id !== $courierId) {
            throw OrderStateException::forbidden('این سفارش به شما تخصیص داده نشده است.');
        }
    }
}
