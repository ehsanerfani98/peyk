<?php

namespace App\Services\Payment\Exceptions;

use Exception;
use Throwable;

class PaymentException extends Exception
{
    /**
     * @param  string  $message  پیام خطا
     * @param  int|null  $resultCode  کد نتیجه‌ی خام برگشتی از درگاه
     * @param  array  $raw  پاسخ خام درگاه جهت دیباگ یا لاگ
     */
    public function __construct(
        string $message,
        public readonly ?int $resultCode = null,
        public readonly array $raw = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
