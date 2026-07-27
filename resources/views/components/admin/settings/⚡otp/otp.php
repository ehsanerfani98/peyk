<?php

use App\Models\Setting;
use App\Services\Admin\ActivityLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('تنظیمات OTP')] class extends Component
{
    use Toast;

    public string $otp_length = '';

    public string $otp_expire_seconds = '';

    public string $otp_resend_seconds = '';

    public string $otp_max_attempts = '';

    public function mount(): void
    {
        $this->otp_length = Setting::getValue('otp.length', config('otp.length'));
        $this->otp_expire_seconds = Setting::getValue('otp.expire_seconds', config('otp.expire_seconds'));
        $this->otp_resend_seconds = Setting::getValue('otp.resend_seconds', config('otp.resend_seconds'));
        $this->otp_max_attempts = Setting::getValue('otp.max_attempts', config('otp.max_attempts'));
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('manage settings'), 403);

        $this->validate([
            'otp_length' => 'required|numeric|min:3|max:10',
            'otp_expire_seconds' => 'required|numeric|min:30',
            'otp_resend_seconds' => 'required|numeric|min:10',
            'otp_max_attempts' => 'required|numeric|min:1|max:20',
        ]);

        Setting::setValue('otp.length', $this->otp_length, 'otp');
        Setting::setValue('otp.expire_seconds', $this->otp_expire_seconds, 'otp');
        Setting::setValue('otp.resend_seconds', $this->otp_resend_seconds, 'otp');
        Setting::setValue('otp.max_attempts', $this->otp_max_attempts, 'otp');

        app(ActivityLogger::class)->log('settings.otp_updated', auth()->user());
        $this->success('تنظیمات OTP ذخیره شد', position: 'toast-bottom toast-end');
    }
};
