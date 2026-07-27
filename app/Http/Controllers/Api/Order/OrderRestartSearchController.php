<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\Exceptions\OrderStateException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * آغاز مجدد جستجوی پیک برای سفارشی که در وضعیت COURIER_NOT_FOUND قرار دارد.
 *
 * فقط مشتری (صاحب سفارش) مجاز به این عملیات است.
 * وضعیت سفارش باید COURIER_NOT_FOUND باشد.
 */
final class OrderRestartSearchController extends Controller
{
    public function __invoke(Request $request, Order $order): JsonResponse
    {
        if ((int) $order->customer_id !== $request->user()->id) {
            throw OrderStateException::forbidden('این سفارش متعلق به شما نیست.');
        }

        if ($order->status !== 'COURIER_NOT_FOUND') {
            throw OrderStateException::invalidTransition(
                'تنها سفارش‌هایی که جستجوی پیک برای آن‌ها به پایان رسیده قابل جستجوی مجدد هستند.'
            );
        }

        $order->restartCourierSearch();

        return response()->json([
            'status' => true,
            'message' => 'جستجوی پیک مجددا آغاز شد.',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->fresh()->status,
            ],
        ]);
    }
}
