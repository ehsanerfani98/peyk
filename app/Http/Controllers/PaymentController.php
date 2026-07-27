<?php

namespace App\Http\Controllers;

use App\Facades\Payment;
use App\Models\Order;
use App\Services\Payment\DTO\PaymentRequestData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * کنترلر نمونه جهت نمایش نحوه‌ی استفاده از سرویس پرداخت.
 * این فایل صرفا یک مثال است و می‌توانید منطق آن را متناسب با پروژه‌ی خود (سفارش، فاکتور و ...) تغییر دهید.
 */
class PaymentController extends Controller
{
    /**
     * شروع فرآیند پرداخت و هدایت کاربر به درگاه.
     *
     * مثال روت:
     * Route::get('/pay/{driver}/{orderId}', [PaymentController::class, 'pay'])->name('payment.pay');
     */
    public function pay(Request $request, string $driver, int $orderId)
    {
        // آدرس بازگشت به صورت داینامیک و در همین لحظه ساخته می‌شود
        // می‌توانید هر پارامتری که لازم دارید (مثل شناسه سفارش) را داخل آن قرار دهید
        $callbackUrl = route('payment.callback', [
            'driver' => $driver,
            'orderId' => $orderId,
        ]);
        $order = Order::findOrFail($orderId);
        $data = new PaymentRequestData(
            amount: $order->price,
            callbackUrl: $callbackUrl,
            description: "پرداخت سفارش شماره {$orderId}",
            mobile: $request->user()?->mobile,
            orderId: $orderId,
        );

        $result = Payment::driver($driver)->request($data);

        if (! $result->success) {
            Log::warning('Payment request failed', [
                'driver' => $driver,
                'orderId' => $orderId,
                'message' => $result->message,
                'raw' => $result->raw,
            ]);

            return back()->with('error', 'خطا در اتصال به درگاه پرداخت: '.$result->message);
        }

        $order->update([
            'payment_authority' => $result->authority,
            'payment_driver' => $driver,
        ]);

        return redirect()->away($result->redirectUrl);
    }

    /**
     * بازگشت کاربر از درگاه پرداخت و تایید تراکنش.
     *
     * مثال روت:
     * Route::get('/pay/{driver}/{orderId}/callback', [PaymentController::class, 'callback'])->name('payment.callback');
     */
    public function callback(Request $request, string $driver, int $orderId)
    {
        $order = Order::findOrFail($orderId);

        // زرین‌پال: Authority و Status را در کوئری استرینگ ارسال می‌کند
        // زیبال: trackId، success و status را ارسال می‌کند
        $authority = $request->query('Authority') ?? $request->query('trackId');
        if (! $authority) {
            return redirect()->route('payment.failed', ['orderId' => $orderId]);
        }

        $amount = $order->price;

        $result = Payment::driver($driver)->verify($authority, $amount);

        if (! $result->success) {
            Log::warning('Payment verify failed', [
                'driver' => $driver,
                'orderId' => $orderId,
                'message' => $result->message,
                'raw' => $result->raw,
            ]);

            return redirect()->route('payment.failed', ['orderId' => $orderId]);
        }

        $order->update([
            'payment_status' => 'paid',
            'payment_ref_id' => $result->refId,
            'paid_at' => now(),
        ]);

        return redirect()->route('payment.success', [
            'orderId' => $orderId,
            'refId' => $result->refId,
        ]);
    }
}
