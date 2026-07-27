<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Models\Order;
use App\Services\Order\Exceptions\OrderStateException;
use App\Services\Order\OrderCancellationService;
use Illuminate\Http\JsonResponse;

final class OrderCancellationController extends Controller
{
    public function __construct(
        private readonly OrderCancellationService $cancellationService,
    ) {}

    /**
     * لغو سفارش توسط مشتری (مثلا وقتی پیکی پیدا نمی‌شود یا به هر دلیل دیگری منصرف می‌شود).
     */
    public function cancelByCustomer(CancelOrderRequest $request, Order $order): JsonResponse
    {
        try {
            $this->cancellationService->cancelByCustomer(
                $order,
                $request->user()->id,
                $request->input('reason'),
            );
        } catch (OrderStateException $e) {
            return $this->errorResponse($e);
        }

        return response()->json([
            'status' => true,
            'message' => 'سفارش لغو شد.',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->fresh()->status,
            ],
        ]);
    }

    /**
     * لغو سفارش توسط پیک (مثلا وقتی پیک به هر دلیلی از انجام سفارش منصرف می‌شود).
     */
    public function cancelByCourier(CancelOrderRequest $request, Order $order): JsonResponse
    {
        try {
            $this->cancellationService->cancelByCourier(
                $order,
                $request->user()->id,
                $request->input('reason'),
            );
        } catch (OrderStateException $e) {
            return $this->errorResponse($e);
        }

        return response()->json([
            'status' => true,
            'message' => 'سفارش لغو شد.',
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
