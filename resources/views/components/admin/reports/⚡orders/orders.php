<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('گزارش سفارش‌ها')] class extends Component
{
    use Toast;

    public string $period = 'today'; // today, week, month, year, custom

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
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo));

        $total = (clone $query)->count();
        $pending = (clone $query)->whereIn('status', ['CREATED', 'WAITING_SENDER_VERIFY', 'SENDER_VERIFIED', 'WAITING_RECEIVER_VERIFY', 'RECEIVER_VERIFIED'])->count();
        $inProgress = (clone $query)->whereIn('status', ['SEARCHING_COURIER', 'COURIER_ASSIGNED', 'WAITING_PICKUP', 'PICKED_UP', 'IN_TRANSIT'])->count();
        $delivered = (clone $query)->where('status', 'DELIVERED')->count();
        $cancelled = (clone $query)->where('status', 'CANCELLED')->count();

        $statusStats = (clone $query)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $dailyTrend = (clone $query)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return [
            'totalOrders' => $total,
            'pendingOrders' => $pending,
            'inProgressOrders' => $inProgress,
            'deliveredOrders' => $delivered,
            'cancelledOrders' => $cancelled,
            'statusStats' => $statusStats,
            'dailyTrend' => $dailyTrend,
        ];
    }

    public function exportCsv(): void
    {
        $orders = Order::query()
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->get();

        $csv = "ID,Status,Sender,Receiver,Price,Created At\n";
        foreach ($orders as $order) {
            $csv .= "{$order->id},{$order->status},{$order->sender_name},{$order->receiver_name},{$order->price},{$order->created_at}\n";
        }

        $this->dispatch('download-csv', csv: $csv, filename: "orders-report-{$this->dateFrom}-{$this->dateTo}.csv");
    }
};
