<?php

namespace App\Jobs;

use App\Events\Order\CourierOfferReceived;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * مهلت پاسخ پیک به پیشنهاد سفارش (COURIER_OFFERED).
 *
 * این Job با تاخیر (courier_offer_timeout_seconds) dispatch می‌شود.
 * اگر پیک در این بازه نه قبول کند و نه رد، سیستم به‌صورت خودکار
 * پیشنهاد را رد شده در نظر می‌گیرد و جستجو را ادامه می‌دهد.
 *
 * ShouldBeUniqueUntilProcessing تضمین می‌کند در هر لحظه حداکثر یک نسخه
 * از این Job برای یک سفارش مشخص در صف باشد.
 */
final class CourierOfferTimeoutJob implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function uniqueId(): string
    {
        return "courier-offer-timeout-order-{$this->orderId}";
    }

    public function handle(): void
    {
        $order = Order::find($this->orderId);

        // سفارش حذف شده یا دیگر در وضعیت COURIER_OFFERED نیست (پیک پاسخ داده)
        if (! $order || $order->status !== 'COURIER_OFFERED') {
            return;
        }

        $timeoutSeconds = (int) Setting::getValue('courier_search.courier_offer_timeout_seconds', config('courier_search.courier_offer_timeout_seconds', 60));

        // بررسی دفاعی: اگر courier_offered_at تنظیم نشده یا هنوز مهلت تمام نشده
        if (
            $order->courier_offered_at === null
            || $order->courier_offered_at->copy()->addSeconds($timeoutSeconds)->isFuture()
        ) {
            Log::warning('courier_offer_timeout.early_fire', [
                'order_id' => $order->id,
                'courier_offered_at' => $order->courier_offered_at?->toIso8601String(),
            ]);

            return;
        }

        $courierId = $order->courier_id;

        // ---- اطلاع‌رسانی بلادرنگ (Reverb) به پیک: پیشنهاد منقضی شده ----
        // ارسال payload با order_id = null تا اپ موبایل پیشنهاد را از UI حذف کند
        if ($courierId) {
            CourierOfferReceived::dispatch([
                'order_id' => null,
                'pickup_address' => '',
                'delivery_address' => '',
                'price' => 0.0,
                'distance_meters' => null,
            ], $courierId);
        }

        DB::transaction(function () use ($order, $courierId) {
            // تایم‌اوت پاسخ پیک (نه رد فعالانه) - changed_by = courier_id
            // وضعیت COURIER_TIMEOUT با COURIER_REJECTED متفاوت است:
            // پیک‌های تایم‌اوت‌شده در excludedCourierIds قرار نمی‌گیرند
            // و می‌توانند در دورهای بعدی جستجو مجددا انتخاب شوند
            $order->changeStatus('COURIER_TIMEOUT', $courierId);

            $order->update([
                'courier_id' => null,
                'courier_offered_at' => null,
                'courier_search_started_at' => now(),
            ]);

            $order->changeStatusBySystem('SEARCHING_COURIER');
        });

        Log::info('courier_offer_timeout.auto_rejected', [
            'order_id' => $order->id,
            'courier_id' => $courierId,
        ]);

        SearchCourierForOrderJob::dispatch($order->id);
    }
}
