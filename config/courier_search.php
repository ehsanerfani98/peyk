<?php

return [
    // فاصله زمانی (ثانیه) بین هر بار تلاش برای پیدا کردن پیک، تا زمانی که سفارش در وضعیت SEARCHING_COURIER است
    'interval_seconds' => env('COURIER_SEARCH_INTERVAL_SECONDS', 10),

    // سقف زمانی (دقیقه) هر دور جستجو؛ در صورت عدم موفقیت، وضعیت سفارش به COURIER_NOT_FOUND تغییر می‌کند.
    // کاربر می‌تواند جستجو را مجددا آغاز کند (با ریست شدن تایمر) یا سفارش را لغو کند.
    'timeout_minutes' => env('COURIER_SEARCH_TIMEOUT_MINUTES', 5),

    // حداکثر شعاع جستجو (متر) حول موقعیت فرستنده برای انتخاب پیک
    'max_distance_meters' => env('COURIER_SEARCH_MAX_DISTANCE_METERS', 5000),

    // مهلت پاسخ پیک به پیشنهاد (ثانیه)؛ اگر پیک در این بازه نه قبول کند و نه رد،
    // سیستم به‌صورت خودکار پیشنهاد را رد شده در نظر می‌گیرد و جستجو را ادامه می‌دهد
    'courier_offer_timeout_seconds' => env('COURIER_OFFER_TIMEOUT_SECONDS', 60),
];
