<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->overrideConfigFromSettings();
        $this->configureDefaults();
    }

    /**
     * بازنویسی مقادیر config از روی تنظیمات دیتابیس.
     *
     * این متد تضمین می‌کند تمام بخش‌های برنامه (از جمله vendor packages)
     * از مقادیر ذخیره شده در پنل مدیریت استفاده کنند.
     */
    protected function overrideConfigFromSettings(): void
    {
        try {
            // فقط در صورتی که دیتابیس در دسترس باشد اجرا می‌شود
            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            $siteName = Setting::getValue('site_name');
            if ($siteName) {
                config(['app.name' => $siteName]);
            }

            $appDebug = Setting::getValue('app_debug');
            if ($appDebug !== null && $appDebug !== '') {
                config(['app.debug' => $appDebug === 'true']);
            }

            $appUrl = Setting::getValue('app_url');
            if ($appUrl) {
                config(['app.url' => $appUrl]);
            }
        } catch (\Throwable $e) {
            // اگر دیتابیس در دسترس نباشد (مثلاً در حین migration)،
            // از مقادیر پیش‌فرض env استفاده می‌شود
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
