<?php

use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('پروفایل من')] class extends Component
{
    use Toast;

    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile(UpdatesUserProfileInformation $updater): void
    {
        $updater->update(auth()->user(), [
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->success(
            'اطلاعات پروفایل به‌روزرسانی شد',
            position: 'toast-bottom toast-end'
        );

    }

    public function updatePassword(UpdatesUserPasswords $updater): void
    {
        $updater->update(auth()->user(), [
            'current_password' => $this->current_password,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->success(
            'رمز عبور تغییر کرد',
            position: 'toast-bottom toast-end'
        );
    }
};
