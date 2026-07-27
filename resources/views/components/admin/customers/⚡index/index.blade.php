<div>
    <x-header title="مدیریت مشتریان" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="جستجو..." wire:model.live.debounce.400ms="search" icon="o-magnifying-glass" clearable />
        </x-slot:middle>
    </x-header>

    <x-card>
        <x-table :headers="[
            ['key' => 'name', 'label' => 'نام'],
            ['key' => 'mobile', 'label' => 'موبایل'],
            ['key' => 'email', 'label' => 'ایمیل'],
            ['key' => 'orders_count', 'label' => 'تعداد سفارش'],
            ['key' => 'orders_sum_price', 'label' => 'مجموع پرداخت‌ها'],
            ['key' => 'created_at', 'label' => 'تاریخ ثبت‌نام'],
        ]" :rows="$customers" :sort-by="$sortBy" with-pagination>
            @scope('cell_orders_sum_price', $customer)
                {{ number_format($customer->orders_sum_price ?? 0) }} تومان
            @endscope
            @scope('cell_created_at', $customer)
                {{ $customer->created_at->format('Y-m-d') }}
            @endscope
            @scope('actions', $customer)
                <x-button icon="o-eye" link="{{ route('admin.customers.show', $customer) }}" class="btn-ghost btn-sm"
                    tooltip="پروفایل" />
            @endscope
        </x-table>
    </x-card>
</div>
