<div>
    <x-header title="جزئیات نظرسنجی" separator progress-indicator>
        <x-slot:actions>
            <x-button label="بازگشت به لیست" icon="o-arrow-right" link="{{ route('admin.surveys') }}"
                class="btn-ghost btn-sm" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <div class="space-y-2">
            <p><span class="font-bold">شناسه:</span> {{ $survey->id }}</p>
            <p><span class="font-bold">سفارش:</span> #{{ $survey->order_id }}</p>
            <p><span class="font-bold">نوع:</span> {{ match($survey->type) { 'customer' => 'مشتری', 'sender' => 'فرستنده', 'receiver' => 'گیرنده', default => $survey->type } }}</p>
            <p><span class="font-bold">توکن:</span> {{ $survey->token }}</p>
            <p><span class="font-bold">وضعیت:</span>
                @if ($survey->used_at)
                    <x-badge value="استفاده شده" class="badge-success" />
                @elseif ($survey->isExpired())
                    <x-badge value="منقضی" class="badge-error" />
                @else
                    <x-badge value="منتظر" class="badge-warning" />
                @endif
            </p>
            <p><span class="font-bold">تاریخ انقضا:</span> {{ $survey->expires_at->format('Y-m-d H:i') }}</p>
            <p><span class="font-bold">تاریخ استفاده:</span> {{ $survey->used_at?->format('Y-m-d H:i') ?? '—' }}</p>
        </div>
    </x-card>
</div>
