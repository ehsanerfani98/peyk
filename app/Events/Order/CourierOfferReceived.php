<?php

namespace App\Events\Order;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * اطلاع‌رسانی بلادرنگ پیشنهاد سفارش جدید به پیک.
 *
 * روی کانال private-courier.{courierId} منتشر می‌شود.
 * فقط پیک مورد نظر مجاز به شنیدن این کانال است.
 */
final class CourierOfferReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array{order_id: int, pickup_address: string, delivery_address: string, price: float, distance_meters: float|null}  $payload
     */
    public function __construct(
        public readonly array $payload,
        private readonly int $courierId,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('private-courier.'.$this->courierId);
    }

    public function broadcastAs(): string
    {
        return 'courier.offer-received';
    }

    /**
     * داده‌ای که به کلاینت‌ها ارسال می‌شود.
     *
     * @return array{order_id: int, pickup_address: string, delivery_address: string, price: float, distance_meters: float|null}
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
