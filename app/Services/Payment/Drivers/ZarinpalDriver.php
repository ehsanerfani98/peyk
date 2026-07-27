<?php

namespace App\Services\Payment\Drivers;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DTO\PaymentRequestData;
use App\Services\Payment\DTO\PaymentRequestResult;
use App\Services\Payment\DTO\PaymentVerifyResult;
use App\Services\Payment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;

/**
 * درایور درگاه پرداخت زرین‌پال (Zarinpal Payment Gateway - API v4)
 *
 * مستندات: https://www.zarinpal.com/docs/paymentGateway/
 */
class ZarinpalDriver implements PaymentGatewayInterface
{
    protected string $merchantId;

    protected bool $sandbox;

    protected string $baseUrl;

    protected string $startPayUrl;

    protected string $currency;

    public function __construct(array $config)
    {
        $this->merchantId = (string) ($config['merchant_id'] ?? '');
        $this->sandbox = (bool) ($config['sandbox'] ?? false);
        $this->currency = $config['currency'] ?? 'IRT';

        $this->baseUrl = $this->sandbox
            ? ($config['sandbox_base_url'] ?? 'https://sandbox.zarinpal.com/pg/v4/payment/')
            : ($config['base_url'] ?? 'https://api.zarinpal.com/pg/v4/payment/');

        $this->startPayUrl = $this->sandbox
            ? ($config['sandbox_start_pay_url'] ?? 'https://sandbox.zarinpal.com/pg/StartPay/')
            : ($config['start_pay_url'] ?? 'https://www.zarinpal.com/pg/StartPay/');
    }

    public function getName(): string
    {
        return 'zarinpal';
    }

    public function request(PaymentRequestData $data): PaymentRequestResult
    {
        $metadata = $data->metadata;

        if ($data->mobile) {
            $metadata['mobile'] = $data->mobile;
        }

        if ($data->email) {
            $metadata['email'] = $data->email;
        }

        if ($data->orderId) {
            $metadata['order_id'] = $data->orderId;
        }

        $payload = [
            'merchant_id' => $this->merchantId,
            'amount' => $data->amount,
            'callback_url' => $data->callbackUrl,
            'description' => $data->description ?? 'پرداخت آنلاین',
            'currency' => $this->currency,
        ];

        if (! empty($metadata)) {
            $payload['metadata'] = $metadata;
        }

        $response = Http::acceptJson()
            ->asJson()
            ->post($this->baseUrl.'request.json', $payload);

        $body = $response->json();
        $result = $body['data']['code'] ?? ($body['errors']['code'] ?? null);
        $message = $body['data']['message'] ?? ($body['errors']['message'] ?? null);

        if (! $response->successful() || $result !== 100) {
            return new PaymentRequestResult(
                success: false,
                resultCode: is_int($result) ? $result : null,
                message: is_array($message) ? implode(', ', $message) : $message,
                raw: $body ?? [],
            );
        }

        $authority = $body['data']['authority'];

        return new PaymentRequestResult(
            success: true,
            authority: $authority,
            redirectUrl: $this->getRedirectUrl($authority),
            resultCode: $result,
            message: $message,
            raw: $body,
        );
    }

    public function getRedirectUrl(string $authority): string
    {
        return rtrim($this->startPayUrl, '/').'/'.$authority;
    }

    public function verify(string $authority, ?int $amount = null): PaymentVerifyResult
    {
        if ($amount === null) {
            throw new PaymentException('برای تایید تراکنش زرین‌پال، ارسال مبلغ (amount) الزامی است.');
        }

        $payload = [
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'authority' => $authority,
        ];

        $response = Http::acceptJson()
            ->asJson()
            ->post($this->baseUrl.'verify.json', $payload);

        $body = $response->json();
        $result = $body['data']['code'] ?? ($body['errors']['code'] ?? null);
        $message = $body['data']['message'] ?? ($body['errors']['message'] ?? null);

        // 100: تایید موفق تراکنش | 101: تراکنش پیش‌تر تایید شده است
        $success = in_array($result, [100, 101], true);
        $alreadyVerified = $result === 101;

        if (! $success) {
            return new PaymentVerifyResult(
                success: false,
                resultCode: is_int($result) ? $result : null,
                message: is_array($message) ? implode(', ', $message) : $message,
                raw: $body ?? [],
            );
        }

        return new PaymentVerifyResult(
            success: true,
            amount: $body['data']['amount'] ?? $amount,
            refId: isset($body['data']['ref_id']) ? (string) $body['data']['ref_id'] : null,
            cardNumber: $body['data']['card_pan'] ?? null,
            resultCode: $result,
            message: $message,
            alreadyVerified: $alreadyVerified,
            raw: $body,
        );
    }
}
