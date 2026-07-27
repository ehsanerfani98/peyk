<div>
    <x-header title="لاگ فعالیت مدیران" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="جستجو..." wire:model.live.debounce.400ms="search" icon="o-magnifying-glass" clearable />
        </x-slot:middle>
    </x-header>

    <x-card class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-select label="نوع اکشن" wire:model="actionFilter" placeholder="همه"
                :options="[
                    ['id' => 'order.', 'name' => 'سفارش‌ها'],
                    ['id' => 'courier.', 'name' => 'پیک‌ها'],
                    ['id' => 'payment.', 'name' => 'پرداخت‌ها'],
                    ['id' => 'settings.', 'name' => 'تنظیمات'],
                    ['id' => 'customer.', 'name' => 'مشتریان'],
                ]" />
            <x-input label="از تاریخ" wire:model="dateFrom" type="date" />
            <x-input label="تا تاریخ" wire:model="dateTo" type="date" />
        </div>
    </x-card>

    <x-card>
        <x-table :headers="[
            ['key' => 'admin.name', 'label' => 'مدیر', 'sortable' => false],
            ['key' => 'action', 'label' => 'اکشن'],
            ['key' => 'subject_type', 'label' => 'نوع موضوع'],
            ['key' => 'subject_id', 'label' => 'شناسه موضوع'],
            ['key' => 'ip', 'label' => 'IP'],
            ['key' => 'created_at', 'label' => 'تاریخ'],
        ]" :rows="$logs" :sort-by="$sortBy" with-pagination>
            @scope('cell_action', $log)
                {{ $this->actionLabel($log->action) }}
            @endscope
            @scope('cell_subject_type', $log)
                {{ class_basename($log->subject_type) }}
            @endscope
            @scope('cell_created_at', $log)
                {{ $log->created_at->format('Y-m-d H:i') }}
            @endscope
        </x-table>
    </x-card>
</div>
