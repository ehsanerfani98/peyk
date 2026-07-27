<div>
    <x-header title="گزارش پیک‌ها" separator progress-indicator>
        <x-slot:actions>
            <x-button label="خروجی CSV" icon="o-document-arrow-down" wire:click="exportCsv" class="btn-ghost btn-sm" />
        </x-slot:actions>
    </x-header>

    <x-card class="mb-4">
        <div class="flex gap-4 items-end">
            <x-input label="از تاریخ" wire:model="dateFrom" type="date" />
            <x-input label="تا تاریخ" wire:model="dateTo" type="date" />
        </div>
    </x-card>

    <x-card>
        <x-table :headers="[
            ['key' => 'name', 'label' => 'نام'],
            ['key' => 'mobile', 'label' => 'موبایل'],
            ['key' => 'vehicle_type', 'label' => 'وسیله نقلیه'],
            ['key' => 'orders_count', 'label' => 'تعداد سفارش'],
            ['key' => 'rating', 'label' => 'میانگین امتیاز'],
            ['key' => 'snapshot_count', 'label' => 'مسافت (تعداد اسنپ‌شات)'],
        ]" :rows="$couriers">
            @scope('cell_rating', $c)
                {{ number_format($c['rating'], 1) }}
            @endscope
        </x-table>
    </x-card>
</div>
