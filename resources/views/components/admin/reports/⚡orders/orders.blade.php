<div>
    <x-header title="گزارش سفارش‌ها" separator progress-indicator />

    <x-card class="mb-4">
        <div class="flex gap-4 items-end">
            <x-select label="بازه زمانی" wire:model.live="period" :options="[
                ['id' => 'today', 'name' => 'امروز'],
                ['id' => 'week', 'name' => 'این هفته'],
                ['id' => 'month', 'name' => 'این ماه'],
                ['id' => 'year', 'name' => 'امسال'],
                ['id' => 'custom', 'name' => 'بازه دلخواه'],
            ]" />
            @if ($period === 'custom')
                <x-input label="از تاریخ" wire:model="dateFrom" type="date" />
                <x-input label="تا تاریخ" wire:model="dateTo" type="date" />
            @endif
            <x-button label="خروجی CSV" icon="o-document-arrow-down" wire:click="exportCsv" class="btn-ghost btn-sm" />
        </div>
    </x-card>

    {{-- کارت‌های آمار --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
        <x-card title="کل سفارش‌ها" shadow>
            <p class="text-2xl font-bold">{{ $totalOrders }}</p>
        </x-card>
        <x-card title="در انتظار" shadow>
            <p class="text-2xl font-bold text-warning">{{ $pendingOrders }}</p>
        </x-card>
        <x-card title="در حال انجام" shadow>
            <p class="text-2xl font-bold text-info">{{ $inProgressOrders }}</p>
        </x-card>
        <x-card title="تحویل شده" shadow>
            <p class="text-2xl font-bold text-success">{{ $deliveredOrders }}</p>
        </x-card>
        <x-card title="لغو شده" shadow>
            <p class="text-2xl font-bold text-error">{{ $cancelledOrders }}</p>
        </x-card>
    </div>

    {{-- جدول تفکیک وضعیت‌ها --}}
    <x-card title="تفکیک وضعیت‌ها" shadow>
        <x-table :headers="[
            ['key' => 'status', 'label' => 'وضعیت'],
            ['key' => 'count', 'label' => 'تعداد'],
        ]" :rows="collect($statusStats)->map(fn($count, $status) => ['status' => $status, 'count' => $count])->values()" />
    </x-card>
</div>
