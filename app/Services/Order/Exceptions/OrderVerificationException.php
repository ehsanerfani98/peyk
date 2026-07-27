<?php

namespace App\Services\Order\Exceptions;

use Exception;

final class OrderVerificationException extends Exception
{
    public function __construct(
        string $message,
        private readonly string $errorCode,
    ) {
        parent::__construct($message);
    }

    public static function notFound(): self
    {
        return new self(
            'لینک تایید معتبر نیست.',
            'order_verification_not_found',
        );
    }

    public static function expired(): self
    {
        return new self(
            'کد/لینک تایید منقضی شده است. لطفا با فرستنده/گیرنده سفارش تماس بگیرید.',
            'order_verification_expired',
        );
    }

    public static function locked(): self
    {
        return new self(
            'امکان تایید این مرحله وجود ندارد. لطفا با پشتیبانی تماس بگیرید.',
            'order_verification_locked',
        );
    }

    public static function invalidCode(): self
    {
        return new self(
            'کد وارد شده صحیح نیست.',
            'order_verification_invalid_code',
        );
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
