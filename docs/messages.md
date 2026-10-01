# لیست متن پیام‌های استفاده‌شده در برنامه

این سند شامل تمام متن پیام‌های استفاده‌شده در برنامه (پیامک‌ها، پیام‌های API، اعتبارسنجی، خطاها و رابط کاربری) است.

---

## ۱. پیامک‌ها (SMS Patterns)

منبع: [`config/mediana.php`](../config/mediana.php) (کلید `simulator_templates`) و [`getPattern()`](../app/Helpers/SmsSimulator.php)

| کد پترن | متن پیام |
|---|---|
| `x7km2n9p4qrst` | `'کد تایید شما '.$code.' می‌باشد'` |
| `a3b8f2k9m5xyz` | `'لینک تایید سفارش: '.$code` |
| `z9y4w1v6n2abc` | `'در حال جستجوی پیک برای سفارش '.$code.' هستیم'` |
| `d5e8r3t7y1uio` | `'پیک برای سفارش '.$code.' تعیین شد'` |
| `p9l2k5j8h6gfd` | `'سفارش '.$code.' آماده تحویل به پیک می‌باشد'` |
| `s4a7w2q1e6rtz` | `'سفارش '.$code.' توسط پیک دریافت شد'` |
| `x9c3v6b8n5mlk` | `'سفارش '.$code.' در مسیر ارسال می‌باشد'` |
| `j1h4g7f0d2asz` | `'سفارش '.$code.' تحویل داده شد'` |
| `q6w9e3r5t8yui` | `'سفارش '.$code.' لغو گردید'` |
| `o2p7l1k4m9nbv` | `'سفارش '.$code.' آماده تحویل است'` |
| `c5x8z3a6s1dgf` | `'سفارش '.$code.' توسط پیک دریافت شد'` |
| `h7j4k9l2m6qwe` | `'سفارش '.$code.' در مسیر است'` |
| `r1t5y8u3i0opz` | `'سفارش '.$code.' تحویل داده شد'` |
| `b4n7v2c6x9zlm` | `'سفارش '.$code.' لغو شد'` |
| `k8j3h5g2f1dsa` | `'پیک گرامی، سفارش جدید با کد '.$code.' برای شما یافت شد'` |
| `p0o9i8u7y6tre` | `'پیک گرامی، سفارش '.$code.' لغو گردید'` |
| `w2e4r5t6y7u8i` | `'سفارش '.$code.' به دلیل عدم یافتن پیک لغو شد'` |
| `l9k0j1h2g3f4d` | `'لطفا در نظرسنجی شرکت کنید : '.$code` |
| `s5a6z7x8c9v0b` | `'لینک پرداخت سفارش : '.$code` |
| default | `'پیامک (پترن {کد پترن}): {مقدار پارامتر}'` |

---

## ۲. پیام‌های خطای سرویس پیامک

منبع: [`SmsSendingException.php`](../app/Services/Sms/Exceptions/SmsSendingException.php) و [`MedianaSmsService.php`](../app/Services/Sms/MedianaSmsService.php)

- `'ارسال پیامک با خطا مواجه شد.'`
- کدهای خطای عددی مدیانا مطابق جدول مستندات به پیام فارسی نگاشت شده‌اند؛ برای نمونه `1042` → `'موجودی کیف پول پیامک کافی نیست.'` و `1047` → `'شماره تلفن گیرنده در لیست سیاه قرار دارد.'`
- در صورت وجود `meta.errorMessage` در پاسخ، همان پیام سرویس‌دهنده در اولویت قرار می‌گیرد.

---

## ۳. پیام‌های خطای وضعیت سفارش (OrderStateException)

منبع: [`CourierOfferService.php`](../app/Services/Order/CourierOfferService.php:100)

- `'این سفارش در حال حاضر در وضعیت پیشنهاد به پیک نیست.'`
- `'این سفارش به شما پیشنهاد نشده است.'`

منبع: [`OrderCancellationService.php`](../app/Services/Order/OrderCancellationService.php:23)

- `'این سفارش متعلق به شما نیست.'`
- `'این سفارش به شما تخصیص داده نشده است.'`
- `'سفارش در وضعیت فعلی (پیک بسته را تحویل گرفته یا سفارش به پایان رسیده) قابل لغو نیست.'`

منبع: [`OrderFulfillmentService.php`](../app/Services/Order/OrderFulfillmentService.php:32)

- `'سفارش در وضعیت انتظار تحویل گرفتن بسته نیست.'`
- `'سفارش در وضعیت درحال ارسال نیست.'`
- `'روش پرداخت این سفارش نقدی نیست.'`
- `'پرداخت این سفارش قبلاً ثبت شده است.'`
- `'سفارش در وضعیت قابل قبول برای ثبت پرداخت نیست.'`
- `'این سفارش به شما تخصیص داده نشده است.'`

منبع: [`OrderRestartSearchController.php`](../app/Http/Controllers/Api/Order/OrderRestartSearchController.php:22)

- `'این سفارش متعلق به شما نیست.'`
- `'تنها سفارش‌هایی که جستجوی پیک برای آن‌ها به پایان رسیده قابل جستجوی مجدد هستند.'`

---

## ۴. پیام‌های خطای تایید سفارش (OrderVerificationException)

منبع: [`OrderVerificationException.php`](../app/Services/Order/Exceptions/OrderVerificationException.php:19)

- `'لینک تایید معتبر نیست.'`
- `'کد/لینک تایید منقضی شده است. لطفا با فرستنده/گیرنده سفارش تماس بگیرید.'`
- `'امکان تایید این مرحله وجود ندارد. لطفا با پشتیبانی تماس بگیرید.'`
- `'کد وارد شده صحیح نیست.'`

---

## ۵. پیام‌های خطای OTP (OtpException)

منبع: [`OtpException.php`](../app/Services/Auth/Exceptions/OtpException.php:19)

- `'درخواست بیش از حد مجاز است. لطفا کمی صبر کنید و دوباره تلاش کنید.'`
- `'کد تایید یافت نشد یا منقضی شده است. دوباره درخواست دهید.'`
- `'تعداد تلاش‌های مجاز به پایان رسید. دوباره درخواست کد تایید دهید.'`
- `'کد تایید نادرست است.'`

---

## ۶. پیام‌های خطای نظرسنجی (SurveyService)

منبع: [`SurveyService.php`](../app/Services/Order/SurveyService.php:70)

- `'شما قبلا برای این سفارش نظر ثبت کرده‌اید.'`
- `'توکن نظرسنجی یافت نشد.'`
- `'این توکن قبلا استفاده شده است.'`
- `'توکن نظرسنجی منقضی شده است.'`
- `'نظرسنجی برای این سفارش قبلا ثبت شده است.'`
- `'امتیاز باید بین ۱ تا ۵ باشد.'`

---

## ۷. پیام‌های خطای پرداخت

منبع: [`ZibalDriver.php`](../app/Services/Payment/Drivers/ZibalDriver.php:119)

- `'مبلغ تراکنش با مبلغ ثبت شده مطابقت ندارد.'`

منبع: [`ZarinpalDriver.php`](../app/Services/Payment/Drivers/ZarinpalDriver.php:69)

- `'پرداخت آنلاین'`
- `'برای تایید تراکنش زرین‌پال، ارسال مبلغ (amount) الزامی است.'`

منبع: [`PaymentController.php`](../app/Http/Controllers/PaymentController.php:50)

- `'خطا در اتصال به درگاه پرداخت: '.$result->message`

---

## ۸. پیام‌های اعتبارسنجی (Validation)

منبع: [`VerifyOtpRequest.php`](../app/Http/Requests/Auth/VerifyOtpRequest.php:26)

- `'شماره موبایل الزامی است.'`
- `'شماره موبایل معتبر نیست.'`
- `'کد تایید الزامی است.'`
- `'کد تایید باید :digits رقم باشد.'`

منبع: [`SendOtpRequest.php`](../app/Http/Requests/Auth/SendOtpRequest.php:24)

- `'شماره موبایل الزامی است.'`
- `'شماره موبایل معتبر نیست.'`

منبع: [`UpdateProfileRequest.php`](../app/Http/Requests/Profile/UpdateProfileRequest.php:27)

- `'نام و نام خانوادگی الزامی است.'`
- `'نام نباید بیشتر از ۲۵۵ کاراکتر باشد.'`
- `'آدرس الزامی است.'`
- `'آدرس نباید بیشتر از ۱۰۰۰ کاراکتر باشد.'`
- `'عرض جغرافیایی الزامی است.'`
- `'عرض جغرافیایی باید عدد باشد.'`
- `'عرض جغرافیایی باید بین -90 و 90 باشد.'`
- `'طول جغرافیایی الزامی است.'`
- `'طول جغرافیایی باید عدد باشد.'`
- `'طول جغرافیایی باید بین -180 و 180 باشد.'`

منبع: [`SubmitReviewRequest.php`](../app/Http/Requests/Order/SubmitReviewRequest.php:25)

- `'امتیاز الزامی است.'`
- `'امتیاز باید عدد صحیح باشد.'`
- `'حداقل امتیاز ۱ است.'`
- `'حداکثر امتیاز ۵ است.'`
- `'متن نظر نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.'`

منبع: [`StoreOrderRequest.php`](../app/Http/Requests/Order/StoreOrderRequest.php:66)

- `'شماره موبایل فرستنده و گیرنده نمی‌تواند یکسان باشد.'`
- `'شما یک سفارش فعال دارید و تا تکمیل یا لغو آن، امکان ثبت سفارش جدید وجود ندارد.'`
- `'شماره موبایل فرستنده الزامی است.'`
- `'شماره موبایل فرستنده معتبر نیست.'`
- `'نام فرستنده الزامی است.'`
- `'آدرس فرستنده الزامی است.'`
- `'عرض جغرافیایی فرستنده الزامی است.'`
- `'طول جغرافیایی فرستنده الزامی است.'`
- `'شماره موبایل گیرنده الزامی است.'`
- `'شماره موبایل گیرنده معتبر نیست.'`
- `'نام گیرنده الزامی است.'`
- `'آدرس گیرنده الزامی است.'`
- `'عرض جغرافیایی گیرنده الزامی است.'`
- `'طول جغرافیایی گیرنده الزامی است.'`
- `'اندازه مرسوله الزامی است.'`
- `'اندازه مرسوله باید یکی از مقادیر small, medium, large باشد.'`
- `'وزن مرسوله باید عدد باشد.'`
- `'وزن مرسوله باید حداقل ۰.۰۱ کیلوگرم باشد.'`
- `'روش پرداخت الزامی است.'`
- `'روش پرداخت نامعتبر است.'`
- `'پرداخت توسط الزامی است.'`
- `'مقدار پرداخت توسط نامعتبر است.'`
- `'قیمت الزامی است.'`
- `'قیمت باید عدد باشد.'`
- `'قیمت نمی‌تواند منفی باشد.'`

منبع: [`form.php`](../resources/views/components/survey/⚡form/form.php:51)

- `'لطفاً امتیاز خود را انتخاب کنید.'`
- `'حداقل امتیاز ۱ است.'`
- `'حداکثر امتیاز ۵ است.'`
- `'متن نظر نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.'`

---

## ۹. پیام‌های پاسخ API (Controllers)

منبع: [`AuthController.php`](../app/Http/Controllers/Api/Auth/AuthController.php:48)

- `'کد تایید ارسال شد.'`
- `'ورود با موفقیت انجام شد.'`
- `'خروج با موفقیت انجام شد.'`

منبع: [`ProfileController.php`](../app/Http/Controllers/Api/Profile/ProfileController.php:23)

- `'اطلاعات پروفایل با موفقیت ثبت شد.'`

منبع: [`CourierOfferController.php`](../app/Http/Controllers/Api/Order/CourierOfferController.php:31)

- `'سفارش با موفقیت پذیرفته شد.'`
- `'سفارش رد شد.'`

منبع: [`OrderCancellationController.php`](../app/Http/Controllers/Api/Order/OrderCancellationController.php:35)

- `'سفارش لغو شد.'`

منبع: [`OrderFulfillmentController.php`](../app/Http/Controllers/Api/Order/OrderFulfillmentController.php:33)

- `'تحویل گرفتن بسته با موفقیت تایید شد.'`
- `'تحویل بسته با موفقیت تایید شد.'`
- `'پرداخت نقدی با موفقیت ثبت شد.'`
- `'موقعیت در مسیر ثبت شد.'`

منبع: [`OrderController.php`](../app/Http/Controllers/Api/Order/OrderController.php:42)

- `'هزینه با موفقیت محاسبه شد.'`
- `'سفارش با موفقیت ثبت شد.'`
- `'سفارش ها با موفقیت دریافت شد.'`
- `'خطا در دریافت سفارش‌ها'`
- `'سفارش‌های پیک با موفقیت دریافت شد.'`
- `'خطا در دریافت سفارش‌های پیک'`
- `'سفارش با موفقیت دریافت شد.'`
- `'خطا در دریافت سفارش.'`

منبع: [`OrderRestartSearchController.php`](../app/Http/Controllers/Api/Order/OrderRestartSearchController.php:35)

- `'جستجوی پیک مجددا آغاز شد.'`

منبع: [`OrderVerificationController.php`](../app/Http/Controllers/Api/Order/OrderVerificationController.php:46)

- `'تایید با موفقیت انجام شد.'`

منبع: [`ReviewController.php`](../app/Http/Controllers/Api/Order/ReviewController.php:39)

- `'نظر با موفقیت ثبت شد.'`

---

## ۱۰. پیام‌های رابط کاربری (Livewire / Blade)

منبع: [`login.php`](../resources/views/components/auth/⚡login/login.php:42)

- `'اطلاعات ورود صحیح نیست.'`
- `"تعداد تلاش‌ها زیاد بود. لطفاً {$seconds} ثانیه دیگر تلاش کنید."`

منبع: [`profile.php`](../resources/views/components/⚡profile/profile.php:38)

- `'اطلاعات پروفایل به‌روزرسانی شد'`
- `'رمز عبور تغییر کرد'`

منبع: [`success.blade.php`](../resources/views/components/payment/⚡success/success.blade.php:10)

- `'پرداخت با موفقیت انجام شد'`
- `'سفارش شما با موفقیت پرداخت شد. می‌توانید وضعیت سفارش را از طریق اپلیکیشن پیگیری کنید.'`
- `'بازگشت به اپلیکیشن'`

منبع: [`failed.blade.php`](../resources/views/components/payment/⚡failed/failed.blade.php:10)

- `'پرداخت ناموفق بود'`
- `'متاسفانه پرداخت سفارش شما با مشکل مواجه شد. لطفاً مجدداً از طریق اپلیکیشن اقدام به پرداخت نمایید.'`

منبع: [`confirm.blade.php`](../resources/views/components/verify/⚡confirm/confirm.blade.php:12)

- `'تایید با موفقیت انجام شد'`
- `'تایید فرستنده'` / `'تایید گیرنده'`
- `'تایید فرستنده با موفقیت انجام شد. کد تایید را هنگام تحویل بسته به پیک ارائه دهید.'`
- `'تایید گیرنده با موفقیت انجام شد. کد تایید را هنگام دریافت بسته از پیک ارائه دهید.'`
- `'لینک منقضی شده است'`
- `'لینک قفل شده است'`
- `'خطا در تایید'`

منبع: [`form.blade.php`](../resources/views/components/survey/⚡form/form.blade.php:34)

- `'نظر شما ثبت شد'`
- `'از اینکه وقت گذاشتید و نظر خود را ثبت کردید، سپاسگزاریم.'`
- `'نظرسنجی'`
- `'{{ $this->surveyTypeLabel() }} گرامی، لطفاً نظر خود را درباره سفارش ثبت کنید.'`
- `'در حال بارگذاری...'`
- `'خطا'`

منبع: [`form.php`](../resources/views/components/survey/⚡form/form.php:78)

- `'فرستنده'` / `'گیرنده'` / `'مشتری'` / `'کاربر'`

---

## ۱۱. پیام‌های پنل مدیریت (Admin Panel)

منبع: [`show.php`](../resources/views/components/admin/payments/⚡show/show.php:32)

- `'پرداخت به صورت دستی ثبت شد'`
- `'این سفارش هنوز پرداخت نشده است'`
- `'بازپرداخت دستی توسط مدیر'`
- `'بازپرداخت با موفقیت انجام شد'`

منبع: [`index.php`](../resources/views/components/admin/roles/⚡index/index.php:65)

- `'نقش جدید ساخته شد'`
- `'مجوزها به‌روزرسانی شد'`
- `'نقش مدیر قابل حذف نیست'`
- `'نقش با موفقیت حذف شد'`

منبع: [`index.php`](../resources/views/components/admin/users/⚡index/index.php:90)

- `'نمی‌تونی نقش خودت رو ویرایش کنی'`
- `'نقش‌های کاربر به‌روزرسانی شد'`
- `'برای ویرایش اطلاعات خودت به صفحه پروفایل برو'`
- `'اطلاعات کاربر به‌روزرسانی شد'`
- `'رمز عبور کاربر تغییر کرد'`
- `'نمی‌تونی خودت رو حذف کنی'`
- `'کاربر حذف شد'`
- `'کاربر جدید ساخته شد'`

منبع: [`show.php`](../resources/views/components/admin/customers/⚡show/show.php:42)

- `'عملیات مسدودسازی/رفع مسدودیت ثبت شد'`

منبع: [`index.php`](../resources/views/components/admin/reviews/⚡index/index.php:37)

- `'نظر با موفقیت حذف شد'`

منبع: [`manual-actions.php`](../resources/views/components/admin/orders/⚡manual-actions/manual-actions.php:79)

- `'سفارش با موفقیت لغو شد'`
- `'وضعیت سفارش تغییر کرد'`
- `'پیک با موفقیت تخصیص یافت'`
- `'قیمت سفارش به‌روزرسانی شد'`
- `'بازپرداخت با موفقیت ثبت شد'`
- `'تحویل سفارش تایید شد'`
- `'پیامک تایید مجدداً ارسال شد'`

منبع: [`index.php`](../resources/views/components/admin/couriers/⚡index/index.php:48)

- `'پیک با موفقیت حذف شد'`

منبع: [`show.php`](../resources/views/components/admin/couriers/⚡show/show.php:72)

- `'وضعیت پیک تغییر کرد'`
- `'پیک با موفقیت حذف شد'`

منبع: [`create.php`](../resources/views/components/admin/couriers/⚡create/create.php:84)

- `'پیک با موفقیت ثبت شد'`

منبع: [`courier-search.php`](../resources/views/components/admin/settings/⚡courier-search/courier-search.php:47)

- `'تنظیمات جستجوی پیک ذخیره شد'`

---

## ۱۲. برچسب‌های وضعیت سفارش (Status Labels)

منبع: [`show.php`](../resources/views/components/admin/orders/⚡show/show.php:47) و [`index.php`](../resources/views/components/admin/orders/⚡index/index.php:61)

- `'ایجاد شده'`
- `'منتظر تایید فرستنده'`
- `'فرستنده تایید شد'`
- `'منتظر تایید گیرنده'`
- `'گیرنده تایید شد'`
- `'در جستجوی پیک'`
- `'پیشنهاد به پیک'`
- `'پیک پذیرفت'`
- `'پیک رد کرد'`
- `'پیک تخصیص یافت'`
- `'منتظر تحویل بسته'`
- `'بسته تحویل گرفته شد'`
- `'در مسیر تحویل'`
- `'تحویل شد'`
- `'تحویل ناموفق'`
- `'بازگشت به فرستنده'`
- `'لغو شده'`
- `'پیک یافت نشد'`

---

## ۱۳. برچسب‌های لاگ فعالیت (Activity Log Labels)

منبع: [`activity.php`](../resources/views/components/admin/logs/⚡activity/activity.php:48)

- `'لغو سفارش'`
- `'تغییر وضعیت سفارش'`
- `'تخصیص پیک'`
- `'تغییر قیمت سفارش'`
- `'بازپرداخت سفارش'`
- `'تایید دستی تحویل'`
- `'ارسال مجدد پیامک تایید'`
- `'ثبت پیک جدید'`
- `'تغییر وضعیت پیک'`
- `'ثبت دستی پرداخت'`
- `'پردازش بازپرداخت'`
- `'به‌روزرسانی تنظیمات عمومی'`
- `'به‌روزرسانی تنظیمات جستجوی پیک'`
- `'به‌روزرسانی تنظیمات پرداخت'`
- `'به‌روزرسانی تنظیمات پیامک'`
- `'مسدودسازی/رفع مسدودیت مشتری'`
