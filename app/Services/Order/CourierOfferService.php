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
    /**
     * وضعیت‌هایی که نشان می‌دهند پیک این سفارش را پذیرفته است.
     */
    private const ACCEPTED_STATUSES = [
        'COURIER_ACCEPTED',
        'COURIER_ASSIGNED',
        'WAITING_PICKUP',
        'PICKED_UP',
        'IN_TRANSIT',
        'DELIVERED',
        'DELIVERY_FAILED',
        'RETURNED_TO_SENDER',
    ];

    public function __construct(
        private readonly OrderNotificationService $notificationService,
    ) {}

    /**
     * یافتن سفارش بر اساس توکن لینک پیشنهاد پیک (لینک قابل کلیک در مرورگر).
     */
    public function findByOfferToken(string $token): ?Order
    {
        return Order::query()
            ->where('courier_offer_token', $token)
            ->with('courier')
            ->first();
    }

    /**
     * وضعیت لینک پیشنهاد پیک:
     * offered (قابل پذیرش) | accepted (قبلاً پذیرفته شده) | expired (بی‌اعتبار یا منقضی).
     */
    public function offerLinkState(Order $order): string
    {
        if ($order->status === 'COURIER_OFFERED' && $order->courier_id !== null) {
            return 'offered';
        }

        if (in_array($order->status, self::ACCEPTED_STATUSES, true)) {
            return 'accepted';
        }

        return 'expired';
    }

    /**
     * پذیرش پیشنهاد از طریق لینک پیامکی (توکن) به‌جای توکن Sanctum اپ موبایل.
     *
     * پیشنهاد به پیک فعلی سفارش تعلق دارد (order->courier_id)، بنابراین توکن لینک
     * نقش احراز هویت پیک را بازی می‌کند.
     *
     * @throws OrderStateException
     */
    public function acceptByOfferToken(string $token): Order
    {
        $order = $this->findByOfferToken($token);

        if (! $order || $order->courier_id === null) {
            throw OrderStateException::invalidTransition('لینک پیشنهاد سفارش نامعتبر یا منقضی شده است.');
        }

        $this->accept($order, (int) $order->courier_id);

        return $order;
    }

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
