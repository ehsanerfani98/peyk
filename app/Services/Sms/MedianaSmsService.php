<?php

namespace App\Services\Sms;

use App\Models\Setting;
use App\Services\Auth\MobileNumberNormalizer;
use App\Services\Sms\Contracts\SmsProvider;
use App\Services\Sms\DTO\SmsSendResult;
use App\Services\Sms\Exceptions\SmsSendingException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * سرویس‌دهنده پیامک مدیانا.
 *
 * مستندات: https://api.mediana.ir (نسخه ۱.۰.۰)
 *
 * خلاصه رفتار سرویس:
 * - احراز هویت با هدر X-API-KEY انجام می‌شود.
 * - پاسخ‌ها ساختار {meta, data, pagination} دارند و موفقیت با data.succeed یا meta.code === 'OK' تشخیص داده می‌شود.
 * - کدهای خطای عددی مستندشده در SmsSendingException نگاشت شده‌اند.
 */
final class MedianaSmsService implements SmsProvider
{
    private const ENDPOINT_SEND_PATTERN = '/sms/v1/send/pattern';

    private const ENDPOINT_SEND_OTP = '/sms/v1/send/otp';

    private const ENDPOINT_REQUEST_STATUS = '/sms/v1/send-requests/status/%s';

    /**
     * {@inheritDoc}
     */
    public function sendPattern(
        string $localMobile,
        string $patternCode,
        array $parameters = [],
        ?string $type = null,
    ): SmsSendResult {
        $payload = [
            'recipients' => [$this->normalizeMobile($localMobile)],
            'patternCode' => $patternCode,
        ];

        // مدیانا یکی از دو حالت WithType یا WithNumber را می‌پذیرد؛
        // اگر شماره ارسال اختصاصی تنظیم شده باشد، فیلد type ارسال نمی‌شود.
        $sendingNumber = $this->configValue('sending_number');

        if (is_string($sendingNumber) && $sendingNumber !== '') {
            $payload['sendingNumber'] = $sendingNumber;
        } else {
            // مقدار پیش‌فرض در صورت خالی بودن تنظیمات (برای نمونه کش config قدیمی)
            $resolvedType = $type ?? (string) $this->configValue('type', 'Informational');

            $payload['type'] = $resolvedType !== '' ? $resolvedType : 'Informational';
        }

        if ($parameters !== []) {
            $payload['parameters'] = $parameters;
        }

        return $this->dispatch(self::ENDPOINT_SEND_PATTERN, $payload);
    }

    /**
     * {@inheritDoc}
     */
    public function sendOtp(string $localMobile, string $otpCode, string $patternCode): SmsSendResult
    {
        return $this->dispatch(self::ENDPOINT_SEND_OTP, [
            'patternCode' => $patternCode,
            'recipient' => $this->normalizeMobile($localMobile),
            'otpCode' => $otpCode,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function requestStatus(string $requestId): array
    {
        $response = $this->client()->get(sprintf(self::ENDPOINT_REQUEST_STATUS, $requestId));

        if (! $response->successful()) {
            $this->logFailure(self::ENDPOINT_REQUEST_STATUS, $response, ['request_id' => $requestId]);

            throw SmsSendingException::fromProviderResponse(
                errorCode: $this->errorCode($response),
                errorMessage: $this->errorMessage($response),
                httpStatus: $response->status(),
            );
        }

        $data = (array) data_get((array) $response->json(), 'data', []);

        return [
            'status' => $this->stringOrNull(data_get($data, 'status')),
            'statusInt' => $this->intOrNull(data_get($data, 'statusInt')),
            'smsItems' => (array) data_get($data, 'smsItems', []),
        ];
    }

    /**
     * ارسال درخواست به مدیانا و تبدیل پاسخ به DTO.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws SmsSendingException
     */
    private function dispatch(string $endpoint, array $payload): SmsSendResult
    {
        $response = $this->client()->post($endpoint, $payload);

        if (! $this->isSuccessful($response)) {
            $this->logFailure($endpoint, $response, [
                'recipient' => $payload['recipients'][0] ?? ($payload['recipient'] ?? null),
                'pattern_code' => $payload['patternCode'] ?? null,
            ]);

            throw SmsSendingException::fromProviderResponse(
                errorCode: $this->errorCode($response),
                errorMessage: $this->errorMessage($response),
                httpStatus: $response->status(),
            );
        }

        return SmsSendResult::fromResponse((array) $response->json());
    }

    /**
     * تشخیص موفقیت بر اساس پوشش پاسخ مدیانا.
     */
    private function isSuccessful(Response $response): bool
    {
        if (! $response->successful()) {
            return false;
        }

        $succeed = $response->json('data.succeed');

        if ($succeed !== null) {
            return (bool) $succeed;
        }

        return $response->json('meta.code') === 'OK';
    }

    /**
     * ساخت کلاینت HTTP با احراز هویت، تایم‌اوت و تلاش مجدد.
     *
     * تلاش مجدد تنها برای خطاهای اتصال انجام می‌شود و خطاهای منطقی
     * سرویس‌دهنده (مانند عدم موجودی کیف پول) تکرار نمی‌شوند.
     */
    private function client(): PendingRequest
    {
        return Http::baseUrl((string) $this->configValue('base_url'))
            ->withHeaders([
                'X-API-KEY' => (string) $this->configValue('api_key'),
                'Accept' => 'application/json',
            ])
            ->timeout((int) $this->configValue('timeout', 10))
            ->connectTimeout((int) $this->configValue('connect_timeout', 5))
            ->retry(
                (int) $this->configValue('retry_times', 2),
                (int) $this->configValue('retry_sleep_ms', 200),
                throw: false,
            );
    }

    /**
     * تبدیل شماره به فرمت محلی مورد انتظار مدیانا (09xxxxxxxxx).
     */
    private function normalizeMobile(string $mobile): string
    {
        return MobileNumberNormalizer::toLocal($mobile);
    }

    /**
     * خواندن مقدار از setting (DB) با فال‌بک به config (env).
     */
    private function configValue(string $key, mixed $default = null): mixed
    {
        return Setting::getValue("mediana.{$key}", config("mediana.{$key}", $default));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logFailure(string $endpoint, Response $response, array $context = []): void
    {
        Log::warning('mediana.sms_send_failed', [
            ...$context,
            'endpoint' => $endpoint,
            'http_status' => $response->status(),
            'error_code' => $this->errorCode($response),
            'body' => (array) $response->json(),
        ]);
    }

    private function errorCode(Response $response): ?string
    {
        $code = $response->json('meta.code');

        if (! is_scalar($code)) {
            return null;
        }

        $code = (string) $code;

        return $code === '' ? null : $code;
    }

    private function errorMessage(Response $response): ?string
    {
        $message = $response->json('meta.errorMessage') ?? $response->json('meta.message');

        return $this->stringOrNull($message);
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
