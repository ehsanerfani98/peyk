<?php

namespace App\Services\Payment\Contracts;

use App\Services\Payment\DTO\PaymentRequestData;
use App\Services\Payment\DTO\PaymentRequestResult;
use App\Services\Payment\DTO\PaymentVerifyResult;

interface PaymentGatewayInterface
{
    /**
     * ایجاد یک تراکنش جدید در درگاه پرداخت.
     */
    public function request(PaymentRequestData $data): PaymentRequestResult;

    /**
     * ساخت آدرس نهایی هدایت کاربر به صفحه‌ی پرداخت درگاه بر اساس authority/trackId.
     */
    public function getRedirectUrl(string $authority): string;

    /**
     * تایید (Verify) یک تراکنش پس از بازگشت کاربر از درگاه.
     *
     * @param  string  $authority  شناسه‌ی تراکنش (Authority در زرین‌پال / trackId در زیبال)
     * @param  int|null  $amount  مبلغ تراکنش جهت اعتبارسنجی (در زرین‌پال الزامی است)
     */
    public function verify(string $authority, ?int $amount = null): PaymentVerifyResult;

    /**
     * نام درایور جهت لاگ‌گیری/شناسایی.
     */
    public function getName(): string;
}
