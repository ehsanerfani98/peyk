<div class="md:w-96 mx-auto mt-20">
    <div class="mb-10 text-center">
        <h1 class="text-2xl font-bold">ورود به حساب کاربری</h1>
    </div>

    <x-form wire:submit="login">
        <x-input label="ایمیل" wire:model="email" icon="o-envelope" />
        <x-input label="رمز عبور" wire:model="password" type="password" icon="o-key" />
        <x-checkbox label="مرا به خاطر بسپار" wire:model="remember" />

        <x-slot:actions>
            <x-button label="ثبت‌نام" class="btn-ghost" link="/register" />
            <x-button label="ورود" type="submit" class="btn-primary" spinner="login" />
        </x-slot:actions>
    </x-form>
</div>
