<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\CourierOfferService;
use App\Services\Order\Exceptions\OrderStateException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CourierOfferController extends Controller
{
    public function __construct(
        private readonly CourierOfferService $offerService,
    ) {}

    /**
     * پیک، پیشنهاد سفارش (COURIER_OFFERED) را می‌پذیرد.
     */
    public function accept(Request $request, Order $order): JsonResponse
    {
        try {
            $this->offerService->accept($order, $request->user()->id);
        } catch (OrderStateException $e) {
            return $this->errorResponse($e);
        }

        return response()->json([
            'status' => true,
            'message' => 'سفارش با موفقیت پذیرفته شد.',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->fresh()->status,
            ],
        ]);
    }

    /**
     * پیک، پیشنهاد سفارش (COURIER_OFFERED) را رد می‌کند - جستجو برای پیک دیگر ادامه می‌یابد.
     */
    public function reject(Request $request, Order $order): JsonResponse
    {
        try {
            $this->offerService->reject($order, $request->user()->id);
        } catch (OrderStateException $e) {
            return $this->errorResponse($e);
        }

        return response()->json([
            'status' => true,
            'message' => 'سفارش رد شد.',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->fresh()->status,
            ],
        ]);
    }

    private function errorResponse(OrderStateException $e): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
            'error_code' => $e->errorCode(),
        ], $e->statusCode());
    }
}
