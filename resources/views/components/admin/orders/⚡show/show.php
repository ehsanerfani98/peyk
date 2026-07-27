<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('جزئیات سفارش')] class extends Component
{
    public Order $order;

    public function with(): array
    {
        $this->order->load([
            'customer',
            'courier',
            'statusHistories.changedByUser',
            'verifications',
            'reviews.user',
            'locationSnapshots',
        ]);

        $snapshots = $this->order->locationSnapshots
            ->map(function ($snap) {
                // Extract lat/lng from location geometry (POINT(lng lat))
                $location = $snap->getRawOriginal('location');
                preg_match('/POINT\(([\d.]+) ([\d.]+)\)/', $location, $matches);

                return [
                    'latitude' => $matches[2] ?? null,
                    'longitude' => $matches[1] ?? null,
                    'snapshot_type' => $snap->snapshot_type,
                    'created_at' => $snap->created_at?->format('Y-m-d H:i:s'),
                ];
            })
            ->filter(fn ($s) => $s['latitude'] && $s['longitude'])
            ->values();

        return [
            'snapshots' => $snapshots,
        ];
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'CREATED' => 'ایجاد شده',
            'WAITING_SENDER_VERIFY' => 'منتظر تایید فرستنده',
            'SENDER_VERIFIED' => 'فرستنده تایید شد',
            'WAITING_RECEIVER_VERIFY' => 'منتظر تایید گیرنده',
            'RECEIVER_VERIFIED' => 'گیرنده تایید شد',
            'SEARCHING_COURIER' => 'در جستجوی پیک',
            'COURIER_OFFERED' => 'پیشنهاد به پیک',
            'COURIER_ACCEPTED' => 'پیک پذیرفت',
            'COURIER_REJECTED' => 'پیک رد کرد',
            'COURIER_ASSIGNED' => 'پیک تخصیص یافت',
            'WAITING_PICKUP' => 'منتظر تحویل بسته',
            'PICKED_UP' => 'بسته تحویل گرفته شد',
            'IN_TRANSIT' => 'در مسیر تحویل',
            'DELIVERED' => 'تحویل شد',
            'DELIVERY_FAILED' => 'تحویل ناموفق',
            'RETURNED_TO_SENDER' => 'بازگشت به فرستنده',
            'CANCELLED' => 'لغو شده',
            'COURIER_NOT_FOUND' => 'پیک یافت نشد',
            default => $status,
        };
    }
};
