<?php

return [

    /*
    |--------------------------------------------------------------------------
    | آدرس پایه API مدیانا
    |--------------------------------------------------------------------------
    */
    'base_url' => env('MEDIANA_BASE_URL', 'https://api.mediana.ir'),

    /*
    |--------------------------------------------------------------------------
    | کلید API (هدر X-API-KEY)
    |--------------------------------------------------------------------------
    */
    'api_key' => env('MEDIANA_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | نوع پیام (انتخاب خط ارسال)
    |--------------------------------------------------------------------------
    |
    | مقادیر مجاز: Informational | PromotionalToCustomers | PromotionalAll
    | این مقدار در بدنه درخواست به عنوان فیلد type ارسال می‌شود.
    |
    */
    'type' => env('MEDIANA_SMS_TYPE', 'Informational'),

    /*
    |--------------------------------------------------------------------------
    | شماره ارسال اختصاصی (اختیاری)
    |--------------------------------------------------------------------------
    |
    | اگر مقداردهی شود، در بدنه درخواست sendingNumber ارسال می‌شود و فیلد type
    | ارسال نمی‌شود (مدیانا یکی از دو حالت WithType یا WithNumber را می‌پذیرد).
    |
    */
    'sending_number' => env('MEDIANA_SENDING_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | کلید پارامتر پیش‌فرض پترن
    |--------------------------------------------------------------------------
    |
    | زمانی استفاده می‌شود که فراخواننده کلید پارامتر را تعیین نکند.
    |
    */
    'default_param_key' => env('MEDIANA_DEFAULT_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | تنظیمات اتصال
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('MEDIANA_TIMEOUT', 10),
    'connect_timeout' => (int) env('MEDIANA_CONNECT_TIMEOUT', 5),
    'retry_times' => (int) env('MEDIANA_RETRY_TIMES', 2),
    'retry_sleep_ms' => (int) env('MEDIANA_RETRY_SLEEP_MS', 200),

    /*
    |--------------------------------------------------------------------------
    | نکته مهم درباره تنظیمات ذخیره‌شده در دیتابیس
    |--------------------------------------------------------------------------
    |
    | مقادیر کدهای پترن و کلیدهای پارامتر (از OTP تا لینک نظرسنجی) از پنل مدیریت
    | در جدول settings و با پیشوند تاریخی `ippanel.` خوانده و ذخیره می‌شوند تا
    | داده‌هایی که قبلاً ثبت شده‌اند بدون مهاجرت داده قابل استفاده بمانند.
    | مقادیر همین فایل برای این کلیدها فقط نقش فال‌بک (مقدار پیش‌فرض) دارند.
    |
    | اعتبارنامه‌های سرویس مدیانا (base_url, api_key, type, sending_number) با
    | پیشوند `mediana.` در دیتابیس ذخیره می‌شوند.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | کد پترن و کلید پارامتر پیش‌فرض (OTP ورود/ثبت‌نام)
    |--------------------------------------------------------------------------
    |
    | OTP از اندپوینت اختصاصی /sms/v1/send/otp استفاده می‌کند؛ بنابراین تنها
    | کد پترن لازم است و کلید پارامتر توسط خود سرویس مدیریت می‌شود.
    |
    */
    'otp_pattern_code' => env('MEDIANA_OTP_PATTERN_CODE'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن لینک تایید فرستنده/گیرنده سفارش (غیرمشتری)
    |--------------------------------------------------------------------------
    */
    'verification_link_pattern_code' => env('MEDIANA_VERIFICATION_LINK_PATTERN_CODE'),
    'verification_link_param_key' => env('MEDIANA_VERIFICATION_LINK_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کلید پارامتر پیامک‌های اطلاع‌رسانی وضعیت سفارش
    |--------------------------------------------------------------------------
    */
    'order_status_param_key' => env('MEDIANA_ORDER_STATUS_PARAM_KEY', 'code'),
    'non_customer_status_param_key' => env('MEDIANA_NON_CUSTOMER_STATUS_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کدهای پترن اطلاع‌رسانی تغییر وضعیت به مشتری
    |--------------------------------------------------------------------------
    */
    'pattern_order_searching' => env('MEDIANA_PATTERN_ORDER_SEARCHING'),
    'pattern_order_courier_assigned' => env('MEDIANA_PATTERN_ORDER_COURIER_ASSIGNED'),
    'pattern_order_waiting_pickup' => env('MEDIANA_PATTERN_ORDER_WAITING_PICKUP'),
    'pattern_order_picked_up' => env('MEDIANA_PATTERN_ORDER_PICKED_UP'),
    'pattern_order_in_transit' => env('MEDIANA_PATTERN_ORDER_IN_TRANSIT'),
    'pattern_order_delivered' => env('MEDIANA_PATTERN_ORDER_DELIVERED'),
    'pattern_order_cancelled' => env('MEDIANA_PATTERN_ORDER_CANCELLED'),
    'pattern_courier_not_found' => env('MEDIANA_PATTERN_COURIER_NOT_FOUND'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن اطلاع‌رسانی تغییر وضعیت به فرستنده/گیرنده غیرمشتری
    |--------------------------------------------------------------------------
    */
    'pattern_sender_order_waiting_pickup' => env('MEDIANA_PATTERN_SENDER_WAITING_PICKUP'),
    'pattern_sender_order_picked_up' => env('MEDIANA_PATTERN_SENDER_PICKED_UP'),
    'pattern_sender_order_in_transit' => env('MEDIANA_PATTERN_SENDER_IN_TRANSIT'),
    'pattern_sender_order_delivered' => env('MEDIANA_PATTERN_SENDER_DELIVERED'),
    'pattern_sender_order_cancelled' => env('MEDIANA_PATTERN_SENDER_CANCELLED'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن اطلاع‌رسانی به پیک
    |--------------------------------------------------------------------------
    */
    'pattern_courier_offer' => env('MEDIANA_PATTERN_COURIER_OFFER'),
    'courier_offer_param_key' => env('MEDIANA_COURIER_OFFER_PARAM_KEY', 'code'),
    'pattern_courier_cancelled' => env('MEDIANA_PATTERN_COURIER_CANCELLED'),
    'courier_cancelled_param_key' => env('MEDIANA_COURIER_CANCELLED_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن اطلاع‌رسانی لغو سیستم (تایم‌اوت جستجوی پیک)
    |--------------------------------------------------------------------------
    */
    'pattern_system_cancellation' => env('MEDIANA_PATTERN_SYSTEM_CANCELLATION'),
    'system_cancellation_param_key' => env('MEDIANA_SYSTEM_CANCELLATION_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن لینک نظرسنجی
    |--------------------------------------------------------------------------
    */
    'pattern_survey_link' => env('MEDIANA_PATTERN_SURVEY_LINK'),
    'survey_link_param_key' => env('MEDIANA_SURVEY_LINK_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن پرداخت نقدی (لینک پیگیری سفارش)
    |--------------------------------------------------------------------------
    */
    'pattern_cash_on_delivery' => env('MEDIANA_PATTERN_CASH_ON_DELIVERY'),
    'cash_on_delivery_param_key' => env('MEDIANA_CASH_ON_DELIVERY_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | قالب پیام‌های شبیه‌ساز
    |--------------------------------------------------------------------------
    |
    | کلیدها همان کد پترن هستند و جای‌گذار :value با مقدار پارامتر جایگزین
    | می‌شود. برای کدهای پترن تعریف‌نشده، شبیه‌ساز یک پیام عمومی نمایش می‌دهد.
    |
    */
    'simulator_templates' => [
        'x7km2n9p4qrst' => 'کد تایید شما :value می‌باشد',
        'a3b8f2k9m5xyz' => 'لینک تایید سفارش: :value',
        'z9y4w1v6n2abc' => 'در حال جستجوی پیک برای سفارش :value هستیم',
        'd5e8r3t7y1uio' => 'پیک برای سفارش :value تعیین شد',
        'p9l2k5j8h6gfd' => 'سفارش :value آماده تحویل به پیک می‌باشد',
        's4a7w2q1e6rtz' => 'سفارش :value توسط پیک دریافت شد',
        'x9c3v6b8n5mlk' => 'سفارش :value در مسیر ارسال می‌باشد',
        'j1h4g7f0d2asz' => 'سفارش :value تحویل داده شد',
        'q6w9e3r5t8yui' => 'سفارش :value لغو گردید',
        'o2p7l1k4m9nbv' => 'سفارش :value آماده تحویل است',
        'c5x8z3a6s1dgf' => 'سفارش :value توسط پیک دریافت شد',
        'h7j4k9l2m6qwe' => 'سفارش :value در مسیر است',
        'r1t5y8u3i0opz' => 'سفارش :value تحویل داده شد',
        'b4n7v2c6x9zlm' => 'سفارش :value لغو شد',
        'k8j3h5g2f1dsa' => 'پیک گرامی، سفارش جدید با کد :value برای شما یافت شد',
        'p0o9i8u7y6tre' => 'پیک گرامی، سفارش :value لغو گردید',
        'w2e4r5t6y7u8i' => 'سفارش :value به دلیل عدم یافتن پیک لغو شد',
        'l9k0j1h2g3f4d' => 'لطفا در نظرسنجی شرکت کنید : :value',
        's5a6z7x8c9v0b' => 'لینک پرداخت سفارش : :value',
    ],

];
