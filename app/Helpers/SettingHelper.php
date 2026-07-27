<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * خواندن مقدار یک تنظیم از دیتابیس با فال‌بک به config (که از env می‌خواند).
 *
 * این تابع ابتدا مقدار را از جدول settings می‌خواند.
 * اگر در دیتابیس نبود، از config fallback استفاده می‌کند.
 * نتیجه برای ۶۰ ثانیه کش می‌شود.
 *
 * @param  string  $key  کلید تنظیم در دیتابیس
 * @param  mixed  $default  مقدار پیش‌فرض (اگر در دیتابیس و config نباشد)
 */
if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 60, function () use ($key, $default) {
            $dbValue = Setting::getValue($key);

            if ($dbValue !== null && $dbValue !== '') {
                return $dbValue;
            }

            return $default;
        });
    }
}

/**
 * بازنشانی کش یک تنظیم خاص.
 */
if (! function_exists('forget_setting_cache')) {
    function forget_setting_cache(string $key): void
    {
        Cache::forget("setting.{$key}");
    }
}
