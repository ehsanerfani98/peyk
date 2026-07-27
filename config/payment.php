<?php

return [

    /*
    |--------------------------------------------------------------------------
    | درگاه پیش‌فرض پرداخت
    |--------------------------------------------------------------------------
    |
    | نام درایور پیش‌فرض که در صورت عدم مشخص کردن درایور مورد استفاده قرار می‌گیرد.
    | مقادیر مجاز: "zarinpal" | "zibal"
    |
    */
    'default' => env('PAYMENT_DEFAULT_DRIVER', 'zarinpal'),

    /*
    |--------------------------------------------------------------------------
    | تنظیمات درایورها
    |--------------------------------------------------------------------------
    */
    'drivers' => [

        'zarinpal' => [
            'merchant_id' => env('ZARINPAL_MERCHANT_ID', ''),
            'sandbox' => env('ZARINPAL_SANDBOX', false),

            // آدرس‌های پایه - در حالت عادی و سندباکس متفاوت هستند
            'base_url' => env('ZARINPAL_BASE_URL', 'https://api.zarinpal.com/pg/v4/payment/'),
            'sandbox_base_url' => env('ZARINPAL_SANDBOX_BASE_URL', 'https://sandbox.zarinpal.com/pg/v4/payment/'),

            'start_pay_url' => env('ZARINPAL_START_PAY_URL', 'https://www.zarinpal.com/pg/StartPay/'),
            'sandbox_start_pay_url' => env('ZARINPAL_SANDBOX_START_PAY_URL', 'https://sandbox.zarinpal.com/pg/StartPay/'),

            // واحد پول: IRT (تومان) یا IRR (ریال)
            'currency' => env('ZARINPAL_CURRENCY', 'IRT'),
        ],

        'zibal' => [
            'merchant' => env('ZIBAL_MERCHANT', 'zibal'), // در حالت سندباکس مقدار "zibal" استفاده می‌شود
            'sandbox' => env('ZIBAL_SANDBOX', false),

            'base_url' => env('ZIBAL_BASE_URL', 'https://gateway.zibal.ir'),
        ],

    ],

];
