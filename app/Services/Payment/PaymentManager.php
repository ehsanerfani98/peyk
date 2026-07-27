<?php

namespace App\Services\Payment;

use App\Models\Setting;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Drivers\ZarinpalDriver;
use App\Services\Payment\Drivers\ZibalDriver;
use App\Services\Payment\Exceptions\PaymentException;
use Illuminate\Contracts\Foundation\Application;

/**
 * مدیریت درایورهای درگاه پرداخت (Zarinpal / Zibal) با استفاده از الگوی Manager لاراول.
 *
 * استفاده:
 *   Payment::driver('zarinpal')->request(...);
 *   Payment::driver('zibal')->request(...);
 *   Payment::request(...); // استفاده از درایور پیش‌فرض
 */
class PaymentManager
{
    protected array $drivers = [];

    public function __construct(protected Application $app) {}

    /**
     * دریافت نمونه‌ی درایور مورد نظر (در صورت عدم مشخص کردن، درایور پیش‌فرض استفاده می‌شود).
     */
    public function driver(?string $name = null): PaymentGatewayInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        if (! isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    protected function createDriver(string $name): PaymentGatewayInterface
    {
        $config = config("payment.drivers.{$name}");

        if ($config === null) {
            throw new PaymentException("درایور پرداخت [{$name}] در فایل config/payment.php تعریف نشده است.");
        }

        // ادغام تنظیمات دیتابیس با کانفیگ (اولویت با دیتابیس)
        $sandboxMode = Setting::getValue('payment_sandbox_mode', 'disabled');
        $config['sandbox'] = $sandboxMode === 'enabled';

        if ($name === 'zarinpal') {
            $config['merchant_id'] = Setting::getValue('zarinpal_merchant_id', $config['merchant_id'] ?? '');
            $config['currency'] = Setting::getValue('zarinpal_currency', $config['currency'] ?? 'IRT');
            $config['base_url'] = Setting::getValue('zarinpal_base_url', $config['base_url'] ?? 'https://api.zarinpal.com/pg/v4/payment/');
            $config['sandbox_base_url'] = Setting::getValue('zarinpal_sandbox_base_url', $config['sandbox_base_url'] ?? 'https://sandbox.zarinpal.com/pg/v4/payment/');
            $config['start_pay_url'] = Setting::getValue('zarinpal_start_pay_url', $config['start_pay_url'] ?? 'https://www.zarinpal.com/pg/StartPay/');
            $config['sandbox_start_pay_url'] = Setting::getValue('zarinpal_sandbox_start_pay_url', $config['sandbox_start_pay_url'] ?? 'https://sandbox.zarinpal.com/pg/StartPay/');
        } elseif ($name === 'zibal') {
            $config['merchant'] = Setting::getValue('zibal_merchant', $config['merchant'] ?? '');
            $config['base_url'] = Setting::getValue('zibal_base_url', $config['base_url'] ?? 'https://gateway.zibal.ir');
        }

        return match ($name) {
            'zarinpal' => new ZarinpalDriver($config),
            'zibal' => new ZibalDriver($config),
            default => throw new PaymentException("درایور پرداخت [{$name}] پشتیبانی نمی‌شود."),
        };
    }

    protected function getDefaultDriver(): string
    {
        return Setting::getValue('payment_default_driver', config('payment.default', 'zarinpal'));
    }

    /**
     * فراخوانی متدهای درایور پیش‌فرض به صورت مستقیم روی منیجر.
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
