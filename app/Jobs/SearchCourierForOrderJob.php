<?php

namespace App\Jobs;

use App\Events\Order\CourierOfferReceived;
use App\Helpers\SmsSender;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Order\CourierMatchingService;
use App\Services\Order\OrderNotificationService;
use App\Services\Sms\Exceptions\SmsSendingException;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * جستجوی دوره‌ای پیک برای یک سفارش در وضعیت SEARCHING_COURIER.
 *
 * الگو: هر بار که Job اجرا می‌شود، یا پیکی پیدا می‌شود (وضعیت -> COURIER_OFFERED)،
 * یا سقف زمانی جستجو به پایان رسیده (وضعیت -> COURIER_NOT_FOUND: جستجو متوقف می‌شود
 * اما سفارش کنسل نمی‌شود؛ کاربر می‌تواند جستجو را مجددا آغاز کند یا سفارش را لغو کند)،
 * یا هیچکدام از این دو رخ نداده که در این حالت نسخه بعدی همین Job با یک تاخیر کوتاه
 * (Setting::getValue('courier_search.interval_seconds')) دوباره به صف اضافه می‌شود.
 *
 * ShouldBeUniqueUntilProcessing تضمین می‌کند در هر لحظه حداکثر یک نسخه از این Job
 * برای یک سفارش مشخص در صف در انتظار اجرا باشد (جلوگیری از صف‌های موازی تکراری).
 */
final class SearchCourierForOrderJob implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function uniqueId(): string
    {
        return "search-courier-order-{$this->orderId}";
    }

    public function handle(
        CourierMatchingService $matcher,
        OrderNotificationService $notificationService,
    ): void {
        $order = Order::find($this->orderId);
        // سفارش حذف شده یا دیگر در وضعیت جستجو نیست (پیک پیدا شده، کنسل شده و ...) -> چرخه متوقف می‌شود
        if (! $order || $order->status !== 'SEARCHING_COURIER') {
            return;
        }

        $timeoutMinutes = (int) Setting::getValue('courier_search.timeout_minutes', config('courier_search.timeout_minutes', 5));
        if (
            $order->courier_search_started_at !== null
            && $order->courier_search_started_at->copy()->addMinutes($timeoutMinutes)->isPast()
        ) {
            // مهلت جستجو به پایان رسیده بدون یافتن پیک -> وضعیت COURIER_NOT_FOUND
            // سفارش کنسل نمی‌شود؛ کاربر می‌تواند جستجو را مجددا آغاز کند یا سفارش را لغو کند
            $order->changeStatusBySystem('COURIER_NOT_FOUND');

            $order->load('customer');
            $notificationService->notifyCustomerStatusChange($order, 'COURIER_NOT_FOUND');

            return;
        }

        // اولین اجرا: اطلاع‌رسانی به مشتری که جستجوی پیک آغاز شده
        $order->load('customer');
        $notificationService->notifyCustomerStatusChange($order, 'SEARCHING_COURIER');

        $candidate = $matcher->findCandidate($order);
        if ($candidate) {
            $order->update([
                'courier_id' => $candidate->courier_id,
                'courier_offered_at' => now(),
            ]);
            $order->changeStatusBySystem('COURIER_OFFERED');

            // dispatch مهلت پاسخ پیک: اگر پیک در بازه تعیین‌شده پاسخ ندهد،
            // سیستم به‌صورت خودکار پیشنهاد را رد شده در نظر می‌گیرد
            $offerTimeoutSeconds = (int) Setting::getValue('courier_search.courier_offer_timeout_seconds', config('courier_search.courier_offer_timeout_seconds', 60));
            CourierOfferTimeoutJob::dispatch($order->id)
                ->delay(now()->addSeconds($offerTimeoutSeconds));

            // ---- اطلاع‌رسانی بلادرنگ (Reverb) به پیک ----
            CourierOfferReceived::dispatch([
                'order_id' => $order->id,
                'pickup_address' => $order->sender_address,
                'delivery_address' => $order->receiver_address,
                'price' => (float) $order->price,
                'distance_meters' => $candidate->distance ?? null,
            ], $candidate->courier_id);

            // ---- اطلاع‌رسانی پیامکی (fallback) به پیک ----
            $patternCode = Setting::getValue('ippanel.pattern_courier_offer', config('ippanel.pattern_courier_offer'));
            if ($patternCode) {
                $courier = $candidate->courier;
                if ($courier) {
                    try {
                        $paramKey = Setting::getValue('ippanel.courier_offer_param_key', config('ippanel.courier_offer_param_key', 'code'));
                        $smsSender = app(SmsSender::class);
                        $smsSender->send(
                            localMobile: $courier->user->mobile,
                            paramValue: url("api/orders/$order->id/accept"),
                            patternCode: $patternCode,
                            paramKey: $paramKey,
                        );
                    } catch (SmsSendingException $e) {
                        Log::warning('sms.courier_offer_failed', [
                            'order_id' => $order->id,
                            'courier_id' => $candidate->courier_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            return;
        }

        $intervalSeconds = (int) Setting::getValue('courier_search.interval_seconds', config('courier_search.interval_seconds', 10));

        self::dispatch($this->orderId)->delay(now()->addSeconds($intervalSeconds));
    }
}
