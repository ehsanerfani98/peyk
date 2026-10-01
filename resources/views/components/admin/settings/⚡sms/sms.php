<?php

use App\Models\Setting;
use App\Services\Admin\ActivityLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('تنظیمات پیامک')] class extends Component
{
    use Toast;

    /**
     * پیشوند تنظیمات اعتبارنامه سرویس مدیانا در دیتابیس.
     */
    private const MEDIANA_PREFIX = 'mediana.';

    /**
     * پیشوند تاریخی تنظیمات پیامک در دیتابیس.
     *
     * کدهای پترن و کلیدهای پارامتر قبلاً با این پیشوند در جدول settings ذخیره
     * شده‌اند؛ برای خواندن همان داده‌ها (بدون مهاجرت) این پیشوند حفظ شده است.
     */
    private const LEGACY_PREFIX = 'ippanel.';

    // General
    public string $sms_mode = 'simulator';

    public string $sms_simulator_base_url = '';

    public string $base_url = '';

    public string $api_key = '';

    public string $type = 'Informational';

    public string $sending_number = '';

    // OTP
    public string $otp_pattern_code = '';

    // Verification Link
    public string $verification_link_pattern_code = '';

    public string $verification_link_param_key = '';

    // Order Status
    public string $order_status_param_key = '';

    public string $non_customer_status_param_key = '';

    // Order Change Status Patterns
    public string $pattern_order_searching = '';

    public string $pattern_order_courier_assigned = '';

    public string $pattern_order_waiting_pickup = '';

    public string $pattern_order_picked_up = '';

    public string $pattern_order_in_transit = '';

    public string $pattern_order_delivered = '';

    public string $pattern_order_cancelled = '';

    public string $pattern_courier_not_found = '';

    // Sender Change Status Patterns
    public string $pattern_sender_waiting_pickup = '';

    public string $pattern_sender_picked_up = '';

    public string $pattern_sender_in_transit = '';

    public string $pattern_sender_delivered = '';

    public string $pattern_sender_cancelled = '';

    // Courier Patterns
    public string $pattern_courier_offer = '';

    public string $courier_offer_param_key = '';

    public string $pattern_courier_cancelled = '';

    public string $courier_cancelled_param_key = '';

    // System Cancellation
    public string $pattern_system_cancellation = '';

    public string $system_cancellation_param_key = '';

    // Survey Link
    public string $pattern_survey_link = '';

    public string $survey_link_param_key = '';

    // Cash on Delivery
    public string $pattern_cash_on_delivery = '';

    public string $cash_on_delivery_param_key = '';

    public function mount(): void
    {
        $this->sms_mode = (string) Setting::getValue('sms_mode', config('sms_simulator.mode', 'simulator'));
        $this->sms_simulator_base_url = (string) Setting::getValue('sms_simulator_base_url', config('sms_simulator.base_url', 'http://localhost:3000'));

        $this->base_url = $this->medianaSetting('base_url');
        $this->api_key = $this->medianaSetting('api_key');
        $this->type = $this->medianaSetting('type', 'Informational');
        $this->sending_number = $this->medianaSetting('sending_number');

        $this->otp_pattern_code = $this->patternSetting('otp_pattern_code');

        $this->verification_link_pattern_code = $this->patternSetting('verification_link_pattern_code');
        $this->verification_link_param_key = $this->patternSetting('verification_link_param_key', 'code');

        $this->order_status_param_key = $this->patternSetting('order_status_param_key', 'code');
        $this->non_customer_status_param_key = $this->patternSetting('non_customer_status_param_key', 'code');

        $this->pattern_order_searching = $this->patternSetting('pattern_order_searching');
        $this->pattern_order_courier_assigned = $this->patternSetting('pattern_order_courier_assigned');
        $this->pattern_order_waiting_pickup = $this->patternSetting('pattern_order_waiting_pickup');
        $this->pattern_order_picked_up = $this->patternSetting('pattern_order_picked_up');
        $this->pattern_order_in_transit = $this->patternSetting('pattern_order_in_transit');
        $this->pattern_order_delivered = $this->patternSetting('pattern_order_delivered');
        $this->pattern_order_cancelled = $this->patternSetting('pattern_order_cancelled');
        $this->pattern_courier_not_found = $this->patternSetting('pattern_courier_not_found');

        $this->pattern_sender_waiting_pickup = $this->patternSetting('pattern_sender_order_waiting_pickup');
        $this->pattern_sender_picked_up = $this->patternSetting('pattern_sender_order_picked_up');
        $this->pattern_sender_in_transit = $this->patternSetting('pattern_sender_order_in_transit');
        $this->pattern_sender_delivered = $this->patternSetting('pattern_sender_order_delivered');
        $this->pattern_sender_cancelled = $this->patternSetting('pattern_sender_order_cancelled');

        $this->pattern_courier_offer = $this->patternSetting('pattern_courier_offer');
        $this->courier_offer_param_key = $this->patternSetting('courier_offer_param_key', 'code');
        $this->pattern_courier_cancelled = $this->patternSetting('pattern_courier_cancelled');
        $this->courier_cancelled_param_key = $this->patternSetting('courier_cancelled_param_key', 'code');

        $this->pattern_system_cancellation = $this->patternSetting('pattern_system_cancellation');
        $this->system_cancellation_param_key = $this->patternSetting('system_cancellation_param_key', 'code');

        $this->pattern_survey_link = $this->patternSetting('pattern_survey_link');
        $this->survey_link_param_key = $this->patternSetting('survey_link_param_key', 'code');

        $this->pattern_cash_on_delivery = $this->patternSetting('pattern_cash_on_delivery');
        $this->cash_on_delivery_param_key = $this->patternSetting('cash_on_delivery_param_key', 'code');
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('manage settings'), 403);

        $this->validate([
            'sms_mode' => 'required|in:simulator,real',
            'type' => 'required|in:Informational,PromotionalToCustomers,PromotionalAll',
        ]);

        $group = 'sms';
        $credentialsPrefix = self::MEDIANA_PREFIX;
        $patternPrefix = self::LEGACY_PREFIX;

        Setting::setValue('sms_mode', $this->sms_mode, $group);
        Setting::setValue('sms_simulator_base_url', $this->sms_simulator_base_url, $group);
        Setting::setValue("{$credentialsPrefix}base_url", $this->base_url, $group);
        Setting::setValue("{$credentialsPrefix}api_key", $this->api_key, $group);
        Setting::setValue("{$credentialsPrefix}type", $this->type, $group);
        Setting::setValue("{$credentialsPrefix}sending_number", $this->sending_number, $group);

        Setting::setValue("{$patternPrefix}otp_pattern_code", $this->otp_pattern_code, $group);

        Setting::setValue("{$patternPrefix}verification_link_pattern_code", $this->verification_link_pattern_code, $group);
        Setting::setValue("{$patternPrefix}verification_link_param_key", $this->verification_link_param_key, $group);

        Setting::setValue("{$patternPrefix}order_status_param_key", $this->order_status_param_key, $group);
        Setting::setValue("{$patternPrefix}non_customer_status_param_key", $this->non_customer_status_param_key, $group);

        Setting::setValue("{$patternPrefix}pattern_order_searching", $this->pattern_order_searching, $group);
        Setting::setValue("{$patternPrefix}pattern_order_courier_assigned", $this->pattern_order_courier_assigned, $group);
        Setting::setValue("{$patternPrefix}pattern_order_waiting_pickup", $this->pattern_order_waiting_pickup, $group);
        Setting::setValue("{$patternPrefix}pattern_order_picked_up", $this->pattern_order_picked_up, $group);
        Setting::setValue("{$patternPrefix}pattern_order_in_transit", $this->pattern_order_in_transit, $group);
        Setting::setValue("{$patternPrefix}pattern_order_delivered", $this->pattern_order_delivered, $group);
        Setting::setValue("{$patternPrefix}pattern_order_cancelled", $this->pattern_order_cancelled, $group);
        Setting::setValue("{$patternPrefix}pattern_courier_not_found", $this->pattern_courier_not_found, $group);

        Setting::setValue("{$patternPrefix}pattern_sender_order_waiting_pickup", $this->pattern_sender_waiting_pickup, $group);
        Setting::setValue("{$patternPrefix}pattern_sender_order_picked_up", $this->pattern_sender_picked_up, $group);
        Setting::setValue("{$patternPrefix}pattern_sender_order_in_transit", $this->pattern_sender_in_transit, $group);
        Setting::setValue("{$patternPrefix}pattern_sender_order_delivered", $this->pattern_sender_delivered, $group);
        Setting::setValue("{$patternPrefix}pattern_sender_order_cancelled", $this->pattern_sender_cancelled, $group);

        Setting::setValue("{$patternPrefix}pattern_courier_offer", $this->pattern_courier_offer, $group);
        Setting::setValue("{$patternPrefix}courier_offer_param_key", $this->courier_offer_param_key, $group);
        Setting::setValue("{$patternPrefix}pattern_courier_cancelled", $this->pattern_courier_cancelled, $group);
        Setting::setValue("{$patternPrefix}courier_cancelled_param_key", $this->courier_cancelled_param_key, $group);

        Setting::setValue("{$patternPrefix}pattern_system_cancellation", $this->pattern_system_cancellation, $group);
        Setting::setValue("{$patternPrefix}system_cancellation_param_key", $this->system_cancellation_param_key, $group);

        Setting::setValue("{$patternPrefix}pattern_survey_link", $this->pattern_survey_link, $group);
        Setting::setValue("{$patternPrefix}survey_link_param_key", $this->survey_link_param_key, $group);

        Setting::setValue("{$patternPrefix}pattern_cash_on_delivery", $this->pattern_cash_on_delivery, $group);
        Setting::setValue("{$patternPrefix}cash_on_delivery_param_key", $this->cash_on_delivery_param_key, $group);

        app(ActivityLogger::class)->log('settings.sms_updated', auth()->user());
        $this->success('تنظیمات پیامک ذخیره شد', position: 'toast-bottom toast-end');
    }

    /**
     * خواندن اعتبارنامه سرویس مدیانا (base_url/api_key/type/sending_number).
     */
    private function medianaSetting(string $key, string $default = ''): string
    {
        return $this->stringSetting(self::MEDIANA_PREFIX, $key, $default);
    }

    /**
     * خواندن کد پترن یا کلید پارامتر از پیشوند تاریخی `ippanel.`.
     */
    private function patternSetting(string $key, string $default = ''): string
    {
        return $this->stringSetting(self::LEGACY_PREFIX, $key, $default);
    }

    /**
     * خواندن یک تنظیم پیامک با تضمین رشته بودن مقدار.
     *
     * مقادیر ناموجود/غیرمعتبر (برای مثال زمانی که MEDIANA_* در .env تعریف نشده
     * یا کش config قدیمی است) به رشته خالی یا مقدار پیش‌فرض تبدیل می‌شوند تا
     * به پراپرتی‌های نوع string اختصاص یابند و TypeError رخ ندهد.
     */
    private function stringSetting(string $prefix, string $key, string $default): string
    {
        $configured = config("mediana.{$key}", $default);

        if (! is_scalar($configured) || $configured === '') {
            $configured = $default;
        }

        $stored = Setting::getValue($prefix.$key, $configured);

        return is_scalar($stored) ? (string) $stored : $default;
    }
};
