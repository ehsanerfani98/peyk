<div>
    <x-header title="ثبت پیک جدید" separator progress-indicator>
        <x-slot:actions>
            <x-button label="بازگشت به لیست" icon="o-arrow-right" link="{{ route('admin.couriers') }}"
                class="btn-ghost btn-sm" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-form wire:submit="save" class="max-w-2xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="نام" wire:model="name" icon="o-user" required />
                <x-input label="ایمیل" wire:model="email" icon="o-envelope" type="email" required />
                <x-input label="موبایل" wire:model="mobile" icon="o-phone" required />
                <x-input label="کد ملی" wire:model="national_code" icon="o-identification" required />
                <x-input label="رمز عبور" wire:model="password" type="password" icon="o-key" required />
                <x-input label="تکرار رمز عبور" wire:model="password_confirmation" type="password" icon="o-key" required />
                <x-select label="وسیله نقلیه" wire:model="vehicle_type" placeholder="انتخاب کنید..."
                    :options="[
                        ['id' => 'motorcycle', 'name' => 'موتور'],
                        ['id' => 'car', 'name' => 'ماشین'],
                        ['id' => 'bicycle', 'name' => 'دوچرخه'],
                        ['id' => 'pickup', 'name' => 'وانت'],
                    ]" required />
                <x-input label="شماره وسیله" wire:model="vehicle_number" icon="o-truck" required />
            </div>

            <x-slot:actions>
                <x-button label="انصراف" class="btn-error btn-soft" link="{{ route('admin.couriers') }}" />
                <x-button label="ثبت پیک" type="submit" class="btn-success btn-soft" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
