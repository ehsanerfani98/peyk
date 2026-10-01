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
    | کلیدها «کلید منطقی» هر اطلاع‌رسانی هستند (مثل pattern_order_searching یا
    | otp_pattern_code)، نه کد پترن واقعی مدیانا. این کار باعث می‌شود حالت
    | شبیه‌ساز کاملاً مستقل از کدهای پترن ذخیره‌شده در دیتابیس کار کند.
    | جای‌گذار :value با مقدار پارامتر جایگزین می‌شود و برای کلیدهای تعریف‌نشده،
    | شبیه‌ساز یک پیام عمومی نمایش می‌دهد.
    |
    */
    'simulator_templates' => [
        'otp_pattern_code' => 'کد تایید شما :value می‌باشد',
        'verification_link_pattern_code' => 'لینک تایید سفارش: :value',
        'pattern_order_searching' => 'در حال جستجوی پیک برای سفارش :value هستیم',
        'pattern_courier_not_found' => 'برای سفارش :value پیکی یافت نشد؛ می‌توانید جستجو را مجدداً آغاز کنید یا سفارش را لغو کنید',
        'pattern_order_courier_assigned' => 'پیک برای سفارش :value تعیین شد',
        'pattern_order_waiting_pickup' => 'سفارش :value آماده تحویل به پیک می‌باشد',
        'pattern_order_picked_up' => 'سفارش :value توسط پیک دریافت شد',
        'pattern_order_in_transit' => 'سفارش :value در مسیر ارسال می‌باشد',
        'pattern_order_delivered' => 'سفارش :value تحویل داده شد',
        'pattern_order_cancelled' => 'سفارش :value لغو گردید',
        'pattern_sender_order_waiting_pickup' => 'سفارش :value آماده تحویل است',
        'pattern_sender_order_picked_up' => 'سفارش :value توسط پیک دریافت شد',
        'pattern_sender_order_in_transit' => 'سفارش :value در مسیر است',
        'pattern_sender_order_delivered' => 'سفارش :value تحویل داده شد',
        'pattern_sender_order_cancelled' => 'سفارش :value لغو شد',
        'pattern_courier_offer' => 'پیک گرامی، سفارش جدید با کد :value برای شما یافت شد',
        'pattern_courier_cancelled' => 'پیک گرامی، سفارش :value لغو گردید',
        'pattern_system_cancellation' => 'سفارش :value به دلیل عدم یافتن پیک لغو شد',
        'pattern_survey_link' => 'لطفا در نظرسنجی شرکت کنید : :value',
        'pattern_cash_on_delivery' => 'لینک پرداخت سفارش : :value',
    ],

];
