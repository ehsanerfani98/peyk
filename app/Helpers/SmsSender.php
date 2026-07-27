<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Services\Sms\Exceptions\SmsSendingException;
use App\Services\Sms\IppanelSmsService;
use Illuminate\Support\Facades\Log;

/**
 * سرویس متمرکز ارسال پیامک.
 *
 * بر اساس مقدار config('sms_simulator.mode') تصمیم می‌گیرد
 * که پیامک به شبیه‌ساز محلی ارسال شود یا از طریق ippanel واقعی.
 *
 * مقادیر مجاز mode:
 *   'simulator' → Sms_Simulator_Send (تست)
 *   'real'      → IppanelSmsService::sendOtp (تولید)
 */
final class SmsSender
{
    public function __construct(
        private readonly IppanelSmsService $smsService,
    ) {}

    /**
     * ارسال پیامک با انتخاب خودکار مسیر simulator یا real.
     *
     * @param  string  $localMobile  شماره به فرمت محلی (مثال: 09120000000)
     * @param  mixed  $paramValue  مقدار پارامتر پترن
     * @param  string  $patternCode  کد پترن
     * @param  string|null  $paramKey  کلید پارامتر (فقط در حالت real استفاده می‌شود)
     */
    public function send(
        string $localMobile,
        mixed $paramValue,
        string $patternCode,
        ?string $paramKey = null,
    ): void {
        $mode = Setting::getValue('sms_mode', config('sms_simulator.mode', 'simulator'));

        if ($mode === 'real') {
            try {
                $this->smsService->sendOtp(
                    localMobile: $localMobile,
                    paramValue: (string) $paramValue,
                    patternCode: $patternCode,
                    paramKey: $paramKey,
                );
            } catch (SmsSendingException $e) {
                Log::warning('sms.real_send_failed', [
                    'mobile' => $localMobile,
                    'pattern' => $patternCode,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        } else {
            Sms_Simulator_Send(
                localMobile: $localMobile,
                paramValue: $paramValue,
                patternCode: $patternCode,
            );
        }
    }

    /**
     * بررسی فعال بودن حالت واقعی.
     */
    public function isRealMode(): bool
    {
        return Setting::getValue('sms_mode', config('sms_simulator.mode', 'simulator')) === 'real';
    }
}
