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
     * کلید پیشوند تنظیمات پیامک در دیتابیس.
     */
    private const SETTING_PREFIX = 'mediana.';

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
        $this->sms_mode = Setting::getValue('sms_mode', config('sms_simulator.mode', 'simulator'));
        $this->sms_simulator_base_url = Setting::getValue('sms_simulator_base_url', config('sms_simulator.base_url', 'http://localhost:3000'));
        $this->base_url = Setting::getValue("{$this->prefix()}base_url", config('mediana.base_url'));
        $this->api_key = Setting::getValue("{$this->prefix()}api_key", config('mediana.api_key'));
        $this->type = Setting::getValue("{$this->prefix()}type", config('mediana.type', 'Informational'));
        $this->sending_number = Setting::getValue("{$this->prefix()}sending_number", config('mediana.sending_number')) ?? '';

        $this->otp_pattern_code = Setting::getValue("{$this->prefix()}otp_pattern_code", config('mediana.otp_pattern_code'));

        $this->verification_link_pattern_code = Setting::getValue("{$this->prefix()}verification_link_pattern_code", config('mediana.verification_link_pattern_code'));
        $this->verification_link_param_key = Setting::getValue("{$this->prefix()}verification_link_param_key", config('mediana.verification_link_param_key', 'code'));

        $this->order_status_param_key = Setting::getValue("{$this->prefix()}order_status_param_key", config('mediana.order_status_param_key', 'code'));
        $this->non_customer_status_param_key = Setting::getValue("{$this->prefix()}non_customer_status_param_key", config('mediana.non_customer_status_param_key', 'code'));

        $this->pattern_order_searching = Setting::getValue("{$this->prefix()}pattern_order_searching", config('mediana.pattern_order_searching'));
        $this->pattern_order_courier_assigned = Setting::getValue("{$this->prefix()}pattern_order_courier_assigned", config('mediana.pattern_order_courier_assigned'));
        $this->pattern_order_waiting_pickup = Setting::getValue("{$this->prefix()}pattern_order_waiting_pickup", config('mediana.pattern_order_waiting_pickup'));
        $this->pattern_order_picked_up = Setting::getValue("{$this->prefix()}pattern_order_picked_up", config('mediana.pattern_order_picked_up'));
        $this->pattern_order_in_transit = Setting::getValue("{$this->prefix()}pattern_order_in_transit", config('mediana.pattern_order_in_transit'));
        $this->pattern_order_delivered = Setting::getValue("{$this->prefix()}pattern_order_delivered", config('mediana.pattern_order_delivered'));
        $this->pattern_order_cancelled = Setting::getValue("{$this->prefix()}pattern_order_cancelled", config('mediana.pattern_order_cancelled'));
        $this->pattern_courier_not_found = Setting::getValue("{$this->prefix()}pattern_courier_not_found", config('mediana.pattern_courier_not_found'));

        $this->pattern_sender_waiting_pickup = Setting::getValue("{$this->prefix()}pattern_sender_order_waiting_pickup", config('mediana.pattern_sender_order_waiting_pickup'));
        $this->pattern_sender_picked_up = Setting::getValue("{$this->prefix()}pattern_sender_order_picked_up", config('mediana.pattern_sender_order_picked_up'));
        $this->pattern_sender_in_transit = Setting::getValue("{$this->prefix()}pattern_sender_order_in_transit", config('mediana.pattern_sender_order_in_transit'));
        $this->pattern_sender_delivered = Setting::getValue("{$this->prefix()}pattern_sender_order_delivered", config('mediana.pattern_sender_order_delivered'));
        $this->pattern_sender_cancelled = Setting::getValue("{$this->prefix()}pattern_sender_order_cancelled", config('mediana.pattern_sender_order_cancelled'));

        $this->pattern_courier_offer = Setting::getValue("{$this->prefix()}pattern_courier_offer", config('mediana.pattern_courier_offer'));
        $this->courier_offer_param_key = Setting::getValue("{$this->prefix()}courier_offer_param_key", config('mediana.courier_offer_param_key', 'code'));
        $this->pattern_courier_cancelled = Setting::getValue("{$this->prefix()}pattern_courier_cancelled", config('mediana.pattern_courier_cancelled'));
        $this->courier_cancelled_param_key = Setting::getValue("{$this->prefix()}courier_cancelled_param_key", config('mediana.courier_cancelled_param_key', 'code'));

        $this->pattern_system_cancellation = Setting::getValue("{$this->prefix()}pattern_system_cancellation", config('mediana.pattern_system_cancellation'));
        $this->system_cancellation_param_key = Setting::getValue("{$this->prefix()}system_cancellation_param_key", config('mediana.system_cancellation_param_key', 'code'));

        $this->pattern_survey_link = Setting::getValue("{$this->prefix()}pattern_survey_link", config('mediana.pattern_survey_link'));
        $this->survey_link_param_key = Setting::getValue("{$this->prefix()}survey_link_param_key", config('mediana.survey_link_param_key', 'code'));

        $this->pattern_cash_on_delivery = Setting::getValue("{$this->prefix()}pattern_cash_on_delivery", config('mediana.pattern_cash_on_delivery'));
        $this->cash_on_delivery_param_key = Setting::getValue("{$this->prefix()}cash_on_delivery_param_key", config('mediana.cash_on_delivery_param_key', 'code'));
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('manage settings'), 403);

        $this->validate([
            'sms_mode' => 'required|in:simulator,real',
            'type' => 'required|in:Informational,PromotionalToCustomers,PromotionalAll',
        ]);

        $group = 'sms';
        $prefix = $this->prefix();

        Setting::setValue('sms_mode', $this->sms_mode, $group);
        Setting::setValue('sms_simulator_base_url', $this->sms_simulator_base_url, $group);
        Setting::setValue("{$prefix}base_url", $this->base_url, $group);
        Setting::setValue("{$prefix}api_key", $this->api_key, $group);
        Setting::setValue("{$prefix}type", $this->type, $group);
        Setting::setValue("{$prefix}sending_number", $this->sending_number, $group);

        Setting::setValue("{$prefix}otp_pattern_code", $this->otp_pattern_code, $group);

        Setting::setValue("{$prefix}verification_link_pattern_code", $this->verification_link_pattern_code, $group);
        Setting::setValue("{$prefix}verification_link_param_key", $this->verification_link_param_key, $group);

        Setting::setValue("{$prefix}order_status_param_key", $this->order_status_param_key, $group);
        Setting::setValue("{$prefix}non_customer_status_param_key", $this->non_customer_status_param_key, $group);

        Setting::setValue("{$prefix}pattern_order_searching", $this->pattern_order_searching, $group);
        Setting::setValue("{$prefix}pattern_order_courier_assigned", $this->pattern_order_courier_assigned, $group);
        Setting::setValue("{$prefix}pattern_order_waiting_pickup", $this->pattern_order_waiting_pickup, $group);
        Setting::setValue("{$prefix}pattern_order_picked_up", $this->pattern_order_picked_up, $group);
        Setting::setValue("{$prefix}pattern_order_in_transit", $this->pattern_order_in_transit, $group);
        Setting::setValue("{$prefix}pattern_order_delivered", $this->pattern_order_delivered, $group);
        Setting::setValue("{$prefix}pattern_order_cancelled", $this->pattern_order_cancelled, $group);
        Setting::setValue("{$prefix}pattern_courier_not_found", $this->pattern_courier_not_found, $group);

        Setting::setValue("{$prefix}pattern_sender_order_waiting_pickup", $this->pattern_sender_waiting_pickup, $group);
        Setting::setValue("{$prefix}pattern_sender_order_picked_up", $this->pattern_sender_picked_up, $group);
        Setting::setValue("{$prefix}pattern_sender_order_in_transit", $this->pattern_sender_in_transit, $group);
        Setting::setValue("{$prefix}pattern_sender_order_delivered", $this->pattern_sender_delivered, $group);
        Setting::setValue("{$prefix}pattern_sender_order_cancelled", $this->pattern_sender_cancelled, $group);

        Setting::setValue("{$prefix}pattern_courier_offer", $this->pattern_courier_offer, $group);
        Setting::setValue("{$prefix}courier_offer_param_key", $this->courier_offer_param_key, $group);
        Setting::setValue("{$prefix}pattern_courier_cancelled", $this->pattern_courier_cancelled, $group);
        Setting::setValue("{$prefix}courier_cancelled_param_key", $this->courier_cancelled_param_key, $group);

        Setting::setValue("{$prefix}pattern_system_cancellation", $this->pattern_system_cancellation, $group);
        Setting::setValue("{$prefix}system_cancellation_param_key", $this->system_cancellation_param_key, $group);

        Setting::setValue("{$prefix}pattern_survey_link", $this->pattern_survey_link, $group);
        Setting::setValue("{$prefix}survey_link_param_key", $this->survey_link_param_key, $group);

        Setting::setValue("{$prefix}pattern_cash_on_delivery", $this->pattern_cash_on_delivery, $group);
        Setting::setValue("{$prefix}cash_on_delivery_param_key", $this->cash_on_delivery_param_key, $group);

        app(ActivityLogger::class)->log('settings.sms_updated', auth()->user());
        $this->success('تنظیمات پیامک ذخیره شد', position: 'toast-bottom toast-end');
    }

    private function prefix(): string
    {
        return self::SETTING_PREFIX;
    }
};
