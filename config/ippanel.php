<?php

return [

    /*
    |--------------------------------------------------------------------------
    | آدرس پایه API آی پی پنل
    |--------------------------------------------------------------------------
    */
    'base_url' => env('IPPANEL_BASE_URL', 'https://api.ippanel.com'),

    /*
    |--------------------------------------------------------------------------
    | توکن احراز هویت (هدر Authorization)
    |--------------------------------------------------------------------------
    */
    'token' => env('IPPANEL_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | شماره فرستنده (از پنل ippanel)
    |--------------------------------------------------------------------------
    */
    'from_number' => env('IPPANEL_FROM_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن و کلید پارامتر پیش‌فرض (OTP ورود/ثبت‌نام)
    |--------------------------------------------------------------------------
    */
    'otp_pattern_code' => env('IPPANEL_OTP_PATTERN_CODE'),
    'otp_param_key' => env('IPPANEL_OTP_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن لینک تایید فرستنده/گیرنده سفارش (غیرمشتری)
    |--------------------------------------------------------------------------
    */
    'verification_link_pattern_code' => env('IPPANEL_VERIFICATION_LINK_PATTERN_CODE'),
    'verification_link_param_key' => env('IPPANEL_VERIFICATION_LINK_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کلید پارامتر پیامک‌های اطلاع‌رسانی وضعیت سفارش
    |--------------------------------------------------------------------------
    */
    'order_status_param_key' => env('IPPANEL_ORDER_STATUS_PARAM_KEY', 'code'),
    'non_customer_status_param_key' => env('IPPANEL_NON_CUSTOMER_STATUS_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کدهای پترن اطلاع‌رسانی تغییر وضعیت به مشتری
    |--------------------------------------------------------------------------
    */
    'pattern_order_searching' => env('IPPANEL_PATTERN_ORDER_SEARCHING'),
    'pattern_order_courier_assigned' => env('IPPANEL_PATTERN_ORDER_COURIER_ASSIGNED'),
    'pattern_order_waiting_pickup' => env('IPPANEL_PATTERN_ORDER_WAITING_PICKUP'),
    'pattern_order_picked_up' => env('IPPANEL_PATTERN_ORDER_PICKED_UP'),
    'pattern_order_in_transit' => env('IPPANEL_PATTERN_ORDER_IN_TRANSIT'),
    'pattern_order_delivered' => env('IPPANEL_PATTERN_ORDER_DELIVERED'),
    'pattern_order_cancelled' => env('IPPANEL_PATTERN_ORDER_CANCELLED'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن اطلاع‌رسانی تغییر وضعیت به فرستنده/گیرنده غیرمشتری
    |--------------------------------------------------------------------------
    */
    'pattern_sender_order_waiting_pickup' => env('IPPANEL_PATTERN_SENDER_WAITING_PICKUP'),
    'pattern_sender_order_picked_up' => env('IPPANEL_PATTERN_SENDER_PICKED_UP'),
    'pattern_sender_order_in_transit' => env('IPPANEL_PATTERN_SENDER_IN_TRANSIT'),
    'pattern_sender_order_delivered' => env('IPPANEL_PATTERN_SENDER_DELIVERED'),
    'pattern_sender_order_cancelled' => env('IPPANEL_PATTERN_SENDER_CANCELLED'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن اطلاع‌رسانی به پیک
    |--------------------------------------------------------------------------
    */
    'pattern_courier_offer' => env('IPPANEL_PATTERN_COURIER_OFFER'),
    'courier_offer_param_key' => env('IPPANEL_COURIER_OFFER_PARAM_KEY', 'code'),
    'pattern_courier_cancelled' => env('IPPANEL_PATTERN_COURIER_CANCELLED'),
    'courier_cancelled_param_key' => env('IPPANEL_COURIER_CANCELLED_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن اطلاع‌رسانی لغو سیستم (تایم‌اوت جستجوی پیک)
    |--------------------------------------------------------------------------
    */
    'pattern_system_cancellation' => env('IPPANEL_PATTERN_SYSTEM_CANCELLATION'),
    'system_cancellation_param_key' => env('IPPANEL_SYSTEM_CANCELLATION_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن لینک نظرسنجی
    |--------------------------------------------------------------------------
    */
    'pattern_survey_link' => env('IPPANEL_PATTERN_SURVEY_LINK'),
    'survey_link_param_key' => env('IPPANEL_SURVEY_LINK_PARAM_KEY', 'code'),

    /*
    |--------------------------------------------------------------------------
    | کد پترن پرداخت نقدی (لینک پیگیری سفارش)
    |--------------------------------------------------------------------------
    */
    'pattern_cash_on_delivery' => env('IPPANEL_PATTERN_CASH_ON_DELIVERY'),
    'cash_on_delivery_param_key' => env('IPPANEL_CASH_ON_DELIVERY_PARAM_KEY', 'code'),

];
