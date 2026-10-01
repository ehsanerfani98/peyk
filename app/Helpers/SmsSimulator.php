<?php

use App\Models\SmsMessage;
use Illuminate\Support\Facades\Log;

/**
 * هلپر ثبت پیامک در حالت شبیه‌ساز.
 *
 * در حالت شبیه‌سازی، پیامک ارسال واقعی نمی‌شود و به‌جای آن در جدول
 * `sms_messages` ذخیره می‌گردد تا محتوای آن (کد تایید، لینک و ...) از طریق
 * ماژول «شبیه‌ساز پیامک» در پنل مدیریت قابل مشاهده و کپی باشد.
 *
 * ورودی $templateKey یک «کلید منطقی» است (مثل pattern_order_searching یا
 * otp_pattern_code) که مستقل از کدهای پترن واقعی مدیانا است؛ متن پیام از
 * config('mediana.simulator_templates') با همان کلید خوانده می‌شود.
 */
function Sms_Simulator_Send(string $localMobile, mixed $paramValue, string $templateKey): bool
{
    try {
        $message = SmsMessage::create([
            'receiver' => $localMobile,
            'sender' => (string) config('sms_simulator.sender', '+983000505'),
            'content' => getPattern($templateKey, $paramValue),
            'pattern_code' => $templateKey,
        ]);

        Log::info('sms_simulator.stored', [
            'id' => $message->id,
            'receiver' => $message->receiver,
            'template' => $templateKey,
        ]);

        return true;
    } catch (Throwable $e) {
        // خطای درج نباید بی‌صدا باشد: سطح error ثبت می‌شود و false برگردانده می‌شود
        // تا فراخوان‌کننده (SmsSender) مانند حالت واقعی رفتار کند.
        Log::error('sms_simulator.store_failed', [
            'receiver' => $localMobile,
            'template' => $templateKey,
            'exception' => $e::class,
            'error' => $e->getMessage(),
        ]);

        return false;
    }
}

/**
 * ساخت متن پیام شبیه‌ساز بر اساس کلید منطقی قالب.
 *
 * کلیدها همان «کلید منطقی» اطلاع‌رسانی هستند (مثل pattern_order_searching) که
 * در config('mediana.simulator_templates') تعریف شده‌اند و مستقل از کدهای پترن
 * واقعی مدیانا هستند. برای کلیدهای تعریف‌نشده، پیام عمومی بازگردانده می‌شود.
 */
function getPattern(string $templateKey, mixed $code): string
{
    $value = is_scalar($code) ? (string) $code : '';
    $template = config("mediana.simulator_templates.{$templateKey}");

    if (is_string($template) && $template !== '') {
        return str_replace(':value', $value, $template);
    }

    return "پیامک (قالب {$templateKey}): {$value}";
}
