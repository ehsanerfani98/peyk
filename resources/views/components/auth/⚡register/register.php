<?php

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.guest')] #[Title('ثبت‌نام')] class extends Component
{
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|unique:users,email')]
    public string $email = '';

    #[Rule('required|confirmed|min:8')]
    public string $password = '';

    #[Rule('required')]
    public string $password_confirmation = '';

    public function mount()
    {
        if (auth()->check()) {
            return redirect()->intended('/');
        }
    }

    public function register(CreatesNewUsers $creator)
    {
        $data = $this->validate();

        $user = $creator->create($data);

        event(new Registered($user));

        Auth::login($user);

        request()->session()->regenerate();

        return redirect()->intended('/');
    }
};
