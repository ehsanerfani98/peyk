<?php

namespace App\Services\Auth;

use App\Helpers\SmsSender;
use App\Models\Setting;
use App\Services\Auth\Exceptions\OtpException;
use App\Services\Sms\Exceptions\SmsSendingException;
use Illuminate\Support\Facades\Cache;

final class OtpService
{
    public function __construct(
        private readonly SmsSender $smsSender,
    ) {}

    /**
     * تولید کد تایید، ذخیره در کش و ارسال پیامک.
     *
     * @throws OtpException
     * @throws SmsSendingException
     */
    public function issue(string $mobile): void
    {
        $this->guardResendThrottle($mobile);

        $code = $this->generateCode();

        Cache::put(
            $this->codeKey($mobile),
            ['code' => $code, 'attempts' => 0],
            now()->addSeconds((int) Setting::getValue('otp.expire_seconds', config('otp.expire_seconds'))),
        );

        Cache::put(
            $this->throttleKey($mobile),
            true,
            now()->addSeconds((int) Setting::getValue('otp.resend_seconds', config('otp.resend_seconds'))),
        );
        $this->smsSender->send(
            localMobile: $mobile,
            paramValue: $code,
            patternCode: 'x7km2n9p4qrst',
            paramKey: Setting::getValue('ippanel.otp_param_key', config('ippanel.otp_param_key', 'code')),
        );
    }

    /**
     * اعتبارسنجی کد تایید وارد شده توسط کاربر.
     * در صورت موفقیت، کد از کش پاک می‌شود.
     *
     * @throws OtpException
     */
    public function verify(string $mobile, string $code): void
    {
        $key = $this->codeKey($mobile);
        $data = Cache::get($key);

        if (! $data) {
            throw OtpException::expiredOrNotFound();
        }

        if ($data['attempts'] >= (int) Setting::getValue('otp.max_attempts', config('otp.max_attempts'))) {
            Cache::forget($key);
            throw OtpException::tooManyAttempts();
        }

        if (! hash_equals((string) $data['code'], $code)) {
            $data['attempts']++;
            Cache::put($key, $data, now()->addSeconds((int) Setting::getValue('otp.expire_seconds', config('otp.expire_seconds'))));
            throw OtpException::invalidCode();
        }

        Cache::forget($key);
        Cache::forget($this->throttleKey($mobile));
    }

    /**
     * @throws OtpException
     */
    private function guardResendThrottle(string $mobile): void
    {
        if (Cache::has($this->throttleKey($mobile))) {
            throw OtpException::tooManyRequests();
        }
    }

    private function generateCode(): string
    {
        $length = (int) Setting::getValue('otp.length', config('otp.length'));
        $min = (int) str_pad('1', $length, '0');
        $max = (int) str_pad('', $length, '9');

        return (string) random_int($min, $max);
    }

    private function codeKey(string $mobile): string
    {
        return "otp:code:{$mobile}";
    }

    private function throttleKey(string $mobile): string
    {
        return "otp:throttle:{$mobile}";
    }
}
