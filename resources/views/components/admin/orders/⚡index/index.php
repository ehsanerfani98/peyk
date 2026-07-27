<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('مدیریت سفارش‌ها')] class extends Component
{
    use Toast;
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public array $statusFilter = [];

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $paymentMethod = '';

    #[Url]
    public string $paymentStatus = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function with(): array
    {
        return [
            'orders' => Order::query()
                ->with(['customer', 'courier'])
                ->when($this->search, fn ($q) => $q->where(function ($q) {
                    $q->where('id', 'like', "%{$this->search}%")
                        ->orWhere('sender_name', 'like', "%{$this->search}%")
                        ->orWhere('receiver_name', 'like', "%{$this->search}%")
                        ->orWhere('sender_mobile', 'like', "%{$this->search}%")
                        ->orWhere('receiver_mobile', 'like', "%{$this->search}%");
                }))
                ->when($this->statusFilter, fn ($q) => $q->whereIn('status', $this->statusFilter))
                ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                ->when($this->paymentMethod, fn ($q) => $q->where('payment_method', $this->paymentMethod))
                ->when($this->paymentStatus, fn ($q) => $q->where('payment_status', $this->paymentStatus))
                ->orderBy(...array_values($this->sortBy))
                ->paginate(15),
        ];
    }

    public function statusLabels(): array
    {
        return [
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
        ];
    }

    public function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'DELIVERED' => 'badge-success',
            'CANCELLED', 'DELIVERY_FAILED' => 'badge-error',
            'CREATED', 'WAITING_SENDER_VERIFY', 'SENDER_VERIFIED',
            'WAITING_RECEIVER_VERIFY', 'RECEIVER_VERIFIED' => 'badge-warning',
            'SEARCHING_COURIER', 'COURIER_OFFERED', 'COURIER_ACCEPTED',
            'COURIER_REJECTED' => 'badge-info',
            'COURIER_ASSIGNED', 'WAITING_PICKUP', 'PICKED_UP', 'IN_TRANSIT' => 'badge-primary',
            default => 'badge-ghost',
        };
    }
};
