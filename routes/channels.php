<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
| نکته‌ی مهم: نام‌هایی که اینجا با Broadcast::channel() ثبت می‌شوند نباید
| شامل پیشوند "private-" یا "presence-" باشند. Laravel این پیشوندها را
| قبل از تطبیق، خودش به‌صورت خودکار از نام کانالِ درخواستی کلاینت حذف
| می‌کند و نتیجه را با این الگوها مقایسه می‌کند. اگر پیشوند را اینجا هم
| بنویسیم، تطبیق هیچ‌وقت برقرار نمی‌شود و درخواست با 403 رد می‌شود.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * کانال خصوصی سفارش - مشتری و پیک تخصیص‌داده‌شده مجاز به شنیدن هستند.
 * از سمت کلاینت با نام "private-order.{orderId}" subscribe می‌شود.
 */
Broadcast::channel('order.{orderId}', function ($user, int $orderId) {
    $order = Order::select('id', 'customer_id', 'courier_id')->find($orderId);

    if (! $order) {
        return false;
    }

    return (int) $user->id === (int) $order->customer_id
        || (int) $user->id === (int) $order->courier_id;
});

/**
 * کانال خصوصی پیک - فقط خود پیک مجاز به شنیدن است.
 * برای دریافت پیشنهاد سفارش‌های جدید استفاده می‌شود.
 * از سمت کلاینت با نام "private-courier.{courierId}" subscribe می‌شود.
 */
Broadcast::channel('courier.{courierId}', function ($user, int $courierId) {
    return (int) $user->id === $courierId;
});

/**
 * کانال حضور (Presence) پیک - برای رهگیری موقعیت لحظه‌ای.
 * مشتریانی که سفارش فعال با این پیک دارند نیز می‌توانند join کنند.
 *
 * عمداً اسم پایه‌ی متفاوتی (courier-tracking) از کانال خصوصیِ بالا
 * (courier) دارد، چون بعد از حذف پیشوند private-/presence- توسط
 * Laravel، هر دو در غیر این صورت روی الگوی یکسان "courier.{courierId}"
 * می‌افتادند و یکی، منطق مجوزدهیِ دیگری را override می‌کرد.
 *
 * از سمت کلاینت با نام "presence-courier-tracking.{courierId}" subscribe می‌شود.
 */
Broadcast::channel('courier-tracking.{courierId}', function ($user, int $courierId) {
    Log::info('ssss');

    // پیک خودش همیشه مجاز است
    if ((int) $user->id === $courierId) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => 'courier',
        ];
    }

    // مشتری فقط در صورتی مجاز است که سفارش فعال با این پیک داشته باشد
    $hasActiveOrder = Order::query()
        ->where('customer_id', $user->id)
        ->where('courier_id', $courierId)
        ->whereNotIn('status', ['DELIVERED', 'CANCELLED', 'RETURNED_TO_SENDER', 'DELIVERY_FAILED'])
        ->exists();
    if ($hasActiveOrder) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => 'customer',
        ];
    }

    // ادمین‌هایی که مجوز manage couriers دارند می‌توانند موقعیت همه پیک‌ها را ببینند
    if ($user->can('manage couriers')) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => 'admin',
        ];
    }

    return false;
});
