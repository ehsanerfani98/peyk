<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * پیامک ذخیره‌شده در حالت شبیه‌ساز.
 *
 * در حالت شبیه‌سازی، به‌جای ارسال واقعی، تمام پیامک‌ها در این جدول ذخیره
 * می‌شوند تا در ماژول «شبیه‌ساز پیامک» پنل مدیریت قابل مشاهده و استفاده باشند.
 */
final class SmsMessage extends Model
{
    protected $fillable = [
        'receiver',
        'sender',
        'content',
        'pattern_code',
    ];
}
