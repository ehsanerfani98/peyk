<div>
    <x-header title="مدیریت پرداخت‌ها" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="جستجو..." wire:model.live.debounce.400ms="search" icon="o-magnifying-glass" clearable />
        </x-slot:middle>
    </x-header>

    <x-card class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-select label="روش پرداخت" wire:model="paymentMethod" placeholder="همه"
                :options="[['id' => 'online', 'name' => 'آنلاین'], ['id' => 'cash_on_delivery', 'name' => 'نقدی']]" />
            <x-select label="وضعیت پرداخت" wire:model="paymentStatus" placeholder="همه"
                :options="[['id' => 'pending', 'name' => 'در انتظار'], ['id' => 'paid', 'name' => 'پرداخت شده']]" />
            <x-select label="درگاه" wire:model="paymentDriver" placeholder="همه"
                :options="[['id' => 'zarinpal', 'name' => 'زرین‌پال'], ['id' => 'zibal', 'name' => 'زیبال']]" />
            <div class="flex gap-2">
                <x-input label="از تاریخ" wire:model="dateFrom" type="date" />
                <x-input label="تا تاریخ" wire:model="dateTo" type="date" />
            </div>
        </div>
    </x-card>

    <x-card>
        <x-table :headers="[
            ['key' => 'id', 'label' => 'سفارش'],
            ['key' => 'customer.name', 'label' => 'مشتری', 'sortable' => false],
            ['key' => 'price', 'label' => 'مبلغ'],
            ['key' => 'payment_method', 'label' => 'روش'],
            ['key' => 'payment_driver', 'label' => 'درگاه'],
            ['key' => 'payment_status', 'label' => 'وضعیت'],
            ['key' => 'payment_ref_id', 'label' => 'کد پیگیری'],
            ['key' => 'paid_at', 'label' => 'تاریخ پرداخت'],
        ]" :rows="$transactions" :sort-by="$sortBy" with-pagination>
            @scope('cell_price', $t)
                {{ number_format($t->price) }} تومان
            @endscope
            @scope('cell_payment_method', $t)
                {{ $t->payment_method === 'online' ? 'آنلاین' : 'نقدی' }}
            @endscope
            @scope('cell_payment_driver', $t)
                {{ $t->payment_driver ?? '—' }}
            @endscope
            @scope('cell_payment_status', $t)
                <x-badge :value="$t->payment_status === 'paid' ? 'پرداخت شده' : 'در انتظار'"
                    :class="$t->payment_status === 'paid' ? 'badge-success' : 'badge-warning'" />
            @endscope
            @scope('cell_paid_at', $t)
                {{ $t->paid_at?->format('Y-m-d H:i') ?? '—' }}
            @endscope
            @scope('actions', $t)
                <x-button icon="o-eye" link="{{ route('admin.payments.show', $t) }}" class="btn-ghost btn-sm"
                    tooltip="جزئیات" />
            @endscope
        </x-table>
    </x-card>
</div>
