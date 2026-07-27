<?php

namespace App\Services\Payment\DTO;

/**
 * نتیجه‌ی مرحله‌ی تایید (Verify) تراکنش در درگاه پرداخت.
 */
class PaymentVerifyResult
{
    /**
     * @param  bool  $success  آیا پرداخت با موفقیت تایید شده است
     * @param  int|null  $amount  مبلغ تایید شده تراکنش
     * @param  string|null  $refId  شماره پیگیری / مرجع تراکنش (RefID در زرین‌پال / refNumber در زیبال)
     * @param  string|null  $cardNumber  شماره کارت پرداخت‌کننده (در صورت وجود، به صورت ماسک شده)
     * @param  int|null  $resultCode  کد نتیجه‌ی خام برگشتی از درگاه
     * @param  string|null  $message  پیام برگشتی از درگاه
     * @param  bool  $alreadyVerified  آیا تراکنش قبلا تایید شده بوده است
     * @param  array  $raw  پاسخ خام درگاه جهت دیباگ یا لاگ
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?int $amount = null,
        public readonly ?string $refId = null,
        public readonly ?string $cardNumber = null,
        public readonly ?int $resultCode = null,
        public readonly ?string $message = null,
        public readonly bool $alreadyVerified = false,
        public readonly array $raw = [],
    ) {}
}
