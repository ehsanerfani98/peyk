<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Services\Sms\Contracts\SmsProvider;
use App\Services\Sms\DTO\SmsSendResult;
use App\Services\Sms\Exceptions\SmsSendingException;
use Illuminate\Support\Facades\Log;

/**
 * سرویس متمرکز ارسال پیامک.
 *
 * بر اساس مقدار setting/config با کلید sms_mode تصمیم می‌گیرد که پیامک به
 * شبیه‌ساز محلی ارسال شود یا از طریق سرویس‌دهنده واقعی (مدیانا).
 *
 * مقادیر مجاز mode:
 *   'simulator' → Sms_Simulator_Send (تست)
 *   'real'      → SmsProvider (تولید)
 */
final class SmsSender
{
    public function __construct(
        private readonly SmsProvider $provider,
    ) {}

    /**
     * ارسال پیامک تک‌پارامتری (سازگار با فراخوانی‌های موجود).
     *
     * @param  string  $localMobile  شماره به فرمت محلی (مثال: 09120000000)
     * @param  mixed  $paramValue  مقدار پارامتر پترن
     * @param  string  $patternCode  کد پترن
     * @param  string|null  $paramKey  کلید پارامتر در پترن
     *
     * @throws SmsSendingException
     */
    public function send(
        string $localMobile,
        mixed $paramValue,
        string $patternCode,
        ?string $paramKey = null,
    ): ?SmsSendResult {
        $key = $paramKey ?? (string) Setting::getValue(
            'ippanel.default_param_key',
            config('mediana.default_param_key', 'code'),
        );

        return $this->sendPattern(
            localMobile: $localMobile,
            patternCode: $patternCode,
            parameters: [$key => is_scalar($paramValue) ? (string) $paramValue : ''],
        );
    }

    /**
     * ارسال پیامک پترنی با یک یا چند پارامتر.
     *
     * @param  array<string, string|int|float>  $parameters  جفت کلید و مقدار جای‌گذارهای پترن
     * @param  string|null  $type  نوع پیام؛ در صورت null از تنظیمات خوانده می‌شود
     *
     * @throws SmsSendingException
     */
    public function sendPattern(
        string $localMobile,
        string $patternCode,
        array $parameters = [],
        ?string $type = null,
    ): ?SmsSendResult {
        if (! $this->isRealMode()) {
            // مقادیر پارامترها (ممکن است شامل توکن/لینک باشد) لاگ نمی‌شوند
            Log::info('sms.dispatch_simulator', [
                'mobile' => $localMobile,
                'template_key' => $patternCode,
            ]);

            $this->storeInSimulator(
                localMobile: $localMobile,
                paramValue: $this->simulatorValue($parameters),
                patternCode: $patternCode,
            );

            return null;
        }

        try {
            $result = $this->provider->sendPattern($localMobile, $patternCode, $parameters, $type);

            // ---- لاگ تشخیصی موقت: شاخه سرویس‌دهنده واقعی انتخاب شد ----
            Log::info('sms.dispatch_real', [
                'mobile' => $localMobile,
                'pattern_code' => $patternCode,
                'succeed' => $result->succeed,
                'tracking_id' => $result->trackingId(),
            ]);

            return $result;
        } catch (SmsSendingException $e) {
            Log::warning('sms.real_send_failed', [
                'mobile' => $localMobile,
                'pattern' => $patternCode,
                'error_code' => $e->providerErrorCode(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * ارسال رمز یک‌بار مصرف از طریق اندپوینت اختصاصی OTP.
     *
     * @throws SmsSendingException
     */
    public function sendOtp(string $localMobile, string $otpCode, string $patternCode): ?SmsSendResult
    {
        if (! $this->isRealMode()) {
            $this->storeInSimulator(
                localMobile: $localMobile,
                paramValue: $otpCode,
                patternCode: $patternCode,
            );

            return null;
        }

        try {
            return $this->provider->sendOtp($localMobile, $otpCode, $patternCode);
        } catch (SmsSendingException $e) {
            Log::warning('sms.real_send_failed', [
                'mobile' => $localMobile,
                'pattern' => $patternCode,
                'error_code' => $e->providerErrorCode(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * بررسی فعال بودن حالت واقعی.
     */
    public function isRealMode(): bool
    {
        return Setting::getValue('sms_mode', config('sms_simulator.mode', 'simulator')) === 'real';
    }

    /**
     * رزولور کد پترن بر اساس کلید منطقی.
     *
     * در حالت شبیه‌ساز، خودِ کلید منطقی برگردانده می‌شود تا شبیه‌ساز کاملاً
     * مستقل از کدهای پترن واقعی ذخیره‌شده در دیتابیس کار کند. در حالت واقعی،
     * کد پترن از setting (DB) با فال‌بک به config (env) خوانده می‌شود و در صورت
     * خالی بودن، null برگردانده می‌شود تا فراخوان‌کننده از ارسال صرف‌نظر کند.
     */
    public function resolvePatternCode(string $logicalKey): ?string
    {
        if (! $this->isRealMode()) {
            return $logicalKey;
        }

        $value = Setting::getValue("ippanel.{$logicalKey}", config("mediana.{$logicalKey}"));

        return is_scalar($value) && $value !== '' ? (string) $value : null;
    }

    /**
     * ثبت پیامک در شبیه‌ساز محلی با تضمین اینکه رکورد واقعاً ذخیره شده است.
     *
     * درج ناموفق در جدول `sms_messages` (مثلاً به دلیل مشکل دیتابیس) نباید بی‌صدا
     * نادیده گرفته شود؛ در آن صورت همان رفتار حالت واقعی اعمال می‌شود: استثنا پرتاب
     * می‌شود تا فراخوان‌کننده رزرو پیامک (مثل courier_offer_sms_sent_at) را آزاد کند.
     *
     * @throws SmsSendingException
     */
    private function storeInSimulator(string $localMobile, mixed $paramValue, string $patternCode): void
    {
        if (Sms_Simulator_Send(
            localMobile: $localMobile,
            paramValue: $paramValue,
            templateKey: $patternCode,
        )) {
            return;
        }

        throw new SmsSendingException('ثبت پیامک در شبیه‌ساز پیامک ناموفق بود.');
    }

    /**
     * تبدیل پارامترهای پترن به یک مقدار قابل نمایش در شبیه‌ساز.
     *
     * @param  array<string, string|int|float>  $parameters
     */
    private function simulatorValue(array $parameters): string
    {
        if ($parameters === []) {
            return '';
        }

        if (count($parameters) === 1) {
            return (string) reset($parameters);
        }

        return implode(' | ', array_map(
            static fn (string|int|float $value): string => (string) $value,
            $parameters,
        ));
    }
}
