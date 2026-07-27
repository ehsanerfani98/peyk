<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('گزارش مالی')] class extends Component
{
    use Toast;

    public string $period = 'month';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $this->applyPeriod();
    }

    public function updatedPeriod(): void
    {
        $this->applyPeriod();
    }

    private function applyPeriod(): void
    {
        match ($this->period) {
            'today' => $this->dateFrom = $this->dateTo = now()->format('Y-m-d'),
            'week' => $this->dateFrom = now()->startOfWeek()->format('Y-m-d'),
            'month' => $this->dateFrom = now()->startOfMonth()->format('Y-m-d'),
            'year' => $this->dateFrom = now()->startOfYear()->format('Y-m-d'),
            default => null,
        };

        if ($this->period !== 'custom') {
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    public function with(): array
    {
        $query = Order::query()
            ->where('payment_status', 'paid')
            ->when($this->dateFrom, fn ($q) => $q->whereDate('paid_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('paid_at', '<=', $this->dateTo));

        $totalRevenue = (clone $query)->sum('price');
        $onlineRevenue = (clone $query)->where('payment_method', 'online')->sum('price');
        $cashRevenue = (clone $query)->where('payment_method', 'cash_on_delivery')->sum('price');
        $transactionCount = (clone $query)->count();

        $driverBreakdown = (clone $query)
            ->selectRaw('payment_driver, SUM(price) as total, COUNT(*) as count')
            ->whereNotNull('payment_driver')
            ->groupBy('payment_driver')
            ->get()
            ->toArray();

        $methodBreakdown = (clone $query)
            ->selectRaw('payment_method, SUM(price) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get()
            ->toArray();

        $dailyTrend = (clone $query)
            ->selectRaw('DATE(paid_at) as date, SUM(price) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        return [
            'totalRevenue' => $totalRevenue,
            'onlineRevenue' => $onlineRevenue,
            'cashRevenue' => $cashRevenue,
            'transactionCount' => $transactionCount,
            'driverBreakdown' => $driverBreakdown,
            'methodBreakdown' => $methodBreakdown,
            'dailyTrend' => $dailyTrend,
        ];
    }

    public function exportCsv(): void
    {
        $orders = Order::query()
            ->where('payment_status', 'paid')
            ->when($this->dateFrom, fn ($q) => $q->whereDate('paid_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('paid_at', '<=', $this->dateTo))
            ->get();

        $csv = "ID,Price,Method,Driver,Paid At\n";
        foreach ($orders as $order) {
            $csv .= "{$order->id},{$order->price},{$order->payment_method},{$order->payment_driver},{$order->paid_at}\n";
        }

        $this->dispatch('download-csv', csv: $csv, filename: "financial-report-{$this->dateFrom}-{$this->dateTo}.csv");
    }
};
