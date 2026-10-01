<?php

namespace App\Services\Sms\Contracts;

use App\Services\Sms\DTO\SmsSendResult;
use App\Services\Sms\Exceptions\SmsSendingException;

/**
 * قرارداد سرویس‌دهنده پیامک.
 *
 * امکان تعویض سرویس‌دهنده (مدیانا، سرویس‌های دیگر یا پیاده‌سازی آزمایشی)
 * بدون تغییر در سرویس‌های مصرف‌کننده مانند SmsSender را فراهم می‌کند.
 */
interface SmsProvider
{
    /**
     * ارسال پیامک با پترن از پیش تعریف‌شده.
     *
     * @param  string  $localMobile  شماره گیرنده به فرمت محلی (09xxxxxxxxx)
     * @param  array<string, string|int|float>  $parameters  جفت کلید و مقدار جای‌گذارهای پترن
     * @param  string|null  $type  نوع پیام (Informational|PromotionalToCustomers|PromotionalAll)
     *
     * @throws SmsSendingException
     */
    public function sendPattern(
        string $localMobile,
        string $patternCode,
        array $parameters = [],
        ?string $type = null,
    ): SmsSendResult;

    /**
     * ارسال رمز یک‌بار مصرف.
     *
     * @param  string  $localMobile  شماره گیرنده به فرمت محلی (09xxxxxxxxx)
     *
     * @throws SmsSendingException
     */
    public function sendOtp(string $localMobile, string $otpCode, string $patternCode): SmsSendResult;

    /**
     * دریافت وضعیت تحویل یک درخواست ارسال.
     *
     * @return array{status: string|null, statusInt: int|null, smsItems: array<int, array<string, mixed>>}
     *
     * @throws SmsSendingException
     */
    public function requestStatus(string $requestId): array;
}
