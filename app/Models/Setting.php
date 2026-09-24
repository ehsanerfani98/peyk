<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class Setting extends Model
{
    /**
     * مدت زمان کش مقادیر تنظیمات (ثانیه).
     */
    private const CACHE_TTL_SECONDS = 3600;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Get a setting value by key.
     *
     * مقدار از کش خوانده می‌شود و در صورت نبود، از دیتابیس واکشی و کش می‌شود.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember(
            self::cacheKey($key),
            self::CACHE_TTL_SECONDS,
            fn (): mixed => self::where('key', $key)->value('value'),
        );

        return $value ?? $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, mixed $value, string $group = 'general'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'group' => $group]
        );

        self::forgetCache($key);
    }

    /**
     * Get all settings for a group as key-value pairs.
     */
    public static function getGroup(string $group): array
    {
        return self::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * پاک‌سازی کش یک تنظیم خاص.
     */
    public static function forgetCache(string $key): void
    {
        Cache::forget(self::cacheKey($key));
    }

    /**
     * پاک‌سازی کش تمام تنظیمات.
     */
    public static function flushCache(): void
    {
        self::query()->pluck('key')->each(
            fn (string $key): bool => Cache::forget(self::cacheKey($key))
        );
    }

    /**
     * کلید کش مربوط به یک تنظیم.
     */
    private static function cacheKey(string $key): string
    {
        return "setting.{$key}";
    }

    /**
     * پاک‌سازی کش هنگام به‌روزرسانی یا حذف مستقیم مدل.
     */
    protected static function booted(): void
    {
        self::saved(fn (self $setting): mixed => Cache::forget(self::cacheKey($setting->key)));
        self::deleted(fn (self $setting): mixed => Cache::forget(self::cacheKey($setting->key)));
    }
}
