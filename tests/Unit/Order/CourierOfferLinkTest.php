<?php

use App\Models\Order;
use App\Models\User;
use App\Services\Order\CourierOfferService;
use App\Services\Order\Exceptions\OrderStateException;
use App\Services\Order\OrderNotificationService;
use App\Services\Sms\Contracts\SmsProvider;
use App\Services\Sms\DTO\SmsSendResult;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| تست لینک تایید پیشنهاد سفارش پیک
|--------------------------------------------------------------------------
|
| دو نکته‌ای که این تست‌ها تضمین می‌کنند:
|   ۱. پیامک پیشنهاد برای هر پیشنهاد فقط یک‌بار ارسال می‌شود.
|   ۲. لینک ارسالی از نوع GET و قابل باز شدن در مرورگر است (نه اندپوینت POST اپ).
|
| مطابق کنوانسیون پوشه Unit این پروژه از RefreshDatabase استفاده نمی‌شود، چون
| میکریشن‌های پروژه شامل spatial index هستند و روی SQLite (پیکربندی phpunit.xml)
| قابل اجرا نیستند؛ بنابراین تنها جدول‌های لازم ساخته می‌شوند.
|
*/

uses(TestCase::class);

beforeEach(function () {
    createCourierOfferLinkSchema();

    config()->set('mediana.pattern_courier_offer', 'courier-offer-pattern-code');
    config()->set('mediana.courier_offer_param_key', 'code');
    config()->set('mediana.pattern_order_searching', null);
    config()->set('mediana.pattern_order_courier_assigned', null);
    config()->set('mediana.pattern_order_waiting_pickup', null);
    config()->set('mediana.pattern_order_cancelled', null);

    // حالت واقعی + سرویس‌دهنده جعلی: هیچ پیامکی به بیرون ارسال نمی‌شود و
    // فراخوانی‌ها قابل شمارش هستند.
    config()->set('sms_simulator.mode', 'real');
    app()->instance(SmsProvider::class, fakeSmsProvider());
});

it('sends the courier offer link sms only once per offer', function () {
    $order = createCourierOfferLinkOrder();
    $token = $order->courier_offer_token;

    $provider = fakeSmsProvider();
    app()->instance(SmsProvider::class, $provider);

    $service = app(OrderNotificationService::class);
    $service->sendCourierOfferLink($order->fresh());
    // اجرای تکراری (مثلاً اجرای دوباره Job) نباید پیامک دوم بفرستد
    $service->sendCourierOfferLink($order->fresh());

    expect($provider->patternCalls)->toHaveCount(1);

    $call = $provider->patternCalls[0];
    $link = (string) ($call['parameters']['code'] ?? '');

    expect($call['mobile'])->toBe('09120000002')
        ->and($call['pattern'])->toBe('courier-offer-pattern-code')
        ->and($link)->toStartWith('http')
        ->and($link)->toContain('/courier-offer/'.$token)
        ->and($link)->not->toContain('/api/orders/')
        ->and($order->fresh()->courier_offer_sms_sent_at)->not->toBeNull();
});

it('sends a new sms for a new offer of the same order', function () {
    $order = createCourierOfferLinkOrder();

    $provider = fakeSmsProvider();
    app()->instance(SmsProvider::class, $provider);

    $service = app(OrderNotificationService::class);
    $service->sendCourierOfferLink($order->fresh());

    // پیشنهاد جدید برای همان سفارش: توکن تازه و وضعیت ارسال پیامک صفر می‌شود
    $order->update(['courier_offer_token' => 'offer-token-2', 'courier_offer_sms_sent_at' => null]);
    $service->sendCourierOfferLink($order->fresh());

    expect($provider->patternCalls)->toHaveCount(2)
        ->and((string) $provider->patternCalls[1]['parameters']['code'])->toContain('/courier-offer/offer-token-2');
});

it('accepts the offer by the sms link token', function () {
    $order = createCourierOfferLinkOrder();
    $courierId = $order->courier_id;

    $offerService = app(CourierOfferService::class);

    expect($offerService->offerLinkState($order))->toBe('offered');

    $accepted = $offerService->acceptByOfferToken($order->courier_offer_token);

    expect($accepted->id)->toBe($order->id);

    $order->refresh();

    expect($order->status)->toBe('WAITING_PICKUP')
        ->and($order->courier_id)->toBe($courierId)
        ->and($order->assigned_at)->not->toBeNull()
        ->and($order->courier_offered_at)->toBeNull()
        ->and($offerService->offerLinkState($order))->toBe('accepted');
});

it('rejects an invalid or reused offer link token', function () {
    $order = createCourierOfferLinkOrder();
    $offerService = app(CourierOfferService::class);

    expect(fn () => $offerService->acceptByOfferToken('not-a-real-token'))
        ->toThrow(OrderStateException::class);

    $offerService->acceptByOfferToken($order->courier_offer_token);

    // استفاده دوباره از همان لینک: سفارش دیگر در وضعیت پیشنهاد نیست
    expect(fn () => $offerService->acceptByOfferToken($order->courier_offer_token))
        ->toThrow(OrderStateException::class);
});

it('treats an offer link as expired when the offer is no longer active', function () {
    $order = createCourierOfferLinkOrder();

    $order->update(['courier_id' => null, 'courier_offered_at' => null]);
    $order->changeStatusBySystem('SEARCHING_COURIER');

    expect(app(CourierOfferService::class)->offerLinkState($order->fresh()))->toBe('expired');
});

it('accepts the offer from the browser confirmation page', function () {
    $order = createCourierOfferLinkOrder();

    $this->withoutVite();

    Livewire::test('courier.offer', ['token' => $order->courier_offer_token])
        ->assertSee('تایید سفارش')
        ->call('confirm')
        ->assertSee('سفارش با موفقیت پذیرفته شد');

    expect($order->fresh()->status)->toBe('WAITING_PICKUP');
});

it('shows an error on the browser page for an unknown token', function () {
    $this->withoutVite();

    Livewire::test('courier.offer', ['token' => 'not-a-real-token'])
        ->assertSee('این لینک پیشنهاد سفارش معتبر نیست یا منقضی شده است.')
        ->assertDontSee('تایید سفارش');
});

/**
 * سرویس‌دهنده پیامک جعلی که فراخوانی‌های ارسال را ثبت می‌کند.
 */
function fakeSmsProvider(): object
{
    return new class implements SmsProvider
    {
        /** @var array<int, array<string, mixed>> */
        public array $patternCalls = [];

        public function sendPattern(
            string $localMobile,
            string $patternCode,
            array $parameters = [],
            ?string $type = null,
        ): SmsSendResult {
            $this->patternCalls[] = [
                'mobile' => $localMobile,
                'pattern' => $patternCode,
                'parameters' => $parameters,
            ];

            return fakeSmsResult();
        }

        public function sendOtp(string $localMobile, string $otpCode, string $patternCode): SmsSendResult
        {
            return fakeSmsResult();
        }

        public function requestStatus(string $requestId): array
        {
            return ['status' => 'PendingApproval', 'statusInt' => 2, 'smsItems' => []];
        }
    };
}

/**
 * نتیجه موفق جعلی ارسال پیامک.
 */
function fakeSmsResult(): SmsSendResult
{
    return new SmsSendResult(true, 187484315, '187484315', 'ok', 'PendingApproval', 2, 0.0);
}

/**
 * ساخت حداقلی جدول‌های لازم برای تست‌های لینک پیشنهاد پیک.
 */
function createCourierOfferLinkSchema(): void
{
    if (! Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('orders')) {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->boolean('sender_is_customer')->default(false);
            $table->string('sender_name')->nullable();
            $table->string('sender_mobile')->nullable();
            $table->text('sender_address')->nullable();
            $table->decimal('sender_lat', 10, 7)->nullable();
            $table->decimal('sender_lng', 10, 7)->nullable();
            $table->boolean('receiver_is_customer')->default(false);
            $table->string('receiver_name')->nullable();
            $table->string('receiver_mobile')->nullable();
            $table->text('receiver_address')->nullable();
            $table->decimal('receiver_lat', 10, 7)->nullable();
            $table->decimal('receiver_lng', 10, 7)->nullable();
            $table->text('package_description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('status')->default('CREATED');
            $table->unsignedBigInteger('courier_id')->nullable();
            $table->string('cancelled_by')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamp('courier_search_started_at')->nullable();
            $table->timestamp('courier_offered_at')->nullable();
            $table->string('courier_offer_token', 64)->nullable();
            $table->timestamp('courier_offer_sms_sent_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('order_status_histories')) {
        Schema::create('order_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    if (! Schema::hasTable('courier_current_locations')) {
        Schema::create('courier_current_locations', function (Blueprint $table): void {
            $table->unsignedBigInteger('courier_id')->primary();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    if (! Schema::hasTable('jobs')) {
        Schema::create('jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('queue')->nullable();
            $table->text('payload')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at')->nullable();
            $table->unsignedInteger('created_at')->nullable();
        });
    }

    if (! Schema::hasTable('settings')) {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });
    }
}

/**
 * ساخت مشتری، پیک و سفارش در وضعیت پیشنهاد به پیک (COURIER_OFFERED).
 */
function createCourierOfferLinkOrder(): Order
{
    $customer = User::create([
        'name' => 'Customer',
        'mobile' => '09120000001',
        'password' => 'secret',
    ]);

    $courier = User::create([
        'name' => 'Courier',
        'mobile' => '09120000002',
        'password' => 'secret',
    ]);

    return Order::create([
        'customer_id' => $customer->id,
        'sender_name' => 'Sender',
        'sender_mobile' => '09120000003',
        'sender_address' => 'Sender Address',
        'sender_lat' => 35.6892,
        'sender_lng' => 51.3890,
        'receiver_name' => 'Receiver',
        'receiver_mobile' => '09120000004',
        'receiver_address' => 'Receiver Address',
        'receiver_lat' => 35.7000,
        'receiver_lng' => 51.4000,
        'price' => 50000,
        'status' => 'COURIER_OFFERED',
        'courier_id' => $courier->id,
        'courier_offered_at' => now(),
        'courier_offer_token' => 'offer-token-1',
        'courier_search_started_at' => now(),
    ]);
}
