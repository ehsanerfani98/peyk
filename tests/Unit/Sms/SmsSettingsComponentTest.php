<?php

use App\Models\Setting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| تست کامپوننت تنظیمات پیامک پنل مدیریت
|--------------------------------------------------------------------------
|
| این تست رگرسیون برای خطای زیر است:
|   "Cannot assign null to property ...::$api_key of type string"
| که زمانی رخ می‌داد که کلیدهای MEDIANA_* در .env سرور تعریف نشده باشند و
| مقدار config برابر null برگردد.
|
*/

uses(TestCase::class);

const SMS_SETTINGS_COMPONENT_FILE = 'resources/views/components/admin/settings/⚡sms/sms.php';

/**
 * مسیر کامل فایل کامپوننت تنظیمات پیامک.
 */
function smsSettingsComponentPath(): string
{
    return base_path(SMS_SETTINGS_COMPONENT_FILE);
}

/**
 * کلاس کامپوننت تنظیمات پیامک.
 *
 * فایل کامپوننت یک کلاس Livewire به صورت anonymous تعریف می‌کند و نمونه را
 * بازنمی‌گرداند؛ بنابراین کلاس با تطبیق نام فایل پیدا می‌شود.
 */
function smsSettingsComponentClass(): string
{
    static $class = null;

    if ($class === null) {
        require_once smsSettingsComponentPath();

        $class = (string) collect(get_declared_classes())->last(
            fn (string $candidate): bool => str_ends_with(
                str_replace('\\', '/', (string) (new ReflectionClass($candidate))->getFileName()),
                str_replace('\\', '/', SMS_SETTINGS_COMPONENT_FILE),
            ),
        );
    }

    return $class;
}

/**
 * وضعیت پراپرتی‌های کامپوننت تنظیمات پیامک پس از اجرای mount.
 *
 * @param  array<int, string>  $properties
 * @return array<string, mixed>
 */
function smsSettingsComponentState(array $properties): array
{
    $class = smsSettingsComponentClass();
    $component = new $class;

    (new ReflectionMethod($component, 'mount'))->invoke($component);

    $state = [];

    foreach ($properties as $property) {
        $state[$property] = (new ReflectionProperty($component, $property))->getValue($component);
    }

    return $state;
}

beforeEach(function () {
    if (! Schema::hasTable('settings')) {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group');
            $table->timestamps();
        });
    }
});

it('mounts the sms settings component when mediana config values are missing', function () {
    config()->set('mediana.base_url', null);
    config()->set('mediana.api_key', null);
    config()->set('mediana.type', null);
    config()->set('mediana.sending_number', null);
    config()->set('mediana.otp_pattern_code', null);
    config()->set('mediana.pattern_order_searching', null);
    config()->set('mediana.pattern_sender_order_delivered', null);
    config()->set('mediana.verification_link_param_key', null);
    config()->set('mediana.cash_on_delivery_param_key', null);

    $state = smsSettingsComponentState([
        'api_key',
        'base_url',
        'sending_number',
        'otp_pattern_code',
        'pattern_order_searching',
        'pattern_sender_delivered',
        'type',
        'verification_link_param_key',
        'cash_on_delivery_param_key',
    ]);

    expect($state['api_key'])->toBe('')
        ->and($state['base_url'])->toBe('')
        ->and($state['sending_number'])->toBe('')
        ->and($state['otp_pattern_code'])->toBe('')
        ->and($state['pattern_order_searching'])->toBe('')
        ->and($state['pattern_sender_delivered'])->toBe('')
        ->and($state['type'])->toBe('Informational')
        ->and($state['verification_link_param_key'])->toBe('code')
        ->and($state['cash_on_delivery_param_key'])->toBe('code');
});

it('prefers stored settings over the config fallback', function () {
    Setting::setValue('mediana.api_key', 'stored-api-key', 'sms');
    Setting::setValue('mediana.type', 'PromotionalAll', 'sms');

    $state = smsSettingsComponentState(['api_key', 'type']);

    expect($state['api_key'])->toBe('stored-api-key')
        ->and($state['type'])->toBe('PromotionalAll');
});

it('reads pattern codes and param keys stored under the legacy ippanel prefix', function () {
    // شرایط سرور: مقادیر قبلی در دیتابیس با پیشوند ippanel. ثبت شده‌اند
    config()->set('mediana.pattern_order_searching', null);
    config()->set('mediana.otp_pattern_code', null);
    config()->set('mediana.order_status_param_key', null);

    Setting::setValue('ippanel.pattern_order_searching', 'z9y4w1v6n2abc', 'sms');
    Setting::setValue('ippanel.otp_pattern_code', 'x7km2n9p4qrst', 'sms');
    Setting::setValue('ippanel.order_status_param_key', 'verification-code', 'sms');

    $state = smsSettingsComponentState([
        'pattern_order_searching',
        'otp_pattern_code',
        'order_status_param_key',
    ]);

    expect($state['pattern_order_searching'])->toBe('z9y4w1v6n2abc')
        ->and($state['otp_pattern_code'])->toBe('x7km2n9p4qrst')
        ->and($state['order_status_param_key'])->toBe('verification-code');
});
