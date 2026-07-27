<div>
    <x-header title="مدیریت نظرسنجی‌ها" separator progress-indicator />

    {{-- آمار --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <x-card title="کل نظرسنجی‌ها" icon="o-clipboard-document-list" shadow>
            <p class="text-2xl font-bold">{{ $totalCount }}</p>
        </x-card>
        <x-card title="مشارکت" icon="o-check-circle" shadow>
            <p class="text-2xl font-bold">{{ $usedCount }}</p>
            <p class="text-sm text-base-500">از {{ $totalCount }} نظرسنجی</p>
        </x-card>
        <x-card title="میانگین امتیاز" icon="o-star" shadow>
            <p class="text-2xl font-bold">{{ number_format($avgRating ?? 0, 1) }}</p>
        </x-card>
    </div>

    <x-card class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-select label="وضعیت" wire:model="statusFilter" placeholder="همه"
                :options="[
                    ['id' => 'pending', 'name' => 'منتظر'],
                    ['id' => 'used', 'name' => 'استفاده شده'],
                    ['id' => 'expired', 'name' => 'منقضی'],
                ]" />
            <x-select label="نوع" wire:model="typeFilter" placeholder="همه"
                :options="[
                    ['id' => 'customer', 'name' => 'مشتری'],
                    ['id' => 'sender', 'name' => 'فرستنده'],
                    ['id' => 'receiver', 'name' => 'گیرنده'],
                ]" />
        </div>
    </x-card>

    <x-card>
        <x-table :headers="[
            ['key' => 'id', 'label' => '#'],
            ['key' => 'order_id', 'label' => 'سفارش'],
            ['key' => 'type', 'label' => 'نوع'],
            ['key' => 'status', 'label' => 'وضعیت'],
            ['key' => 'expires_at', 'label' => 'تاریخ انقضا'],
            ['key' => 'used_at', 'label' => 'تاریخ استفاده'],
        ]" :rows="$tokens" :sort-by="$sortBy" with-pagination>
            @scope('cell_type', $token)
                {{ match($token->type) { 'customer' => 'مشتری', 'sender' => 'فرستنده', 'receiver' => 'گیرنده', default => $token->type } }}
            @endscope
            @scope('cell_status', $token)
                @if ($token->used_at)
                    <x-badge value="استفاده شده" class="badge-success" />
                @elseif ($token->isExpired())
                    <x-badge value="منقضی" class="badge-error" />
                @else
                    <x-badge value="منتظر" class="badge-warning" />
                @endif
            @endscope
            @scope('cell_expires_at', $token)
                {{ $token->expires_at->format('Y-m-d H:i') }}
            @endscope
            @scope('cell_used_at', $token)
                {{ $token->used_at?->format('Y-m-d H:i') ?? '—' }}
            @endscope
        </x-table>
    </x-card>
</div>
