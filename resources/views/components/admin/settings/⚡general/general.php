<?php

use App\Models\Setting;
use App\Services\Admin\ActivityLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('تنظیمات عمومی')] class extends Component
{
    use Toast;

    public string $site_name = '';

    public string $app_debug = 'false';

    public string $app_url = '';

    public string $support_phone = '';

    public string $site_address = '';

    public function mount(): void
    {
        $this->site_name = Setting::getValue('site_name', config('app.name'));
        $this->app_debug = Setting::getValue('app_debug', config('app.debug') ? 'true' : 'false');
        $this->app_url = Setting::getValue('app_url', config('app.url'));
        $this->support_phone = Setting::getValue('support_phone', '');
        $this->site_address = Setting::getValue('site_address', '');
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('manage settings'), 403);

        Setting::setValue('site_name', $this->site_name, 'general');
        Setting::setValue('app_debug', $this->app_debug, 'general');
        Setting::setValue('app_url', $this->app_url, 'general');
        Setting::setValue('support_phone', $this->support_phone, 'general');
        Setting::setValue('site_address', $this->site_address, 'general');

        app(ActivityLogger::class)->log('settings.general_updated', auth()->user());
        $this->success('تنظیمات عمومی ذخیره شد', position: 'toast-bottom toast-end');
    }
};
