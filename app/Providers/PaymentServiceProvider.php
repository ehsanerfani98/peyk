<?php

namespace App\Providers;

use App\Services\Payment\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/payment.php', 'payment');

        $this->app->singleton('payment', function ($app) {
            return new PaymentManager($app);
        });

        $this->app->singleton(PaymentManager::class, function ($app) {
            return $app->make('payment');
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/payment.php' => config_path('payment.php'),
        ], 'payment-config');
    }
}
