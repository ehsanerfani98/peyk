<?php

namespace App\Services\Payment\Drivers;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DTO\PaymentRequestData;
use App\Services\Payment\DTO\PaymentRequestResult;
use App\Services\Payment\DTO\PaymentVerifyResult;
use Illuminate\Support\Facades\Http;

/**
 * درایور درگاه پرداخت زیبال (Zibal Payment Gateway)
 *
 * مستندات: https://help.zibal.ir/facilities/
 */
class ZibalDriver implements PaymentGatewayInterface
{
    protected string $merchant;

    protected bool $sandbox;

    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->sandbox = (bool) ($config['sandbox'] ?? false);

        // در حالت سندباکس، مقدار مرچنت باید همیشه "zibal" باشد
        $this->merchant = $this->sandbox ? 'zibal' : (string) ($config['merchant'] ?? '');

        $this->baseUrl = rtrim($config['base_url'] ?? 'https://gateway.zibal.ir', '/');
    }

    public function getName(): string
    {
        return 'zibal';
    }

    public function request(PaymentRequestData $data): PaymentRequestResult
    {
        $payload = array_filter([
            'merchant' => $this->merchant,
            'amount' => $data->amount,
            'callbackUrl' => $data->callbackUrl,
            'description' => $data->description,
            'orderId' => $data->orderId,
            'mobile' => $data->mobile,
        ], static fn ($value) => $value !== null);

        // ادغام هرگونه فیلد اضافی سفارشی (مانند allowedCards, feeMode, ...)
        $payload = array_merge($payload, $data->metadata);

        $response = Http::acceptJson()
            ->asJson()
            ->post($this->baseUrl.'/v1/request', $payload);

        $body = $response->json();
        $result = $body['result'] ?? null;

        if (! $response->successful() || $result !== 100) {
            return new PaymentRequestResult(
                success: false,
                resultCode: $result,
                message: $body['message'] ?? null,
                raw: $body ?? [],
            );
        }

        $trackId = (string) $body['trackId'];

        return new PaymentRequestResult(
            success: true,
            authority: $trackId,
            redirectUrl: $this->getRedirectUrl($trackId),
            resultCode: $result,
            message: $body['message'] ?? null,
            raw: $body,
        );
    }

    public function getRedirectUrl(string $authority): string
    {
        return $this->baseUrl.'/start/'.$authority;
    }

    public function verify(string $authority, ?int $amount = null): PaymentVerifyResult
    {
        $payload = [
            'merchant' => $this->merchant,
            'trackId' => $authority,
        ];

        $response = Http::acceptJson()
            ->asJson()
            ->post($this->baseUrl.'/v1/verify', $payload);

        $body = $response->json();
        $result = $body['result'] ?? null;

        // 100: تایید موفق تراکنش | 201: تراکنش پیش‌تر تایید شده است
        $success = in_array($result, [100, 201], true);
        $alreadyVerified = $result === 201;

        if (! $success) {
            return new PaymentVerifyResult(
                success: false,
                resultCode: $result,
                message: $body['message'] ?? null,
                raw: $body ?? [],
            );
        }

        // در صورت نیاز به تایید مبلغ در سمت پروژه
        if ($amount !== null && isset($body['amount']) && (int) $body['amount'] !== $amount) {
            return new PaymentVerifyResult(
                success: false,
                amount: (int) $body['amount'],
                resultCode: $result,
                message: 'مبلغ تراکنش با مبلغ ثبت شده مطابقت ندارد.',
                raw: $body,
            );
        }

        return new PaymentVerifyResult(
            success: true,
            amount: isset($body['amount']) ? (int) $body['amount'] : null,
            refId: isset($body['refNumber']) ? (string) $body['refNumber'] : null,
            cardNumber: $body['cardNumber'] ?? null,
            resultCode: $result,
            message: $body['message'] ?? null,
            alreadyVerified: $alreadyVerified,
            raw: $body,
        );
    }
}
