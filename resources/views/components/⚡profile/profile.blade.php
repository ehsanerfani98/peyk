<div class="max-w-full">
    <x-header title="پروفایل من" separator progress-indicator />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-card title="اطلاعات حساب">
            <x-form wire:submit="updateProfile">
                <x-input label="نام" wire:model="name" icon="o-user" />
                <x-input label="ایمیل" wire:model="email" icon="o-envelope" />
                <x-slot:actions>
                    <x-button label="ذخیره" type="submit" class="btn-success btn-soft" spinner="updateProfile" />
                </x-slot:actions>
            </x-form>
        </x-card>

        <x-card title="تغییر رمز عبور">
            <x-form wire:submit="updatePassword">
                <x-input label="رمز عبور فعلی" wire:model="current_password" type="password" icon="o-key" />
                <x-input label="رمز عبور جدید" wire:model="password" type="password" icon="o-key" />
                <x-input label="تکرار رمز عبور جدید" wire:model="password_confirmation" type="password"
                    icon="o-key" />
                <x-slot:actions>
                    <x-button label="تغییر رمز" type="submit" class="btn-primary btn-soft" spinner="updatePassword" />
                </x-slot:actions>
            </x-form>
        </x-card>
    </div>
</div>
