<?php

namespace App\Events\Courier;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * اطلاع‌رسانی بلادرنگ موقعیت لحظه‌ای پیک.
 *
 * روی کانال presence-courier.{courierId} منتشر می‌شود.
 * مشتریانی که سفارش فعال با این پیک دارند، می‌توانند
 * موقعیت لحظه‌ای را از طریق این کانال دریافت کنند.
 */
final class CourierLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array{courier_id: int, lat: float, lng: float, timestamp: string}  $payload
     */
    public function __construct(
        public readonly array $payload,
        private readonly int $courierId,
    ) {}

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('courier-tracking.'.$this->courierId);
    }

    public function broadcastAs(): string
    {
        return 'courier.location-updated';
    }

    /**
     * داده‌ای که به کلاینت‌ها ارسال می‌شود.
     *
     * @return array{courier_id: int, lat: float, lng: float, timestamp: string}
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
