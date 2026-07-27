<div>
    <x-header title="تنظیمات جستجوی پیک" separator progress-indicator />

    <x-card>
        <x-form wire:submit="save" class="max-w-xl">
            <x-input label="فاصله بین تلاش‌ها (ثانیه)" wire:model="courier_search_interval" type="number" />
            <x-input label="سقف زمانی جستجو (دقیقه)" wire:model="courier_search_timeout" type="number" />
            <x-input label="حداکثر شعاع جستجو (متر)" wire:model="courier_search_max_distance" type="number" />
            <x-input label="مهلت پاسخ پیک (ثانیه)" wire:model="courier_offer_timeout" type="number" />

            <x-slot:actions>
                <x-button label="ذخیره" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
