<?php

use App\Models\Setting;
use App\Services\Admin\ActivityLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('تنظیمات پرداخت')] class extends Component
{
    use Toast;

    // General
    public string $default_driver = '';

    public string $sandbox_mode = '';

    // Zarinpal
    public string $zarinpal_merchant_id = '';

    public string $zarinpal_currency = '';

    public string $zarinpal_base_url = '';

    public string $zarinpal_sandbox_base_url = '';

    public string $zarinpal_start_pay_url = '';

    public string $zarinpal_sandbox_start_pay_url = '';

    // Zibal
    public string $zibal_merchant = '';

    public string $zibal_base_url = '';

    public function mount(): void
    {
        $this->default_driver = Setting::getValue('payment_default_driver', config('payment.default'));
        $this->sandbox_mode = Setting::getValue('payment_sandbox_mode', 'disabled');

        $this->zarinpal_merchant_id = Setting::getValue('zarinpal_merchant_id', config('payment.drivers.zarinpal.merchant_id'));
        $this->zarinpal_currency = Setting::getValue('zarinpal_currency', config('payment.drivers.zarinpal.currency'));
        $this->zarinpal_base_url = Setting::getValue('zarinpal_base_url', config('payment.drivers.zarinpal.base_url'));
        $this->zarinpal_sandbox_base_url = Setting::getValue('zarinpal_sandbox_base_url', config('payment.drivers.zarinpal.sandbox_base_url'));
        $this->zarinpal_start_pay_url = Setting::getValue('zarinpal_start_pay_url', config('payment.drivers.zarinpal.start_pay_url'));
        $this->zarinpal_sandbox_start_pay_url = Setting::getValue('zarinpal_sandbox_start_pay_url', config('payment.drivers.zarinpal.sandbox_start_pay_url'));

        $this->zibal_merchant = Setting::getValue('zibal_merchant', config('payment.drivers.zibal.merchant'));
        $this->zibal_base_url = Setting::getValue('zibal_base_url', config('payment.drivers.zibal.base_url'));
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('manage settings'), 403);

        Setting::setValue('payment_default_driver', $this->default_driver, 'payment');
        Setting::setValue('payment_sandbox_mode', $this->sandbox_mode, 'payment');

        Setting::setValue('zarinpal_merchant_id', $this->zarinpal_merchant_id, 'payment');
        Setting::setValue('zarinpal_currency', $this->zarinpal_currency, 'payment');
        Setting::setValue('zarinpal_base_url', $this->zarinpal_base_url, 'payment');
        Setting::setValue('zarinpal_sandbox_base_url', $this->zarinpal_sandbox_base_url, 'payment');
        Setting::setValue('zarinpal_start_pay_url', $this->zarinpal_start_pay_url, 'payment');
        Setting::setValue('zarinpal_sandbox_start_pay_url', $this->zarinpal_sandbox_start_pay_url, 'payment');

        Setting::setValue('zibal_merchant', $this->zibal_merchant, 'payment');
        Setting::setValue('zibal_base_url', $this->zibal_base_url, 'payment');

        app(ActivityLogger::class)->log('settings.payment_updated', auth()->user());
        $this->success('تنظیمات پرداخت ذخیره شد', position: 'toast-bottom toast-end');
    }
};
