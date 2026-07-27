<?php

namespace App\Facades;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static PaymentGatewayInterface driver(?string $name = null)
 * @method static \App\Services\Payment\DTO\PaymentRequestResult request(\App\Services\Payment\DTO\PaymentRequestData $data)
 * @method static string getRedirectUrl(string $authority)
 * @method static \App\Services\Payment\DTO\PaymentVerifyResult verify(string $authority, ?int $amount = null)
 *
 * @see PaymentManager
 */
class Payment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'payment';
    }
}
