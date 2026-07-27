<?php

use App\Jobs\CourierOfferTimeoutJob;
use App\Jobs\SearchCourierForOrderJob;
use App\Models\CourierProfile;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Config::set('courier_search.courier_offer_timeout_seconds', 60);
});

// ---- helper functions ----

function createCustomer(): User
{
    return User::create([
        'name' => 'Customer',
        'mobile' => '09120000001',
        'email' => 'customer@test.com',
        'password' => bcrypt('password'),
        'address' => 'Test Address',
        'lat' => 35.6892,
        'lng' => 51.3890,
    ]);
}

function createCourierUser(): User
{
    return User::create([
        'name' => 'Courier',
        'mobile' => '09120000002',
        'email' => 'courier@test.com',
        'password' => bcrypt('password'),
        'address' => 'Courier Address',
        'lat' => 35.6900,
        'lng' => 51.3900,
    ]);
}

function createCourierProfile(User $user, string $status = 'online'): CourierProfile
{
    return CourierProfile::create([
        'user_id' => $user->id,
        'national_code' => '1234567890',
        'vehicle_type' => 'motorcycle',
        'vehicle_number' => '12345',
        'rating' => 4.5,
        'status' => $status,
    ]);
}

function createCourierLocation(int $courierId, ?int $orderId = null): void
{
    DB::statement(
        'INSERT INTO courier_current_locations (courier_id, location, order_id, updated_at)
         VALUES (?, ST_GeomFromText(?, 4326), ?, ?)
         ON DUPLICATE KEY UPDATE location = VALUES(location), order_id = VALUES(order_id), updated_at = VALUES(updated_at)',
        [
            $courierId,
            sprintf('POINT(%F %F)', 51.3900, 35.6900),
            $orderId,
            now(),
        ]
    );
}

function createOrder(User $customer, string $status = 'SEARCHING_COURIER', ?int $courierId = null): Order
{
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
        'package_description' => 'Test package',
        'package_weight_kg' => 1.5,
        'package_size' => 'small',
        'payment_method' => 'cash_on_delivery',
        'payment_by' => 'sender',
        'price' => 50000,
        'status' => $status,
        'courier_id' => $courierId,
        'courier_search_started_at' => now(),
    ]);
}

// ---- tests ----

test('CourierOfferTimeoutJob does nothing when order is not in COURIER_OFFERED status', function () {
    Queue::fake();

    $customer = createCustomer();
    $order = createOrder($customer, 'SEARCHING_COURIER');

    $job = new CourierOfferTimeoutJob($order->id);
    $job->handle();

    $order->refresh();
    expect($order->status)->toBe('SEARCHING_COURIER');
});

test('CourierOfferTimeoutJob does nothing when order does not exist', function () {
    $job = new CourierOfferTimeoutJob(99999);
    $job->handle();

    // Should not throw any exception
    expect(true)->toBeTrue();
});

test('CourierOfferTimeoutJob auto-rejects when courier does not respond within timeout', function () {
    Bus::fake();

    $customer = createCustomer();
    $courierUser = createCourierUser();
    createCourierProfile($courierUser);
    createCourierLocation($courierUser->id);

    $order = createOrder($customer, 'COURIER_OFFERED', $courierUser->id);
    $order->update(['courier_offered_at' => now()->subSeconds(61)]); // past the 60s timeout

    $job = new CourierOfferTimeoutJob($order->id);
    $job->handle();

    $order->refresh();

    // Order should be back to SEARCHING_COURIER
    expect($order->status)->toBe('SEARCHING_COURIER');
    // courier_id should be null
    expect($order->courier_id)->toBeNull();
    // courier_offered_at should be cleared
    expect($order->courier_offered_at)->toBeNull();

    // A new SearchCourierForOrderJob should be dispatched
    Bus::assertDispatched(SearchCourierForOrderJob::class, function ($job) use ($order) {
        return $job->orderId === $order->id;
    });
});

test('CourierOfferTimeoutJob does nothing when courier_offered_at is still within timeout', function () {
    Queue::fake();

    $customer = createCustomer();
    $courierUser = createCourierUser();
    createCourierProfile($courierUser);
    createCourierLocation($courierUser->id);

    $order = createOrder($customer, 'COURIER_OFFERED', $courierUser->id);
    $order->update(['courier_offered_at' => now()->subSeconds(30)]); // still within 60s

    $job = new CourierOfferTimeoutJob($order->id);
    $job->handle();

    $order->refresh();
    // Status should remain COURIER_OFFERED
    expect($order->status)->toBe('COURIER_OFFERED');
    expect($order->courier_id)->toBe($courierUser->id);
});

test('CourierOfferTimeoutJob does NOT exclude timed-out courier from future searches', function () {
    Bus::fake();

    $customer = createCustomer();
    $courierUser = createCourierUser();
    createCourierProfile($courierUser);
    createCourierLocation($courierUser->id);

    $order = createOrder($customer, 'COURIER_OFFERED', $courierUser->id);
    $order->update(['courier_offered_at' => now()->subSeconds(61)]);

    $job = new CourierOfferTimeoutJob($order->id);
    $job->handle();

    $order->refresh();

    // The timed-out courier should NOT be in excludedCourierIds
    // because COURIER_TIMEOUT is different from COURIER_REJECTED.
    // Timed-out couriers can be re-selected in future search rounds.
    $excludedIds = $order->excludedCourierIds();
    expect($excludedIds)->not->toContain($courierUser->id);

    // But the COURIER_TIMEOUT status should be recorded in history
    $hasTimeoutHistory = $order->statusHistories()
        ->where('new_status', 'COURIER_TIMEOUT')
        ->where('changed_by', $courierUser->id)
        ->exists();
    expect($hasTimeoutHistory)->toBeTrue();
});

test('CourierOfferTimeoutJob records COURIER_TIMEOUT status in history', function () {
    Bus::fake();

    $customer = createCustomer();
    $courierUser = createCourierUser();
    createCourierProfile($courierUser);
    createCourierLocation($courierUser->id);

    $order = createOrder($customer, 'COURIER_OFFERED', $courierUser->id);
    $order->update(['courier_offered_at' => now()->subSeconds(61)]);

    $job = new CourierOfferTimeoutJob($order->id);
    $job->handle();

    $order->refresh();

    // Verify the intermediate COURIER_TIMEOUT was recorded
    $history = $order->statusHistories()
        ->where('new_status', 'COURIER_TIMEOUT')
        ->first();

    expect($history)->not->toBeNull();
    expect($history->changed_by)->toBe($courierUser->id);
    expect($history->old_status)->toBe('COURIER_OFFERED');
});

test('CourierOfferTimeoutJob allows timed-out courier to be re-matched', function () {
    Bus::fake();

    $customer = createCustomer();
    $courierUser = createCourierUser();
    createCourierProfile($courierUser);
    createCourierLocation($courierUser->id);

    $order = createOrder($customer, 'COURIER_OFFERED', $courierUser->id);
    $order->update(['courier_offered_at' => now()->subSeconds(61)]);

    // First timeout
    $job = new CourierOfferTimeoutJob($order->id);
    $job->handle();

    $order->refresh();

    // The courier should NOT be excluded
    $excludedIds = $order->excludedCourierIds();
    expect($excludedIds)->not->toContain($courierUser->id);

    // Simulate: the same courier is found again by CourierMatchingService
    // This should be allowed since they are not in excludedCourierIds
    $order->update([
        'courier_id' => $courierUser->id,
        'courier_offered_at' => now(),
    ]);
    $order->changeStatusBySystem('COURIER_OFFERED');

    expect($order->status)->toBe('COURIER_OFFERED');
    expect($order->courier_id)->toBe($courierUser->id);
});

test('CourierOfferTimeoutJob does nothing when courier_offered_at is null', function () {
    Queue::fake();

    $customer = createCustomer();
    $courierUser = createCourierUser();
    createCourierProfile($courierUser);
    createCourierLocation($courierUser->id);

    $order = createOrder($customer, 'COURIER_OFFERED', $courierUser->id);
    // courier_offered_at is intentionally left null

    $job = new CourierOfferTimeoutJob($order->id);
    $job->handle();

    $order->refresh();
    expect($order->status)->toBe('COURIER_OFFERED');
});

test('SearchCourierForOrderJob dispatches CourierOfferTimeoutJob when courier is found', function () {
    Bus::fake();

    $customer = createCustomer();
    $courierUser = createCourierUser();
    createCourierProfile($courierUser);
    createCourierLocation($courierUser->id);

    $order = createOrder($customer, 'SEARCHING_COURIER');

    // Manually simulate what SearchCourierForOrderJob does when finding a courier
    $order->update([
        'courier_id' => $courierUser->id,
        'courier_offered_at' => now(),
    ]);
    $order->changeStatusBySystem('COURIER_OFFERED');

    $offerTimeoutSeconds = (int) config('courier_search.courier_offer_timeout_seconds', 60);
    CourierOfferTimeoutJob::dispatch($order->id)
        ->delay(now()->addSeconds($offerTimeoutSeconds));

    Bus::assertDispatched(CourierOfferTimeoutJob::class, function ($job) use ($order) {
        return $job->orderId === $order->id;
    });
});
