<?php

namespace App\Services\Payment\DTO;

/**
 * نتیجه‌ی مرحله‌ی ایجاد تراکنش (Request) در درگاه پرداخت.
 */
class PaymentRequestResult
{
    /**
     * @param  bool  $success  آیا ایجاد تراکنش موفق بوده است
     * @param  string|null  $authority  شناسه‌ی تراکنش برگشتی از درگاه (Authority در زرین‌پال / trackId در زیبال)
     * @param  string|null  $redirectUrl  آدرس نهایی برای هدایت کاربر به درگاه پرداخت
     * @param  int|null  $resultCode  کد نتیجه‌ی خام برگشتی از درگاه
     * @param  string|null  $message  پیام برگشتی از درگاه
     * @param  array  $raw  پاسخ خام درگاه جهت دیباگ یا لاگ
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?string $authority = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?int $resultCode = null,
        public readonly ?string $message = null,
        public readonly array $raw = [],
    ) {}
}
