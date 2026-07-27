<div>
    <x-header title="پروفایل مشتری: {{ $customer->name }}" separator progress-indicator>
        <x-slot:actions>
            <x-button label="بازگشت به لیست" icon="o-arrow-right" link="{{ route('admin.customers') }}"
                class="btn-ghost btn-sm" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-card title="اطلاعات مشتری" icon="o-user" shadow>
            <div class="space-y-2">
                <p><span class="font-bold">نام:</span> {{ $customer->name }}</p>
                <p><span class="font-bold">موبایل:</span> {{ $customer->mobile }}</p>
                <p><span class="font-bold">ایمیل:</span> {{ $customer->email }}</p>
                <p><span class="font-bold">آدرس:</span> {{ $customer->address ?? '—' }}</p>
                <p><span class="font-bold">تعداد سفارش‌ها:</span> {{ $customer->orders_count }}</p>
                <p><span class="font-bold">مجموع هزینه سفارش‌ها:</span> {{ number_format($customer->orders_sum_price ?? 0) }} تومان</p>
                <p><span class="font-bold">تاریخ ثبت‌نام:</span> {{ $customer->created_at->format('Y-m-d') }}</p>
            </div>
            <x-slot:actions>
                <x-button label="مسدودسازی/رفع مسدودیت" icon="o-no-symbol" wire:click="toggleBlock"
                    class="btn-warning btn-sm" spinner="toggleBlock" />
            </x-slot:actions>
        </x-card>
    </div>

    {{-- تاریخچه سفارش‌ها --}}
    <x-card title="تاریخچه سفارش‌ها" icon="o-truck" shadow class="mt-4">
        <x-table :headers="[
            ['key' => 'id', 'label' => '#'],
            ['key' => 'sender_name', 'label' => 'فرستنده'],
            ['key' => 'receiver_name', 'label' => 'گیرنده'],
            ['key' => 'status', 'label' => 'وضعیت'],
            ['key' => 'price', 'label' => 'مبلغ'],
            ['key' => 'courier.name', 'label' => 'پیک', 'sortable' => false],
            ['key' => 'created_at', 'label' => 'تاریخ'],
        ]" :rows="$orders">
            @scope('cell_status', $order)
                <x-badge :value="$order->status" class="badge-sm" />
            @endscope
            @scope('cell_price', $order)
                {{ number_format($order->price) }} تومان
            @endscope
            @scope('cell_created_at', $order)
                {{ $order->created_at->format('Y-m-d H:i') }}
            @endscope
            @scope('actions', $order)
                <x-button icon="o-eye" link="{{ route('admin.orders.show', $order) }}" class="btn-ghost btn-sm" />
            @endscope
        </x-table>
    </x-card>
</div>
