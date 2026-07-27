<?php

namespace App\Services\Order;

use App\Jobs\CourierOfferTimeoutJob;
use App\Jobs\SearchCourierForOrderJob;
use App\Models\CourierCurrentLocation;
use App\Models\Order;
use App\Services\Order\Exceptions\OrderStateException;
use Illuminate\Support\Facades\DB;

final class CourierOfferService
{
    public function __construct(
        private readonly OrderNotificationService $notificationService,
    ) {}

    /**
     * پیک، پیشنهاد سفارش را می‌پذیرد.
     *
     * @throws OrderStateException
     */
    public function accept(Order $order, int $courierId): void
    {
        $this->assertOffered($order, $courierId);

        // حذف Job مهلت پاسخ از صف، چون پیک پاسخ داده است
        $this->deletePendingTimeoutJob($order->id);

        DB::transaction(function () use ($order, $courierId) {
            $order->changeStatus('COURIER_ACCEPTED', $courierId);

            // اساین شدن سفارش به رکورد موقعیت لحظه‌ای پیک
            CourierCurrentLocation::query()
                ->where('courier_id', $courierId)
                ->update(['order_id' => $order->id]);

            $order->update([
                'assigned_at' => now(),
                'courier_offered_at' => null,
            ]);
            $order->changeStatus('COURIER_ASSIGNED', $courierId);

            // طبق قرارداد: بلافاصله پس از تخصیص، سفارش وارد وضعیت انتظار تحویل گرفتن بسته می‌شود
            $order->changeStatusBySystem('WAITING_PICKUP');
        });

        // ---- اطلاع‌رسانی پیامکی ----
        $freshOrder = $order->fresh()->load('customer');
        $this->notificationService->notifyCustomerStatusChange($freshOrder, 'COURIER_ASSIGNED');
        $this->notificationService->notifyCustomerStatusChange($freshOrder, 'WAITING_PICKUP');
    }

    /**
     * پیک، پیشنهاد سفارش را رد می‌کند - جستجو برای پیک دیگر از سر گرفته می‌شود.
     *
     * @throws OrderStateException
     */
    public function reject(Order $order, int $courierId): void
    {
        $this->assertOffered($order, $courierId);

        // حذف Job مهلت پاسخ از صف، چون پیک پاسخ داده است
        $this->deletePendingTimeoutJob($order->id);

        DB::transaction(function () use ($order, $courierId) {
            $order->changeStatus('COURIER_REJECTED', $courierId);

            $order->update([
                'courier_id' => null,
                'courier_offered_at' => null,
                'courier_search_started_at' => now(),
            ]);

            $order->changeStatusBySystem('SEARCHING_COURIER');
        });

        SearchCourierForOrderJob::dispatch($order->id);
    }

    /**
     * حذف Job مهلت پاسخ پیک از صف، برای جلوگیری از اجرای تایم‌اوت
     * پس از اینکه پیک پاسخ خود را (قبول یا رد) اعلام کرده است.
     */
    private function deletePendingTimeoutJob(int $orderId): void
    {
        $uniqueId = (new CourierOfferTimeoutJob($orderId))->uniqueId();

        DB::table('jobs')
            ->where('payload', 'like', "%{$uniqueId}%")
            ->delete();
    }

    /**
     * @throws OrderStateException
     */
    private function assertOffered(Order $order, int $courierId): void
    {
        if ($order->status !== 'COURIER_OFFERED') {
            throw OrderStateException::invalidTransition('این سفارش در حال حاضر در وضعیت پیشنهاد به پیک نیست.');
        }

        if ((int) $order->courier_id !== $courierId) {
            throw OrderStateException::forbidden('این سفارش به شما پیشنهاد نشده است.');
        }
    }
}
