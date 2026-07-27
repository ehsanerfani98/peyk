<?php

return [

    // تعداد رقم‌های کد تایید
    'length' => (int) env('OTP_LENGTH', 5),

    // مدت اعتبار کد تایید (ثانیه)
    'expire_seconds' => (int) env('OTP_EXPIRE_SECONDS', 120),

    // حداقل فاصله زمانی بین دو درخواست ارسال کد برای یک شماره (ثانیه)
    'resend_seconds' => (int) env('OTP_RESEND_SECONDS', 60),

    // حداکثر تعداد تلاش مجاز برای وارد کردن کد تایید
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),

];
