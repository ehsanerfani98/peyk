<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * اطلاع‌رسانی بلادرنگ تغییر وضعیت سفارش به مشتری و پیک.
 *
 * روی کانال private-order.{orderId} منتشر می‌شود.
 * مشتری (customer_id) و پیک تخصیص‌داده‌شده (courier_id) هر دو
 * مجاز به شنیدن این کانال هستند.
 */
final class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array{order_id: int, status: string, timestamp: string}  $payload
     */
    public function __construct(
        public readonly array $payload,
        private readonly int $orderId,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('private-order.'.$this->orderId);
    }

    public function broadcastAs(): string
    {
        return 'order.status-changed';
    }

    /**
     * داده‌ای که به کلاینت‌ها ارسال می‌شود.
     *
     * @return array{order_id: int, status: string, timestamp: string}
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
