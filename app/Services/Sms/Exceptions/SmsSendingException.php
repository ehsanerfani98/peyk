<?php

namespace App\Services\Sms\Exceptions;

use Exception;

final class SmsSendingException extends Exception
{
    private const DEFAULT_MESSAGE = 'ارسال پیامک با خطا مواجه شد.';

    public function __construct(
        string $message = self::DEFAULT_MESSAGE,
        private readonly ?string $providerErrorCode = null,
        private readonly ?int $httpStatus = null,
    ) {
        parent::__construct($message);
    }

    /**
     * ساخت استثنا از پاسخ خطای سرویس‌دهنده.
     *
     * در صورت وجود پیام سرویس‌دهنده همان پیام استفاده می‌شود، در غیر این صورت
     * کد خطای عددی به پیام فارسی نگاشت می‌گردد.
     */
    public static function fromProviderResponse(
        ?string $errorCode = null,
        ?string $errorMessage = null,
        ?int $httpStatus = null,
    ): self {
        $message = $errorMessage !== null && $errorMessage !== ''
            ? $errorMessage
            : (self::mapProviderMessage($errorCode) ?? self::DEFAULT_MESSAGE);

        return new self($message, $errorCode, $httpStatus);
    }

    /**
     * کد خطای عددی سرویس‌دهنده (در صورت وجود).
     */
    public function providerErrorCode(): ?string
    {
        return $this->providerErrorCode;
    }

    /**
     * کد وضعیت HTTP پاسخ سرویس‌دهنده (در صورت وجود).
     */
    public function httpStatus(): ?int
    {
        return $this->httpStatus;
    }

    /**
     * نگاشت کدهای خطای عددی مستندشده مدیانا به پیام فارسی.
     */
    private static function mapProviderMessage(?string $errorCode): ?string
    {
        return match ($errorCode) {
            '1021' => 'خطای ناشناخته‌ای در سرویس پیامک رخ داده است.',
            '1032' => 'برنامه فعالی در سرویس پیامک یافت نشد.',
            '1033' => 'برنامه فعال سرویس پیامک قابلیت API ندارد.',
            '1034' => 'برنامه فعال سرویس پیامک قابلیت الگو ندارد.',
            '1035' => 'برنامه فعال سرویس پیامک خط اختصاصی ندارد.',
            '1041' => 'شماره گیرنده نامعتبر است.',
            '1042' => 'موجودی کیف پول پیامک کافی نیست.',
            '1043' => 'حداکثر تعداد دریافت‌کنندگان مجاز تجاوز شده است.',
            '1044' => 'شناسه پیامک نامعتبر است.',
            '1045' => 'کد درخواست نامعتبر است.',
            '1046' => 'پارامترهای ورودی سرویس پیامک نامعتبر هستند.',
            '1047' => 'شماره تلفن گیرنده در لیست سیاه قرار دارد.',
            '1051' => 'کمپین پیامکی منقضی شده است.',
            '1061' => 'خط ارسال فعالی یافت نشد.',
            '1062' => 'خط ارسال در این زمان از روز قابل استفاده نیست.',
            '1071' => 'URL در متن الگو شناسایی شد.',
            '1072' => 'الگوی پیامک توسط مدیر رد شده است.',
            '1073' => 'الگو متعلق به شماره ارسال دیگری است.',
            '1074' => 'متن پیام خالی است.',
            '1075' => 'درخواست پیامک یافت نشد.',
            '1076' => 'الگوی پیامک خالی است.',
            '1093' => 'دریافت‌کنندگان یافت نشدند.',
            '1101' => 'شماره ارسال یافت نشد.',
            '1102' => 'شماره ارسال منقضی شده است.',
            default => null,
        };
    }
}
