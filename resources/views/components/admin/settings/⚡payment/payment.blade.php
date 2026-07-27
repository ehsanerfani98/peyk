<div>
    <x-header title="تنظیمات پرداخت" separator progress-indicator />

    <x-form wire:submit="save" class="max-w-2xl">
        {{-- تنظیمات عمومی --}}
        <x-card title="تنظیمات عمومی" icon="o-cog-6-tooth" shadow class="mb-4">
            <x-select label="درگاه پیش‌فرض" wire:model="default_driver"
                :options="[['id' => 'zarinpal', 'name' => 'زرین‌پال'], ['id' => 'zibal', 'name' => 'زیبال']]" />

            <x-select label="حالت سندباکس" wire:model="sandbox_mode"
                :options="[
                    ['id' => 'disabled', 'name' => 'غیرفعال (واقعی)'],
                    ['id' => 'enabled', 'name' => 'فعال (سندباکس)'],
                ]"
                hint="در حالت سندباکس، تراکنش‌ها آزمایشی هستند و پولی جابجا نمی‌شود." />
        </x-card>

        {{-- زرین‌پال --}}
        <x-card title="تنظیمات زرین‌پال" icon="o-credit-card" shadow class="mb-4">
            <x-input label="Merchant ID" wire:model="zarinpal_merchant_id" type="password"
                hint="کلید درگاه زرین‌پال" />
            <x-input label="واحد پول" wire:model="zarinpal_currency"
                hint="IRT (تومان) یا IRR (ریال)" />
            <x-input label="آدرس پایه API" wire:model="zarinpal_base_url" />
            <x-input label="آدرس پایه سندباکس" wire:model="zarinpal_sandbox_base_url" />
            <x-input label="آدرس شروع پرداخت" wire:model="zarinpal_start_pay_url" />
            <x-input label="آدرس شروع پرداخت سندباکس" wire:model="zarinpal_sandbox_start_pay_url" />
        </x-card>

        {{-- زیبال --}}
        <x-card title="تنظیمات زیبال" icon="o-credit-card" shadow class="mb-4">
            <x-input label="Merchant" wire:model="zibal_merchant" type="password"
                hint="در حالت سندباکس مقدار 'zibal' استفاده می‌شود" />
            <x-input label="آدرس پایه API" wire:model="zibal_base_url" />
        </x-card>

        <x-card>
            <x-slot:actions>
                <x-button label="ذخیره تمام تنظیمات" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-card>
    </x-form>
</div>
