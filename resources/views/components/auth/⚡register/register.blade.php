<div class="md:w-96 mx-auto mt-20">
    <div class="mb-10 text-center">
        <h1 class="text-2xl font-bold">ایجاد حساب کاربری</h1>
    </div>

    <x-form wire:submit="register">
        <x-input label="نام" wire:model="name" icon="o-user" />
        <x-input label="ایمیل" wire:model="email" icon="o-envelope" />
        <x-input label="رمز عبور" wire:model="password" type="password" icon="o-key" />
        <x-input label="تکرار رمز عبور" wire:model="password_confirmation" type="password" icon="o-key" />

        <x-slot:actions>
            <x-button label="حساب دارید؟ ورود" class="btn-ghost" link="/login" />
            <x-button label="ثبت‌نام" type="submit" class="btn-primary" spinner="register" />
        </x-slot:actions>
    </x-form>
</div>
