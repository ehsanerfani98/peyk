<?php

namespace App\Services\Payment\DTO;

/**
 * داده‌های لازم برای ایجاد یک تراکنش پرداخت.
 * این کلاس مستقل از درایور است و بین همه درگاه‌ها مشترک است.
 */
class PaymentRequestData
{
    /**
     * @param  int  $amount  مبلغ تراکنش (بر اساس واحد پول درگاه - تومان یا ریال)
     * @param  string  $callbackUrl  آدرس بازگشت از درگاه - می‌تواند در لحظه‌ی فراخوانی به صورت داینامیک ساخته شود
     * @param  string|null  $description  توضیحات تراکنش
     * @param  string|null  $mobile  شماره موبایل پرداخت‌کننده (اختیاری)
     * @param  string|null  $email  ایمیل پرداخت‌کننده (اختیاری - فقط زرین‌پال)
     * @param  string|null  $orderId  شناسه سفارش داخلی (اختیاری)
     * @param  array  $metadata  هرگونه اطلاعات اضافی که بخواهید همراه تراکنش ارسال شود
     */
    public function __construct(
        public readonly int $amount,
        public readonly string $callbackUrl,
        public readonly ?string $description = null,
        public readonly ?string $mobile = null,
        public readonly ?string $email = null,
        public readonly ?string $orderId = null,
        public readonly array $metadata = [],
    ) {}
}
