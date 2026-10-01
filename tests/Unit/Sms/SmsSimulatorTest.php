<?php

use App\Helpers\SmsSender;
use App\Models\Setting;
use App\Models\SmsMessage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| تست جداسازی شبیه‌ساز پیامک از پترن‌های واقعی
|--------------------------------------------------------------------------
|
| این تست عمداً در پوشه Unit قرار دارد و از RefreshDatabase استفاده نمی‌کند،
| زیرا میکریشن‌های پروژه شامل spatial index هستند و اجرای کل میکریشن‌ها روی
| SQLite (پیکربندی phpunit.xml) ممکن نیست. در اینجا فقط جدول‌های settings و
| sms_messages ساخته می‌شوند.
|
*/

uses(TestCase::class);

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

    if (! Schema::hasTable('sms_messages')) {
        Schema::create('sms_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('receiver', 20)->index();
            $table->string('sender', 32)->nullable();
            $table->text('content');
            $table->string('pattern_code')->nullable();
            $table->timestamps();
        });
    }
});

it('uses the logical key in simulator mode even when the real pattern is empty', function () {
    config()->set('mediana.pattern_order_searching', null);
    Setting::setValue('ippanel.pattern_order_searching', '', 'sms');
    Setting::setValue('sms_mode', 'simulator', 'sms');

    expect(app(SmsSender::class)->resolvePatternCode('pattern_order_searching'))
        ->toBe('pattern_order_searching');
});

it('stores a simulator message even when the real pattern is empty', function () {
    config()->set('mediana.pattern_order_searching', null);
    Setting::setValue('ippanel.pattern_order_searching', '', 'sms');
    Setting::setValue('sms_mode', 'simulator', 'sms');

    app(SmsSender::class)->send(
        localMobile: '09120000000',
        paramValue: 123,
        patternCode: 'pattern_order_searching',
        paramKey: 'code',
    );

    expect(SmsMessage::count())->toBe(1);

    $message = SmsMessage::query()->firstOrFail();

    expect($message->receiver)->toBe('09120000000')
        ->and($message->pattern_code)->toBe('pattern_order_searching')
        ->and($message->content)->toContain('123')
        ->and($message->content)->toContain('جستجوی پیک');
});

it('falls back to null for an unconfigured pattern in real mode', function () {
    config()->set('mediana.pattern_order_searching', null);
    Setting::setValue('ippanel.pattern_order_searching', '', 'sms');
    Setting::setValue('sms_mode', 'real', 'sms');

    expect(app(SmsSender::class)->resolvePatternCode('pattern_order_searching'))
        ->toBeNull();
});

it('defines a simulator template for every logical notification key', function (string $key) {
    expect(config("mediana.simulator_templates.{$key}"))
        ->toBeString()
        ->not->toBeEmpty();
})->with([
    'otp_pattern_code',
    'verification_link_pattern_code',
    'pattern_order_searching',
    'pattern_courier_not_found',
    'pattern_order_courier_assigned',
    'pattern_order_waiting_pickup',
    'pattern_order_picked_up',
    'pattern_order_in_transit',
    'pattern_order_delivered',
    'pattern_order_cancelled',
    'pattern_sender_order_waiting_pickup',
    'pattern_sender_order_picked_up',
    'pattern_sender_order_in_transit',
    'pattern_sender_order_delivered',
    'pattern_sender_order_cancelled',
    'pattern_courier_offer',
    'pattern_courier_cancelled',
    'pattern_system_cancellation',
    'pattern_survey_link',
    'pattern_cash_on_delivery',
]);
