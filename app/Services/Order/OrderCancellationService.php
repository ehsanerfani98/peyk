<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Services\Order\Exceptions\OrderStateException;
use Illuminate\Support\Facades\DB;

final class OrderCancellationService
{
    public function __construct(
        private readonly OrderNotificationService $notificationService,
    ) {}

    /**
     * لغو سفارش توسط مشتری (مثلا وقتی پیکی پیدا نمی‌شود یا به هر دلیل دیگر).
     *
     * @throws OrderStateException
     */
    public function cancelByCustomer(Order $order, int $customerId, ?string $reason = null): void
    {
        if ((int) $order->customer_id !== $customerId) {
            throw OrderStateException::forbidden('این سفارش متعلق به شما نیست.');
        }

        $this->cancel($order, cancelledBy: 'customer', changedBy: $customerId, reason: $reason);
    }

    /**
     * لغو سفارش توسط پیک (مثلا وقتی پیک به هر دلیلی از انجام سفارش منصرف می‌شود).
     *
     * @throws OrderStateException
     */
    public function cancelByCourier(Order $order, int $courierId, ?string $reason = null): void
    {
        if ((int) $order->courier_id !== $courierId) {
            throw OrderStateException::forbidden('این سفارش به شما تخصیص داده نشده است.');
        }

        $this->cancel($order, cancelledBy: 'courier', changedBy: $courierId, reason: $reason);
    }

    /**
     * کنسل خودکار توسط سیستم؛ زمانی رخ می‌دهد که سقف زمانی جستجوی پیک (پیش‌فرض ۵ دقیقه) بدون یافتن پیک به پایان برسد.
     */
    public function cancelBySystem(Order $order): void
    {
        $this->cancel($order, cancelledBy: 'system', changedBy: null, reason: null);
    }

    /**
     * @throws OrderStateException
     */
    private function cancel(Order $order, string $cancelledBy, ?int $changedBy, ?string $reason): void
    {
        if (! $order->isCancellable()) {
            throw OrderStateException::invalidTransition(
                'سفارش در وضعیت فعلی (پیک بسته را تحویل گرفته یا سفارش به پایان رسیده) قابل لغو نیست.'
            );
        }

        DB::transaction(function () use ($order, $cancelledBy, $changedBy, $reason) {
            $order->update([
                'cancelled_by' => $cancelledBy,
                'cancel_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            if ($changedBy === null) {
                $order->changeStatusBySystem('CANCELLED');
            } else {
                $order->changeStatus('CANCELLED', $changedBy);
            }
        });

        // ---- اطلاع‌رسانی پیامکی ----
        $freshOrder = $order->fresh()->load(['customer', 'courier']);
        // اطلاع‌رسانی به مشتری
        $this->notificationService->notifyCustomerStatusChange($freshOrder, 'CANCELLED');

        // اطلاع‌رسانی به فرستنده/گیرنده غیرمشتری
        $this->notificationService->notifySenderStatusChange($freshOrder, 'CANCELLED');
        $this->notificationService->notifyReceiverStatusChange($freshOrder, 'CANCELLED');

        // اطلاع‌رسانی به پیک (فقط اگر پیک اختصاص داده شده باشد)
        if ($freshOrder->courier_id) {
            $this->notificationService->notifyCourierCancellation($freshOrder);
        }

        // اگر لغو توسط سیستم (تایم‌اوت) بوده، پیامک جداگانه تایم‌اوت به مشتری
        if ($cancelledBy === 'system') {
            $this->notificationService->notifyCustomerSystemCancellation($freshOrder);
        }
    }
}
