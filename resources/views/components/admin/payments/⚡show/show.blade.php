<div>
    <x-header title="جزئیات تراکنش - سفارش #{{ $order->id }}" separator progress-indicator>
        <x-slot:actions>
            <x-button label="بازگشت به لیست" icon="o-arrow-right" link="{{ route('admin.payments') }}"
                class="btn-ghost btn-sm" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-card title="اطلاعات تراکنش" icon="o-credit-card" shadow>
            <div class="space-y-2">
                <p><span class="font-bold">سفارش:</span> #{{ $order->id }}</p>
                <p><span class="font-bold">مشتری:</span> {{ $order->customer?->name ?? '—' }}</p>
                <p><span class="font-bold">مبلغ:</span> {{ number_format($order->price) }} تومان</p>
                <p><span class="font-bold">روش پرداخت:</span> {{ $order->payment_method === 'online' ? 'آنلاین' : 'نقدی' }}</p>
                <p><span class="font-bold">درگاه:</span> {{ $order->payment_driver ?? '—' }}</p>
                <p><span class="font-bold">وضعیت پرداخت:</span>
                    <x-badge :value="$order->payment_status === 'paid' ? 'پرداخت شده' : 'در انتظار'"
                        :class="$order->payment_status === 'paid' ? 'badge-success' : 'badge-warning'" />
                </p>
                @if ($order->payment_authority)
                    <p><span class="font-bold">Authority:</span> {{ $order->payment_authority }}</p>
                @endif
                @if ($order->payment_ref_id)
                    <p><span class="font-bold">کد پیگیری:</span> {{ $order->payment_ref_id }}</p>
                @endif
                @if ($order->paid_at)
                    <p><span class="font-bold">تاریخ پرداخت:</span> {{ $order->paid_at->format('Y-m-d H:i') }}</p>
                @endif
            </div>
        </x-card>

        <x-card title="اقدامات" icon="o-adjustments-horizontal" shadow>
            <div class="space-y-3">
                @can('manage payments')
                    <x-button label="ثبت دستی پرداخت" icon="o-check-circle" wire:click="markAsPaid"
                        wire:confirm="از پرداخت این سفارش اطمینان دارید؟" class="btn-success btn-block"
                        spinner="markAsPaid" />
                @endcan
                @can('process refunds')
                    <x-button label="بازپرداخت" icon="o-arrow-uturn-left" wire:click="processRefund"
                        wire:confirm="از بازپرداخت این سفارش اطمینان دارید؟" class="btn-error btn-block"
                        spinner="processRefund" />
                @endcan
            </div>
        </x-card>
    </div>

    {{-- تاریخچه بازپرداخت‌ها --}}
    @if ($order->refunds->isNotEmpty())
        <x-card title="تاریخچه بازپرداخت‌ها" icon="o-clock" shadow class="mt-4">
            <x-table :headers="[
                ['key' => 'id', 'label' => '#'],
                ['key' => 'amount', 'label' => 'مبلغ'],
                ['key' => 'reason', 'label' => 'دلیل'],
                ['key' => 'processed_by', 'label' => 'پردازشگر'],
                ['key' => 'status', 'label' => 'وضعیت'],
                ['key' => 'refunded_at', 'label' => 'تاریخ'],
            ]" :rows="$order->refunds">
                @scope('cell_amount', $refund)
                    {{ number_format($refund->amount) }} تومان
                @endscope
                @scope('cell_processed_by', $refund)
                    {{ $refund->processedBy?->name ?? '—' }}
                @endscope
                @scope('cell_status', $refund)
                    <x-badge :value="$refund->status" />
                @endscope
                @scope('cell_refunded_at', $refund)
                    {{ $refund->refunded_at?->format('Y-m-d H:i') ?? '—' }}
                @endscope
            </x-table>
        </x-card>
    @endif
</div>
