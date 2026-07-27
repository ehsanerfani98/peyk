<?php

return [
    // طول کد تایید عددی که در جدول order_verifications ذخیره می‌شود
    'code_length' => env('ORDER_VERIFICATION_CODE_LENGTH', 4),

    // مدت اعتبار لینک/کد تایید فرستنده و گیرنده بر حسب دقیقه (پیش‌فرض: ۲۴ ساعت)
    'expire_minutes' => env('ORDER_VERIFICATION_EXPIRE_MINUTES', 1440),
];
