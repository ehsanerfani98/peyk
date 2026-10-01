<?php

namespace App\Services\Sms\DTO;

/**
 * نتیجه ارسال پیامک در سرویس مدیانا.
 *
 * ساختار پاسخ مدیانا به شکل {meta, data, pagination} است و این DTO تنها
 * بخش data را مدل‌سازی می‌کند.
 */
final readonly class SmsSendResult
{
    /**
     * @param  array<int, array<string, mixed>>  $smsItems  فهرست آیتم‌های پیامک ساخته‌شده
     */
    public function __construct(
        public bool $succeed,
        public ?int $requestId,
        public ?string $requestCode,
        public ?string $message,
        public ?string $status,
        public ?int $statusInt,
        public float $totalPrice,
        public array $smsItems = [],
        public ?string $clientRef = null,
    ) {}

    /**
     * ساخت DTO از بدنه پاسخ خام مدیانا.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function fromResponse(array $payload): self
    {
        $data = (array) data_get($payload, 'data', []);

        return new self(
            succeed: (bool) data_get($data, 'succeed', false),
            requestId: self::toInt(data_get($data, 'requestId')),
            requestCode: self::toString(data_get($data, 'requestCode')),
            message: self::toString(data_get($data, 'message')),
            status: self::toString(data_get($data, 'status')),
            statusInt: self::toInt(data_get($data, 'statusInt')),
            totalPrice: (float) data_get($data, 'totalPrice', 0),
            smsItems: (array) data_get($data, 'smsItems', []),
            clientRef: self::toString(data_get($data, 'clientRef')),
        );
    }

    /**
     * شناسه قابل استفاده برای پیگیری وضعیت تحویل.
     */
    public function trackingId(): ?string
    {
        return $this->requestCode ?? ($this->requestId !== null ? (string) $this->requestId : null);
    }

    private static function toString(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }

    private static function toInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
