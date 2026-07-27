<div>
    <x-header title="مدیریت سفارش‌ها" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="جستجو..." wire:model.live.debounce.400ms="search" icon="o-magnifying-glass" clearable />
        </x-slot:middle>
    </x-header>

    {{-- فیلترها --}}
    <x-card class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <x-select label="وضعیت" wire:model="statusFilter" :options="array_map(fn($k, $v) => ['id' => $k, 'name' => $v], array_keys($this->statusLabels()), $this->statusLabels())"
                    multiple placeholder="همه وضعیت‌ها" />
            </div>
            <div>
                <x-input label="از تاریخ" wire:model="dateFrom" type="date" />
            </div>
            <div>
                <x-input label="تا تاریخ" wire:model="dateTo" type="date" />
            </div>
            <div>
                <x-select label="روش پرداخت" wire:model="paymentMethod" placeholder="همه"
                    :options="[['id' => 'online', 'name' => 'آنلاین'], ['id' => 'cash_on_delivery', 'name' => 'نقدی']]" />
            </div>
            <div>
                <x-select label="وضعیت پرداخت" wire:model="paymentStatus" placeholder="همه"
                    :options="[['id' => 'pending', 'name' => 'در انتظار'], ['id' => 'paid', 'name' => 'پرداخت شده']]" />
            </div>
        </div>
    </x-card>

    <x-card>
        <x-table :headers="[
            ['key' => 'id', 'label' => '#'],
            ['key' => 'customer.name', 'label' => 'مشتری', 'sortable' => false],
            ['key' => 'sender_name', 'label' => 'فرستنده'],
            ['key' => 'receiver_name', 'label' => 'گیرنده'],
            ['key' => 'status', 'label' => 'وضعیت'],
            ['key' => 'price', 'label' => 'مبلغ'],
            ['key' => 'payment_method', 'label' => 'روش پرداخت'],
            ['key' => 'payment_status', 'label' => 'وضعیت پرداخت'],
            ['key' => 'courier.name', 'label' => 'پیک', 'sortable' => false],
            ['key' => 'created_at', 'label' => 'تاریخ ایجاد'],
        ]" :rows="$orders" :sort-by="$sortBy" with-pagination>
            @scope('cell_status', $order)
                <x-badge :value="$this->statusLabels()[$order->status] ?? $order->status" :class="$this->statusBadgeClass($order->status) . ' badge-sm'" />
            @endscope

            @scope('cell_price', $order)
                {{ number_format($order->price) }} تومان
            @endscope

            @scope('cell_payment_method', $order)
                {{ $order->payment_method === 'online' ? 'آنلاین' : 'نقدی' }}
            @endscope

            @scope('cell_payment_status', $order)
                <x-badge :value="$order->payment_status === 'paid' ? 'پرداخت شده' : 'در انتظار'"
                    :class="$order->payment_status === 'paid' ? 'badge-success' : 'badge-warning'" />
            @endscope

            @scope('cell_created_at', $order)
                {{ $order->created_at->toJalali() ?? $order->created_at->format('Y-m-d H:i') }}
            @endscope

            @scope('actions', $order)
                <div class="flex gap-1">
                    <x-button icon="o-eye" link="{{ route('admin.orders.show', $order) }}" class="btn-ghost btn-sm"
                        tooltip="جزئیات" />
                    @can('manual order actions')
                        <x-button icon="o-adjustments-horizontal" link="{{ route('admin.orders.manual-actions', $order) }}"
                            class="btn-ghost btn-sm" tooltip="اقدامات دستی" />
                    @endcan
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
