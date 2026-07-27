<?php

namespace App\Services\Sms;

use App\Models\Setting;
use App\Services\Sms\Exceptions\SmsSendingException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class IppanelSmsService
{
    /**
     * ارسال پیامک با استفاده از پترن ippanel.
     *
     * این متد عمومی‌سازی‌شده و برای تمام انواع پیامک (OTP، لینک تایید، اطلاع‌رسانی وضعیت،
     * لینک نظرسنجی و ...) استفاده می‌شود. پترن و کلید پارامتر توسط فراخواننده تعیین می‌شود.
     *
     * @param  string  $localMobile  شماره به فرمت محلی (مثال: 09120000000)
     * @param  string  $paramValue  مقدار پارامتر پترن (کد OTP، لینک، متن وضعیت و ...)
     * @param  string|null  $patternCode  کد پترن ippanel. در صورت null از setting/config مقداردهی می‌شود.
     * @param  string|null  $paramKey  نام کلید پارامتر در پترن. در صورت null از setting/config مقداردهی می‌شود.
     *
     * @throws SmsSendingException
     */
    public function sendOtp(
        string $localMobile,
        string $paramValue,
        ?string $patternCode = null,
        ?string $paramKey = null,
    ): void {
        $recipient = $this->toE164($localMobile);

        $code = $patternCode ?? Setting::getValue('ippanel.otp_pattern_code', config('ippanel.otp_pattern_code'));
        $key = $paramKey ?? Setting::getValue('ippanel.otp_param_key', config('ippanel.otp_param_key', 'code'));

        $response = Http::baseUrl(Setting::getValue('ippanel.base_url', config('ippanel.base_url')))
            ->withHeaders([
                'Authorization' => Setting::getValue('ippanel.token', config('ippanel.token')),
                'Content-Type' => 'application/json',
            ])
            ->post('/api/send', [
                'sending_type' => 'pattern',
                'from_number' => Setting::getValue('ippanel.from_number', config('ippanel.from_number')),
                'code' => $code,
                'recipients' => [$recipient],
                'params' => [
                    $key => $paramValue,
                ],
            ]);

        $payload = $response->json();
        $ok = $response->successful() && (bool) data_get($payload, 'meta.status');

        if (! $ok) {
            Log::warning('ippanel.sms_send_failed', [
                'mobile' => $recipient,
                'pattern_code' => $code,
                'http_status' => $response->status(),
                'body' => $payload,
            ]);

            throw new SmsSendingException(
                data_get($payload, 'meta.message', 'ارسال پیامک با خطا مواجه شد.')
            );
        }
    }

    /**
     * تبدیل شماره محلی (09xxxxxxxxx) به فرمت E.164 مورد نیاز ippanel (+98xxxxxxxxx).
     */
    private function toE164(string $localMobile): string
    {
        return '+98'.substr($localMobile, 1);
    }
}
