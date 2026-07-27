<?php

namespace App\Services\Order\Exceptions;

use Exception;

final class OrderStateException extends Exception
{
    public function __construct(
        string $message,
        private readonly string $errorCode,
        private readonly int $statusCode = 422,
    ) {
        parent::__construct($message);
    }

    public static function invalidTransition(string $message): self
    {
        return new self($message, 'order_invalid_state', 422);
    }

    public static function forbidden(string $message): self
    {
        return new self($message, 'order_forbidden', 403);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
