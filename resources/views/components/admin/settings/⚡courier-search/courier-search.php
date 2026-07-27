<?php

use App\Models\Setting;
use App\Services\Admin\ActivityLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('تنظیمات جستجوی پیک')] class extends Component
{
    use Toast;

    public string $courier_search_interval = '';

    public string $courier_search_timeout = '';

    public string $courier_search_max_distance = '';

    public string $courier_offer_timeout = '';

    public function mount(): void
    {
        $this->courier_search_interval = Setting::getValue('courier_search_interval', config('courier_search.interval_seconds'));
        $this->courier_search_timeout = Setting::getValue('courier_search_timeout', config('courier_search.timeout_minutes'));
        $this->courier_search_max_distance = Setting::getValue('courier_search_max_distance', config('courier_search.max_distance_meters'));
        $this->courier_offer_timeout = Setting::getValue('courier_offer_timeout', config('courier_search.courier_offer_timeout_seconds'));
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('manage settings'), 403);

        $this->validate([
            'courier_search_interval' => 'required|numeric|min:1',
            'courier_search_timeout' => 'required|numeric|min:1',
            'courier_search_max_distance' => 'required|numeric|min:100',
            'courier_offer_timeout' => 'required|numeric|min:10',
        ]);

        Setting::setValue('courier_search_interval', $this->courier_search_interval, 'courier_search');
        Setting::setValue('courier_search_timeout', $this->courier_search_timeout, 'courier_search');
        Setting::setValue('courier_search_max_distance', $this->courier_search_max_distance, 'courier_search');
        Setting::setValue('courier_offer_timeout', $this->courier_offer_timeout, 'courier_search');

        app(ActivityLogger::class)->log('settings.courier_search_updated', auth()->user());
        $this->success('تنظیمات جستجوی پیک ذخیره شد', position: 'toast-bottom toast-end');
    }
};
