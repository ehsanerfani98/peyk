<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * هلپر ارسال پیامک شبیه‌سازی شده به شبیه‌ساز گوشی موبایل.
 *
 * پیامک‌ها از طریق API شبیه‌ساز (FastAPI روی پورت 3000) ارسال می‌شوند
 * و به‌صورت لحظه‌ای در UI گوشی نمایش داده می‌شوند.
 */
function Sms_Simulator_Send(string $localMobile, mixed $paramValue, string $patternCode): void
{
    $baseUrl = Setting::getValue('sms_simulator_base_url', config('sms_simulator.base_url', 'http://localhost:3000'));

    try {
        $response = Http::timeout(5)
            ->withQueryParameters([
                'receiver' => $localMobile,
                'sender' => '+983000505',
                'content' => getPattern($patternCode, $paramValue),
            ])
            ->post("{$baseUrl}/sms/receive");

        if (! $response->successful()) {
            Log::warning('sms_simulator.send_failed', [
                'receiver' => $localMobile,
                'sender' => '+983000505',
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    } catch (Throwable $e) {
        Log::warning('sms_simulator.connection_failed', [
            'receiver' => $localMobile,
            'sender' => '+983000505',
            'error' => $e->getMessage(),
        ]);
    }
}

function getPattern(string $pattern, mixed $code)
{
    switch ($pattern) {
        case 'x7km2n9p4qrst':
            return 'کد تایید شما '.$code.' می‌باشد';
        case 'a3b8f2k9m5xyz':
            return 'لینک تایید سفارش: '.$code;
        case 'z9y4w1v6n2abc':
            return 'در حال جستجوی پیک برای سفارش '.$code.' هستیم';
        case 'd5e8r3t7y1uio':
            return 'پیک برای سفارش '.$code.' تعیین شد';
        case 'p9l2k5j8h6gfd':
            return 'سفارش '.$code.' آماده تحویل به پیک می‌باشد';
        case 's4a7w2q1e6rtz':
            return 'سفارش '.$code.' توسط پیک دریافت شد';
        case 'x9c3v6b8n5mlk':
            return 'سفارش '.$code.' در مسیر ارسال می‌باشد';
        case 'j1h4g7f0d2asz':
            return 'سفارش '.$code.' تحویل داده شد';
        case 'q6w9e3r5t8yui':
            return 'سفارش '.$code.' لغو گردید';
        case 'o2p7l1k4m9nbv':
            return 'سفارش '.$code.' آماده تحویل است';
        case 'c5x8z3a6s1dgf':
            return 'سفارش '.$code.' توسط پیک دریافت شد';
        case 'h7j4k9l2m6qwe':
            return 'سفارش '.$code.' در مسیر است';
        case 'r1t5y8u3i0opz':
            return 'سفارش '.$code.' تحویل داده شد';
        case 'b4n7v2c6x9zlm':
            return 'سفارش '.$code.' لغو شد';
        case 'k8j3h5g2f1dsa':
            return 'پیک گرامی، سفارش جدید با کد '.$code.' برای شما یافت شد';
        case 'p0o9i8u7y6tre':
            return 'پیک گرامی، سفارش '.$code.' لغو گردید';
        case 'w2e4r5t6y7u8i':
            return 'سفارش '.$code.' به دلیل عدم یافتن پیک لغو شد';
        case 'l9k0j1h2g3f4d':
            return 'لطفا در نظرسنجی شرکت کنید : '.$code;
        case 's5a6z7x8c9v0b':
            return 'لینک پرداخت سفارش : '.$code;
        default:
            return 'کد پترن معتبر نیست!';
    }
}
