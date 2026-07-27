<?php

namespace App\Services\Auth;

final class MobileNumberNormalizer
{
    /**
     * تبدیل هر فرمت ورودی شماره موبایل ایران به فرمت محلی یکسان: 09xxxxxxxxx
     * ورودی‌های پشتیبانی‌شده: 09xxxxxxxxx / +989xxxxxxxxx / 00989xxxxxxxxx / 989xxxxxxxxx
     */
    public static function toLocal(string $mobile): string
    {
        $mobile = preg_replace('/\s+/', '', $mobile) ?? $mobile;

        if (str_starts_with($mobile, '+98')) {
            return '0'.substr($mobile, 3);
        }

        if (str_starts_with($mobile, '0098')) {
            return '0'.substr($mobile, 4);
        }

        if (str_starts_with($mobile, '98') && strlen($mobile) === 12) {
            return '0'.substr($mobile, 2);
        }

        return $mobile;
    }
}
