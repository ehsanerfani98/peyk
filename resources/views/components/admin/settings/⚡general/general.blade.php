<div>
    <x-header title="تنظیمات عمومی" separator progress-indicator />

    <x-card>
        <x-form wire:submit="save" class="max-w-xl">
            <x-input label="نام سایت" wire:model="site_name" icon="o-globe-alt" />
            <x-select label="حالت Debug" wire:model="app_debug"
                :options="[
                    ['id' => 'false', 'name' => 'غیرفعال'],
                    ['id' => 'true', 'name' => 'فعال'],
                ]"
                hint="در حالت فعال، خطاها با جزئیات نمایش داده می‌شوند." />
            <x-input label="آدرس سایت" wire:model="app_url" icon="o-link" />
            <x-input label="شماره پشتیبانی" wire:model="support_phone" icon="o-phone" />
            <x-input label="آدرس" wire:model="site_address" icon="o-map-pin" />

            <x-slot:actions>
                <x-button label="ذخیره" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
