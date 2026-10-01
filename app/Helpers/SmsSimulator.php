<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * هلپر ارسال پیامک شبیه‌سازی شده به شبیه‌ساز گوشی موبایل.
 *
 * پیامک‌ها از طریق API شبیه‌ساز (FastAPI روی پورت 3000) ارسال می‌شوند
 * و به‌صورت لحظه‌ای در UI گوشی نمایش داده می‌شوند.
 *
 * متن هر پترن از config('mediana.simulator_templates') خوانده می‌شود؛ بنابراین
 * افزودن کدهای پترن جدید نیازی به تغییر کد ندارد.
 */
function Sms_Simulator_Send(string $localMobile, mixed $paramValue, string $patternCode): void
{
    $baseUrl = Setting::getValue('sms_simulator_base_url', config('sms_simulator.base_url', 'http://localhost:3000'));
    $sender = (string) config('sms_simulator.sender', '+983000505');

    try {
        $response = Http::timeout(5)
            ->connectTimeout(2)
            ->withQueryParameters([
                'receiver' => $localMobile,
                'sender' => $sender,
                'content' => getPattern($patternCode, $paramValue),
            ])
            ->post("{$baseUrl}/sms/receive");

        if (! $response->successful()) {
            Log::warning('sms_simulator.send_failed', [
                'receiver' => $localMobile,
                'sender' => $sender,
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    } catch (Throwable $e) {
        Log::warning('sms_simulator.connection_failed', [
            'receiver' => $localMobile,
            'sender' => $sender,
            'error' => $e->getMessage(),
        ]);
    }
}

/**
 * ساخت متن پیام شبیه‌ساز بر اساس کد پترن.
 *
 * برای کدهای پترن تعریف‌نشده، پیام عمومی حاوی کد پترن و مقدار پارامتر بازگردانده می‌شود.
 */
function getPattern(string $pattern, mixed $code): string
{
    $value = is_scalar($code) ? (string) $code : '';
    $template = config("mediana.simulator_templates.{$pattern}");

    if (is_string($template) && $template !== '') {
        return str_replace(':value', $value, $template);
    }

    return "پیامک (پترن {$pattern}): {$value}";
}
