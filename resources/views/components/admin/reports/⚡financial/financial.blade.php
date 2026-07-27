<div>
    <x-header title="گزارش مالی" separator progress-indicator />

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

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <x-card title="مجموع درآمد" shadow>
            <p class="text-2xl font-bold text-success">{{ number_format($totalRevenue) }} تومان</p>
        </x-card>
        <x-card title="درآمد آنلاین" shadow>
            <p class="text-2xl font-bold text-info">{{ number_format($onlineRevenue) }} تومان</p>
        </x-card>
        <x-card title="درآمد نقدی" shadow>
            <p class="text-2xl font-bold text-warning">{{ number_format($cashRevenue) }} تومان</p>
        </x-card>
        <x-card title="تعداد تراکنش‌ها" shadow>
            <p class="text-2xl font-bold">{{ $transactionCount }}</p>
        </x-card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-card title="تفکیک بر اساس درگاه" shadow>
            <x-table :headers="[
                ['key' => 'payment_driver', 'label' => 'درگاه'],
                ['key' => 'total', 'label' => 'مجموع'],
                ['key' => 'count', 'label' => 'تعداد'],
            ]" :rows="$driverBreakdown">
                @scope('cell_total', $row)
                    {{ number_format($row['total']) }} تومان
                @endscope
            </x-table>
        </x-card>

        <x-card title="تفکیک بر اساس روش" shadow>
            <x-table :headers="[
                ['key' => 'payment_method', 'label' => 'روش'],
                ['key' => 'total', 'label' => 'مجموع'],
                ['key' => 'count', 'label' => 'تعداد'],
            ]" :rows="$methodBreakdown">
                @scope('cell_payment_method', $row)
                    {{ $row['payment_method'] === 'online' ? 'آنلاین' : 'نقدی' }}
                @endscope
                @scope('cell_total', $row)
                    {{ number_format($row['total']) }} تومان
                @endscope
            </x-table>
        </x-card>
    </div>
</div>
