<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ConfirmCodeRequest;
use App\Models\Order;
use App\Services\Order\Exceptions\OrderStateException;
use App\Services\Order\Exceptions\OrderVerificationException;
use App\Services\Order\OrderFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderFulfillmentController extends Controller
{
    public function __construct(
        private readonly OrderFulfillmentService $fulfillmentService,
    ) {}

    /**
     * پیک، کدی که از فرستنده گرفته را برای تحویل گرفتن بسته وارد می‌کند.
     */
    public function confirmPickup(ConfirmCodeRequest $request, Order $order): JsonResponse
    {
        try {
            $this->fulfillmentService->confirmPickup($order, $request->user()->id, $request->input('code'));
        } catch (OrderStateException|OrderVerificationException $e) {
            return $this->errorResponse($e);
        }

        return response()->json([
            'status' => true,
            'message' => 'تحویل گرفتن بسته با موفقیت تایید شد.',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->fresh()->status,
            ],
        ]);
    }

    /**
     * پیک، کدی که از گیرنده گرفته را برای تحویل دادن بسته وارد می‌کند.
     */
    public function confirmDelivery(ConfirmCodeRequest $request, Order $order): JsonResponse
    {
        try {
            $this->fulfillmentService->confirmDelivery($order, $request->user()->id, $request->input('code'));
        } catch (OrderStateException|OrderVerificationException $e) {
            return $this->errorResponse($e);
        }

        return response()->json([
            'status' => true,
            'message' => 'تحویل بسته با موفقیت تایید شد.',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->fresh()->status,
            ],
        ]);
    }

    /**
     * تایید پرداخت نقدی توسط پیک.
     *
     * وقتی payment_method = cash_on_delivery، پیک بعد از دریافت وجه نقد
     * این endpoint را صدا می‌زند تا payment_status به paid تغییر کند.
     */
    public function confirmCashPayment(Request $request, Order $order): JsonResponse
    {
        try {
            $this->fulfillmentService->confirmCashPayment($order, $request->user()->id);
        } catch (OrderStateException $e) {
            return $this->errorResponse($e);
        }

        return response()->json([
            'status' => true,
            'message' => 'پرداخت نقدی با موفقیت ثبت شد.',
            'data' => [
                'order_id' => $order->id,
                'payment_status' => $order->fresh()->payment_status,
            ],
        ]);
    }

    /**
     * ثبت اسنپ‌شات موقعیت پیک در طول مسیر - اپ پیک می‌تواند در تمام طول مرحله IN_TRANSIT
     * به‌طور مکرر این endpoint را صدا بزند تا مسیر کامل سفر ثبت شود.
     */
    public function recordMidpoint(Request $request, Order $order): JsonResponse
    {
        try {
            $this->fulfillmentService->recordMidpointSnapshot($order, $request->user()->id);
        } catch (OrderStateException $e) {
            return $this->errorResponse($e);
        }

        return response()->json([
            'status' => true,
            'message' => 'موقعیت در مسیر ثبت شد.',
        ]);
    }

    private function errorResponse(OrderStateException|OrderVerificationException $e): JsonResponse
    {
        $statusCode = $e instanceof OrderStateException ? $e->statusCode() : 422;

        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
            'error_code' => $e->errorCode(),
        ], $statusCode);
    }
}
