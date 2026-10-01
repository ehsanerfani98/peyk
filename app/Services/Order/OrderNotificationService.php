<?php

namespace App\Services\Order;

use App\Helpers\SmsSender;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Sms\Exceptions\SmsSendingException;
use Illuminate\Support\Facades\Log;

/**
 * سرویس متمرکز اطلاع‌رسانی پیامکی برای تغییرات وضعیت سفارش.
 *
 * تمام تنظیمات از طریق تابع setting() خوانده می‌شوند که ابتدا دیتابیس
 * و سپس config (env) را چک می‌کند. ادمین می‌تواند همه مقادیر را از پنل مدیریت تنظیم کند.
 */
final class OrderNotificationService
{
    /**
     * نقشه وضعیت → کلید تنظیم برای کد پترن پیامک اطلاع‌رسانی به مشتری.
     */
    private const CUSTOMER_STATUS_PATTERNS = [
        'SEARCHING_COURIER' => 'pattern_order_searching',
        'COURIER_NOT_FOUND' => 'pattern_courier_not_found',
        'COURIER_ASSIGNED' => 'pattern_order_courier_assigned',
        'WAITING_PICKUP' => 'pattern_order_waiting_pickup',
        'PICKED_UP' => 'pattern_order_picked_up',
        'IN_TRANSIT' => 'pattern_order_in_transit',
        'DELIVERED' => 'pattern_order_delivered',
        'CANCELLED' => 'pattern_order_cancelled',
    ];

    /**
     * وضعیت‌هایی که باید به فرستنده/گیرنده غیرمشتری اطلاع‌رسانی شود.
     */
    private const NON_CUSTOMER_STATUS_PATTERNS = [
        'WAITING_PICKUP' => 'pattern_sender_order_waiting_pickup',
        'PICKED_UP' => 'pattern_sender_order_picked_up',
        'IN_TRANSIT' => 'pattern_sender_order_in_transit',
        'DELIVERED' => 'pattern_sender_order_delivered',
        'CANCELLED' => 'pattern_sender_order_cancelled',
    ];

    public function __construct(
        private readonly SmsSender $smsSender,
    ) {}

    /**
     * خواندن کد پترن از طریق رزولور مرکزی SmsSender.
     *
     * در حالت شبیه‌ساز کلید منطقی برگردانده می‌شود (بدون وابستگی به کد واقعی)؛
     * در حالت واقعی کد پترن از setting (DB) با فال‌بک به config (env) خوانده می‌شود
     * و در صورت خالی بودن null برمی‌گردد تا گارد فراخوان‌کننده فعال شود.
     */
    private function getPatternCode(string $key): ?string
    {
        return $this->smsSender->resolvePatternCode($key);
    }

    /**
     * خواندن کلید پارامتر از setting با فال‌بک به config.
     */
    private function getParamKey(string $key, string $default = 'code'): string
    {
        $value = Setting::getValue("ippanel.{$key}", config("mediana.{$key}", $default));

        return is_scalar($value) && $value !== '' ? (string) $value : $default;
    }

    // ---------------------------------------------------------------
    // ۱. اطلاع‌رسانی تغییر وضعیت به مشتری
    // ---------------------------------------------------------------

    public function notifyCustomerStatusChange(Order $order, string $newStatus): void
    {
        if ($order->sender_is_customer || $order->receiver_is_customer) {
            return;
        }

        $patternKey = self::CUSTOMER_STATUS_PATTERNS[$newStatus] ?? null;

        if (! $patternKey) {
            return;
        }

        $patternCode = $this->getPatternCode($patternKey);
        $paramKey = $this->getParamKey('order_status_param_key');

        if (! $patternCode) {
            Log::info('sms.pattern_not_configured', ['key' => $patternKey]);

            return;
        }

        try {
            $this->smsSender->send(
                localMobile: $order->customer?->mobile ?? '',
                paramValue: $order->id,
                patternCode: $patternCode,
                paramKey: $paramKey,
            );
        } catch (SmsSendingException $e) {
            Log::warning('sms.customer_status_notification_failed', [
                'order_id' => $order->id,
                'status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ---------------------------------------------------------------
    // ۲. اطلاع‌رسانی تغییر وضعیت به فرستنده/گیرنده غیرمشتری
    // ---------------------------------------------------------------

    public function notifySenderStatusChange(Order $order, string $newStatus): void
    {
        if ($order->sender_is_customer) {
            return;
        }

        $patternKey = self::NON_CUSTOMER_STATUS_PATTERNS[$newStatus] ?? null;

        if (! $patternKey) {
            return;
        }

        $patternCode = $this->getPatternCode($patternKey);
        $paramKey = $this->getParamKey('non_customer_status_param_key');

        if (! $patternCode) {
            Log::info('sms.pattern_not_configured', ['key' => $patternKey]);

            return;
        }

        try {
            $this->smsSender->send(
                localMobile: $order->sender_mobile,
                paramValue: $order->id,
                patternCode: $patternCode,
                paramKey: $paramKey,
            );
        } catch (SmsSendingException $e) {
            Log::warning('sms.sender_status_notification_failed', [
                'order_id' => $order->id,
                'status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notifyReceiverStatusChange(Order $order, string $newStatus): void
    {
        if ($order->receiver_is_customer) {
            return;
        }

        $patternKey = self::NON_CUSTOMER_STATUS_PATTERNS[$newStatus] ?? null;

        if (! $patternKey) {
            return;
        }

        $patternCode = $this->getPatternCode($patternKey);
        $paramKey = $this->getParamKey('non_customer_status_param_key');

        if (! $patternCode) {
            Log::info('sms.pattern_not_configured', ['key' => $patternKey]);

            return;
        }

        try {
            $this->smsSender->send(
                localMobile: $order->receiver_mobile,
                paramValue: $order->id,
                patternCode: $patternCode,
                paramKey: $paramKey,
            );
        } catch (SmsSendingException $e) {
            Log::warning('sms.receiver_status_notification_failed', [
                'order_id' => $order->id,
                'status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ---------------------------------------------------------------
    // ۳. اطلاع‌رسانی لغو به پیک
    // ---------------------------------------------------------------

    public function notifyCourierCancellation(Order $order): void
    {
        if (! $order->courier_id || ! $order->courier) {
            return;
        }

        $patternCode = $this->getPatternCode('pattern_courier_cancelled');

        if (! $patternCode) {
            Log::info('sms.pattern_not_configured', ['key' => 'pattern_courier_cancelled']);

            return;
        }

        $paramKey = $this->getParamKey('courier_cancelled_param_key');

        try {
            $this->smsSender->send(
                localMobile: $order->courier->mobile,
                paramValue: $order->id,
                patternCode: $patternCode,
                paramKey: $paramKey,
            );
        } catch (SmsSendingException $e) {
            Log::warning('sms.courier_cancellation_notification_failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ---------------------------------------------------------------
    // ۴. اطلاع‌رسانی لغو سیستم (تایم‌اوت) به مشتری
    // ---------------------------------------------------------------

    public function notifyCustomerSystemCancellation(Order $order): void
    {
        $patternCode = $this->getPatternCode('pattern_system_cancellation');

        if (! $patternCode) {
            Log::info('sms.pattern_not_configured', ['key' => 'pattern_system_cancellation']);

            return;
        }

        $paramKey = $this->getParamKey('system_cancellation_param_key');

        try {
            $this->smsSender->send(
                localMobile: $order->customer?->mobile,
                paramValue: $order->id,
                patternCode: $patternCode,
                paramKey: $paramKey,
            );
        } catch (SmsSendingException $e) {
            Log::warning('sms.system_cancellation_notification_failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ---------------------------------------------------------------
    // ۵. اطلاع‌رسانی پرداخت نقدی (لینک پیگیری سفارش)
    // ---------------------------------------------------------------

    public function sendCashOnDeliverySms(Order $order, string $mobile, string $paymentUrl): void
    {
        $patternCode = $this->getPatternCode('pattern_cash_on_delivery');
        $paramKey = $this->getParamKey('cash_on_delivery_param_key');

        if (! $patternCode) {
            Log::info('sms.pattern_not_configured', ['key' => 'pattern_cash_on_delivery']);

            return;
        }

        try {
            $this->smsSender->send(
                localMobile: $mobile,
                paramValue: $paymentUrl,
                patternCode: $patternCode,
                paramKey: $paramKey,
            );
        } catch (SmsSendingException $e) {
            Log::warning('sms.cash_on_delivery_failed', [
                'order_id' => $order->id,
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ---------------------------------------------------------------
    // ۶. اطلاع‌رسانی لینک نظرسنجی به فرستنده/گیرنده غیرمشتری
    // ---------------------------------------------------------------

    public function sendSurveyLinkToSender(Order $order, string $surveyLink): void
    {
        if ($order->sender_is_customer) {
            return;
        }

        $patternCode = $this->getPatternCode('pattern_survey_link');

        if (! $patternCode) {
            Log::info('sms.pattern_not_configured', ['key' => 'pattern_survey_link']);

            return;
        }

        $paramKey = $this->getParamKey('survey_link_param_key');

        try {
            $this->smsSender->send(
                localMobile: $order->sender_mobile,
                paramValue: $surveyLink,
                patternCode: $patternCode,
                paramKey: $paramKey,
            );
        } catch (SmsSendingException $e) {
            Log::warning('sms.sender_survey_link_failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendSurveyLinkToReceiver(Order $order, string $surveyLink): void
    {
        if ($order->receiver_is_customer) {
            return;
        }

        $patternCode = $this->getPatternCode('pattern_survey_link');

        if (! $patternCode) {
            Log::info('sms.pattern_not_configured', ['key' => 'pattern_survey_link']);

            return;
        }

        $paramKey = $this->getParamKey('survey_link_param_key');

        try {
            $this->smsSender->send(
                localMobile: $order->receiver_mobile,
                paramValue: $surveyLink,
                patternCode: $patternCode,
                paramKey: $paramKey,
            );
        } catch (SmsSendingException $e) {
            Log::warning('sms.receiver_survey_link_failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ---------------------------------------------------------------
    // ۷. اطلاع‌رسانی لینک پیشنهاد سفارش به پیک (فقط یک‌بار برای هر پیشنهاد)
    // ---------------------------------------------------------------

    /**
     * ارسال پیامک لینک تایید پیشنهاد سفارش به پیک.
     *
     * لینک از نوع GET است و مستقیماً در مرورگر باز می‌شود؛ پیک با کلیک روی لینک،
     * پیشنهاد را می‌پذیرد (اندپوینت POST اپ موبایل برای لینک پیامکی مناسب نبود).
     *
     * برای هر پیشنهاد فقط یک‌بار پیامک ارسال می‌شود: ستون courier_offer_sms_sent_at
     * با یک کوئری شرطی (اتمی) رزرو می‌شود؛ اگر پیشنهاد در این فاصله عوض شده باشد
     * یا پیامک قبلاً ارسال شده باشد، ارسال تکرار نمی‌شود.
     */
    public function sendCourierOfferLink(Order $order): void
    {
        $order->loadMissing('courier');

        $mode = $this->smsSender->isRealMode() ? 'real' : 'simulator';
        $paramKey = $this->getParamKey('courier_offer_param_key');

        // ---- لاگ تشخیصی موقت: ورود به مسیر ارسال پیامک پیشنهاد پیک ----
        Log::info('sms.courier_offer_attempt', [
            'order_id' => $order->id,
            'status' => $order->status,
            'mode' => $mode,
            'courier_id' => $order->courier_id,
            'courier_mobile' => $order->courier?->mobile,
            'has_token' => (bool) $order->courier_offer_token,
            'sms_sent_at' => $order->courier_offer_sms_sent_at?->toIso8601String(),
            'param_key' => $paramKey,
            'raw_pattern_setting' => (string) Setting::getValue('ippanel.pattern_courier_offer', ''),
            'config_pattern' => (string) config('mediana.pattern_courier_offer', ''),
        ]);

        $patternCode = $this->getPatternCode('pattern_courier_offer');

        if (! $patternCode) {
            Log::info('sms.pattern_not_configured', [
                'key' => 'pattern_courier_offer',
                'order_id' => $order->id,
                'mode' => $mode,
            ]);

            return;
        }

        if (! $order->courier_offer_token || ! $order->courier) {
            Log::warning('sms.courier_offer_link_not_available', [
                'order_id' => $order->id,
                'mode' => $mode,
                'has_token' => (bool) $order->courier_offer_token,
                'has_courier' => (bool) $order->courier,
            ]);

            return;
        }

        if (! $this->claimCourierOfferSms($order)) {
            Log::info('sms.courier_offer_already_sent', [
                'order_id' => $order->id,
                'mode' => $mode,
                'sms_sent_at' => $order->fresh()?->courier_offer_sms_sent_at?->toIso8601String(),
            ]);

            return;
        }

        $offerUrl = route('courier.offer', ['token' => $order->courier_offer_token]);

        try {
            $result = $this->smsSender->send(
                localMobile: (string) $order->courier->mobile,
                paramValue: $offerUrl,
                patternCode: $patternCode,
                paramKey: $paramKey,
            );

            // ---- لاگ تشخیصی موقت: پیامک (واقعی/شبیه‌ساز) بدون استثنا پردازش شد ----
            Log::info('sms.courier_offer_dispatched', [
                'order_id' => $order->id,
                'mode' => $mode,
                'receiver' => $order->courier->mobile,
                'pattern_code' => $patternCode,
                'offer_url' => $offerUrl,
                'provider_tracking_id' => $result?->trackingId(),
            ]);
        } catch (SmsSendingException $e) {
            // ارسال ناموفق: رزرو آزاد می‌شود تا برای همین پیشنهاد امکان تلاش مجدد بماند
            $this->releaseCourierOfferSmsClaim($order);

            Log::warning('sms.courier_offer_failed', [
                'order_id' => $order->id,
                'mode' => $mode,
                'courier_id' => $order->courier_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * رزرو اتمی ارسال پیامک برای پیشنهاد فعلی.
     *
     * true یعنی همین فراخوانی مسئول ارسال است (و پیامک تکراری ارسال نمی‌شود).
     */
    private function claimCourierOfferSms(Order $order): bool
    {
        $claimed = Order::query()
            ->whereKey($order->getKey())
            ->where('courier_offer_token', $order->courier_offer_token)
            ->whereNull('courier_offer_sms_sent_at')
            ->update(['courier_offer_sms_sent_at' => now()]);

        return $claimed === 1;
    }

    /**
     * آزادسازی رزرو ارسال پیامک - فقط برای همان پیشنهادی که رزرو شده بود.
     */
    private function releaseCourierOfferSmsClaim(Order $order): void
    {
        Order::query()
            ->whereKey($order->getKey())
            ->where('courier_offer_token', $order->courier_offer_token)
            ->update(['courier_offer_sms_sent_at' => null]);
    }
}
