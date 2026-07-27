<?php

use App\Models\AdminActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('لاگ فعالیت مدیران')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $actionFilter = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function with(): array
    {
        return [
            'logs' => AdminActivityLog::query()
                ->with('admin')
                ->when($this->search, fn ($q) => $q->where(function ($q) {
                    $q->where('action', 'like', "%{$this->search}%")
                        ->orWhereHas('admin', fn ($qa) => $qa->where('name', 'like', "%{$this->search}%"));
                }))
                ->when($this->actionFilter, fn ($q) => $q->where('action', 'like', "{$this->actionFilter}%"))
                ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                ->orderBy(...array_values($this->sortBy))
                ->paginate(20),
        ];
    }

    public function actionLabel(string $action): string
    {
        return match ($action) {
            'order.cancelled' => 'لغو سفارش',
            'order.status_changed' => 'تغییر وضعیت سفارش',
            'order.courier_assigned' => 'تخصیص پیک',
            'order.price_changed' => 'تغییر قیمت سفارش',
            'order.refunded' => 'بازپرداخت سفارش',
            'order.force_delivered' => 'تایید دستی تحویل',
            'order.verification_sms_resent' => 'ارسال مجدد پیامک تایید',
            'courier.created' => 'ثبت پیک جدید',
            'courier.status_changed' => 'تغییر وضعیت پیک',
            'payment.manual_mark_paid' => 'ثبت دستی پرداخت',
            'payment.refund_processed' => 'پردازش بازپرداخت',
            'settings.general_updated' => 'به‌روزرسانی تنظیمات عمومی',
            'settings.courier_search_updated' => 'به‌روزرسانی تنظیمات جستجوی پیک',
            'settings.payment_updated' => 'به‌روزرسانی تنظیمات پرداخت',
            'settings.sms_updated' => 'به‌روزرسانی تنظیمات پیامک',
            'customer.toggle_block' => 'مسدودسازی/رفع مسدودیت مشتری',
            default => $action,
        };
    }
};
