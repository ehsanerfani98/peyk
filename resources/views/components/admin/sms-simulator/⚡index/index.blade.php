<div>
    <x-header title="شبیه‌ساز پیامک" subtitle="مشاهده پیامک‌های ثبت‌شده در حالت شبیه‌سازی" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="جستجو در گیرنده، فرستنده یا متن پیامک..."
                wire:model.live.debounce.400ms="search" icon="o-magnifying-glass" clearable />
        </x-slot:middle>
        <x-slot:actions>
            @can('clear sms simulator')
                <x-button label="خالی کردن جدول" icon="o-trash" class="btn-error btn-sm"
                    wire:click="$set('showClearModal', true)" />
            @endcan
        </x-slot:actions>
    </x-header>

    @unless ($isSimulatorMode)
        <x-alert title="حالت شبیه‌ساز غیرفعال است"
            description="در حال حاضر ارسال پیامک روی حالت واقعی (مدیانا) تنظیم شده است؛ بنابراین پیامک جدیدی در این جدول ثبت نمی‌شود. برای تغییر حالت به تنظیمات ← پیامک مراجعه کنید."
            icon="o-exclamation-triangle" class="alert-warning mb-4" />
    @endunless

    <x-card>
        <x-table :headers="[
            ['key' => 'created_at', 'label' => 'زمان', 'sortable' => false],
            ['key' => 'receiver', 'label' => 'گیرنده', 'sortable' => false],
            ['key' => 'sender', 'label' => 'فرستنده', 'sortable' => false],
            ['key' => 'content', 'label' => 'متن پیامک', 'sortable' => false],
            ['key' => 'pattern_code', 'label' => 'کد پترن', 'sortable' => false],
        ]" :rows="$messages" with-pagination>
            @scope('cell_created_at', $message)
                {{ $message->created_at->format('Y-m-d H:i:s') }}
            @endscope

            @scope('cell_receiver', $message)
                <span dir="ltr">{{ $message->receiver }}</span>
            @endscope

            @scope('cell_sender', $message)
                <span dir="ltr">{{ $message->sender }}</span>
            @endscope

            @scope('cell_content', $message)
                <div x-data="{ copied: false }" class="flex items-center gap-2">
                    <span dir="auto" class="font-mono text-sm break-all">{{ $message->content }}</span>
                    <x-button icon="o-clipboard-document" class="btn-ghost btn-xs shrink-0"
                        tooltip-left="کپی متن"
                        x-on:click="navigator.clipboard.writeText(@js($message->content)); copied = true; setTimeout(() => copied = false, 1500)" />
                </div>
            @endscope

            @scope('cell_pattern_code', $message)
                <span dir="ltr" class="badge badge-ghost badge-sm">{{ $message->pattern_code }}</span>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="showClearModal" title="خالی کردن جدول پیامک‌ها" separator>
        <p>آیا از حذف تمام پیامک‌های ثبت‌شده در حالت شبیه‌سازی مطمئن هستید؟ این عملیات قابل بازگشت نیست.</p>

        <x-slot:actions>
            <x-button label="انصراف" wire:click="$set('showClearModal', false)" />
            <x-button label="بله، خالی کن" class="btn-error" wire:click="clearMessages" spinner="clearMessages" />
        </x-slot:actions>
    </x-modal>
</div>
