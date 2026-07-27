<?php

namespace App\Services\Auth\Exceptions;

use Exception;

final class OtpException extends Exception
{
    public function __construct(
        string $message,
        private readonly string $errorCode,
    ) {
        parent::__construct($message);
    }

    public static function tooManyRequests(): self
    {
        return new self(
            'درخواست بیش از حد مجاز است. لطفا کمی صبر کنید و دوباره تلاش کنید.',
            'otp_too_many_requests',
        );
    }

    public static function expiredOrNotFound(): self
    {
        return new self(
            'کد تایید یافت نشد یا منقضی شده است. دوباره درخواست دهید.',
            'otp_expired',
        );
    }

    public static function tooManyAttempts(): self
    {
        return new self(
            'تعداد تلاش‌های مجاز به پایان رسید. دوباره درخواست کد تایید دهید.',
            'otp_too_many_attempts',
        );
    }

    public static function invalidCode(): self
    {
        return new self(
            'کد تایید نادرست است.',
            'otp_invalid_code',
        );
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
