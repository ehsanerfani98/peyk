<?php

use App\Services\Sms\DTO\SmsSendResult;
use App\Services\Sms\Exceptions\SmsSendingException;
use App\Services\Sms\MedianaSmsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| تست سرویس پیامک مدیانا
|--------------------------------------------------------------------------
|
| این تست عمداً در پوشه Unit قرار دارد و از RefreshDatabase استفاده نمی‌کند،
| زیرا میکریشن‌های پروژه شامل spatial index هستند و اجرای کل میکریشن‌ها روی
| SQLite (پیکربندی phpunit.xml) ممکن نیست. در اینجا تنها جدول settings ساخته
| می‌شود تا Setting::getValue بتواند مقدار پیش‌فرض config را برگرداند.
|
*/

uses(TestCase::class);

/**
 * پاسخ موفق استاندارد مدیانا (مطابق مستندات).
 *
 * @return array<string, mixed>
 */
function medianaSuccessPayload(): array
{
    return [
        'meta' => [
            'requestId' => '929de9cf-e4aa-4eba-af85-10eb3a4b2b3d',
            'code' => 'OK',
            'errorMessage' => null,
            'errors' => null,
        ],
        'data' => [
            'succeed' => true,
            'requestId' => 187484315,
            'requestCode' => '187484315',
            'message' => 'در حال ساخت',
            'status' => 'PendingApproval',
            'statusInt' => 2,
            'totalPrice' => 2284,
            'smsItems' => [],
            'clientRef' => null,
        ],
        'pagination' => null,
    ];
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

    config()->set('mediana.base_url', 'https://api.mediana.ir');
    config()->set('mediana.api_key', 'test-api-key');
    config()->set('mediana.type', 'Informational');
    config()->set('mediana.sending_number', null);
    config()->set('mediana.timeout', 5);
    config()->set('mediana.connect_timeout', 3);
    config()->set('mediana.retry_times', 1);
    config()->set('mediana.retry_sleep_ms', 0);

    Http::preventStrayRequests();
});

it('sends a pattern sms with the api key header and a local mobile number', function () {
    Http::fake(['*' => Http::response(medianaSuccessPayload())]);

    $result = app(MedianaSmsService::class)->sendPattern(
        localMobile: '+989120000000',
        patternCode: '110022',
        parameters: ['code' => '48213'],
    );

    expect($result)->toBeInstanceOf(SmsSendResult::class)
        ->and($result->succeed)->toBeTrue()
        ->and($result->trackingId())->toBe('187484315')
        ->and($result->status)->toBe('PendingApproval')
        ->and($result->statusInt)->toBe(2);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.mediana.ir/sms/v1/send/pattern'
        && $request->hasHeader('X-API-KEY', 'test-api-key')
        && $request['type'] === 'Informational'
        && $request['recipients'] === ['09120000000']
        && $request['patternCode'] === '110022'
        && $request['parameters'] === ['code' => '48213']);
});

it('sends otp through the dedicated endpoint', function () {
    Http::fake(['*' => Http::response(medianaSuccessPayload())]);

    app(MedianaSmsService::class)->sendOtp(
        localMobile: '09120000000',
        otpCode: '48213',
        patternCode: '110022',
    );

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.mediana.ir/sms/v1/send/otp'
        && $request['recipient'] === '09120000000'
        && $request['otpCode'] === '48213'
        && $request['patternCode'] === '110022'
        && ! isset($request['type']));
});

it('uses the dedicated sending number instead of the message type when configured', function () {
    config()->set('mediana.sending_number', '983000505');

    Http::fake(['*' => Http::response(medianaSuccessPayload())]);

    app(MedianaSmsService::class)->sendPattern('09120000000', '110022', ['code' => '1']);

    Http::assertSent(fn (Request $request): bool => $request['sendingNumber'] === '983000505'
        && ! isset($request['type']));
});

it('honours a per call message type override', function () {
    Http::fake(['*' => Http::response(medianaSuccessPayload())]);

    app(MedianaSmsService::class)->sendPattern('09120000000', '110022', ['code' => '1'], 'PromotionalAll');

    Http::assertSent(fn (Request $request): bool => $request['type'] === 'PromotionalAll');
});

it('omits the parameters key when no parameter is given', function () {
    Http::fake(['*' => Http::response(medianaSuccessPayload())]);

    app(MedianaSmsService::class)->sendPattern('09120000000', '110022');

    Http::assertSent(fn (Request $request): bool => ! isset($request['parameters']));
});

it('throws a mapped exception when the provider rejects the request with an error code', function () {
    Http::fake(['*' => Http::response([
        'meta' => [
            'requestId' => '929de9cf-e4aa-4eba-af85-10eb3a4b2b3d',
            'code' => '1042',
            'errorMessage' => null,
            'errors' => null,
        ],
        'data' => ['succeed' => false],
    ], 400)]);

    $exception = null;

    try {
        app(MedianaSmsService::class)->sendPattern('09120000000', '110022', ['code' => '1']);
    } catch (SmsSendingException $caught) {
        $exception = $caught;
    }

    expect($exception)->toBeInstanceOf(SmsSendingException::class)
        ->and($exception?->getMessage())->toBe('موجودی کیف پول پیامک کافی نیست.')
        ->and($exception?->providerErrorCode())->toBe('1042')
        ->and($exception?->httpStatus())->toBe(400);
});

it('prefers the provider error message over the mapped message', function () {
    Http::fake(['*' => Http::response([
        'meta' => ['code' => '1046', 'errorMessage' => 'پارامترها ناقص است', 'errors' => []],
        'data' => ['succeed' => false],
    ], 400)]);

    expect(fn () => app(MedianaSmsService::class)->sendPattern('09120000000', '110022'))
        ->toThrow(SmsSendingException::class, 'پارامترها ناقص است');
});

it('throws when the request fails even with an http 200 status', function () {
    Http::fake(['*' => Http::response([
        'meta' => ['code' => '1047', 'errorMessage' => null, 'errors' => null],
        'data' => ['succeed' => false],
    ])]);

    expect(fn () => app(MedianaSmsService::class)->sendPattern('09120000000', '110022'))
        ->toThrow(SmsSendingException::class, 'شماره تلفن گیرنده در لیست سیاه قرار دارد.');
});

it('retrieves the delivery status of a send request', function () {
    Http::fake(['*' => Http::response([
        'meta' => ['code' => 'OK', 'errorMessage' => null, 'errors' => null],
        'data' => [
            'status' => 'Delivered',
            'statusInt' => 5,
            'smsItems' => [['smsItemId' => 'abc', 'recipient' => '09120000000']],
        ],
    ])]);

    $status = app(MedianaSmsService::class)->requestStatus('187484315');

    expect($status['status'])->toBe('Delivered')
        ->and($status['statusInt'])->toBe(5)
        ->and($status['smsItems'])->toHaveCount(1);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.mediana.ir/sms/v1/send-requests/status/187484315'
        && $request->hasHeader('X-API-KEY', 'test-api-key'));
});
