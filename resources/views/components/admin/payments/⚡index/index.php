<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('مدیریت پرداخت‌ها')] class extends Component
{
    use Toast;
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $paymentMethod = '';

    #[Url]
    public string $paymentStatus = '';

    #[Url]
    public string $paymentDriver = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function with(): array
    {
        return [
            'transactions' => Order::query()
                ->with('customer')
                ->whereNotNull('payment_authority') // فقط سفارش‌هایی که اقدام به پرداخت داشته‌اند
                ->when($this->search, fn ($q) => $q->where(function ($q) {
                    $q->where('id', 'like', "%{$this->search}%")
                        ->orWhere('payment_ref_id', 'like', "%{$this->search}%");
                }))
                ->when($this->paymentMethod, fn ($q) => $q->where('payment_method', $this->paymentMethod))
                ->when($this->paymentStatus, fn ($q) => $q->where('payment_status', $this->paymentStatus))
                ->when($this->paymentDriver, fn ($q) => $q->where('payment_driver', $this->paymentDriver))
                ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                ->orderBy(...array_values($this->sortBy))
                ->paginate(15),
        ];
    }
};
