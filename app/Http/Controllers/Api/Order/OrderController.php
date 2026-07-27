<?php

namespace App\Http\Controllers\Api\Order;

use App\Facades\Payment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Setting;
use App\Services\Auth\MobileNumberNormalizer;
use App\Services\Order\OrderNotificationService;
use App\Services\Order\OrderVerificationService;
use App\Services\Payment\DTO\PaymentRequestData;
use App\Services\Sms\Exceptions\SmsSendingException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class OrderController extends Controller
{
    public function __construct(
        private readonly OrderVerificationService $verificationService,
        private readonly OrderNotificationService $notificationService,
    ) {}

    public function priceEstimation(Request $request)
    {
        $validated = $request->validate([
            'distance' => ['required', 'numeric'],
            'weight' => ['required', 'integer', 'min:1'],
        ]);

        $distancePrice = $request->distance * 100;
        $weightPrice = $validated['weight'] * 500;

        $totalPrice = $distancePrice + $weightPrice;

        return response()->json([
            'status' => true,
            'message' => 'هزینه با موفقیت محاسبه شد.',
            'data' => [
                'distance_in_meter' => round($request->distance),
                'weight_in_gram' => $validated['weight'],
                'distance_price' => (int) round($distancePrice),
                'weight_price' => (int) round($weightPrice),
                'total_price' => (int) round($totalPrice),
            ],
        ]);
    }

    /**
     * ثبت سفارش جدید.
     *
     * منطق تشخیص فرستنده/گیرنده:
     * ۱. اندروید همیشه sender_mobile و receiver_mobile را ارسال می‌کند
     * ۲. سرور شماره کاربر (از sanctum) را با sender_mobile مقایسه می‌کند:
     *    - یکسان بود → sender_is_customer = true → اطلاعات از پروفایل کاربر
     *    - متفاوت بود → sender_is_customer = false → اطلاعات از فرم اندروید
     * ۳. همین منطق برای receiver_mobile تکرار می‌شود
     *
     * پس از ثبت سفارش، فرآیند تایید فرستنده/گیرنده (order_verifications) آغاز می‌شود:
     * ابتدا فرستنده و در ادامه گیرنده بررسی می‌شوند. جزئیات کامل در OrderVerificationService.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $userMobile = MobileNumberNormalizer::toLocal($user->mobile);

        // نرمال‌سازی شماره موبایل‌ها برای مقایسه دقیق
        $senderMobileParam = MobileNumberNormalizer::toLocal($request->input('sender_mobile'));
        $receiverMobileParam = MobileNumberNormalizer::toLocal($request->input('receiver_mobile'));

        // ---- تشخیص هویت فرستنده و گیرنده ----
        $senderIsCustomer = ($userMobile === $senderMobileParam);
        $receiverIsCustomer = ($userMobile === $receiverMobileParam);

        // ---- آماده‌سازی داده‌های فرستنده ----
        if ($senderIsCustomer) {
            // فرستنده = خود کاربر → اطلاعات از دیتابیس (sanctum)
            $senderData = [
                'sender_name' => $user->name,
                'sender_mobile' => $user->mobile,
                'sender_address' => $user->address,
                'sender_lat' => $user->lat,
                'sender_lng' => $user->lng,
            ];
        } else {
            // فرستنده = شخص دیگر → اطلاعات از فرم اندروید
            $senderData = [
                'sender_name' => $request->input('sender_name'),
                'sender_mobile' => $senderMobileParam,
                'sender_address' => $request->input('sender_address'),
                'sender_lat' => $request->input('sender_lat'),
                'sender_lng' => $request->input('sender_lng'),
            ];
        }

        // ---- آماده‌سازی داده‌های گیرنده ----
        if ($receiverIsCustomer) {
            // گیرنده = خود کاربر → اطلاعات از دیتابیس (sanctum)
            $receiverData = [
                'receiver_name' => $user->name,
                'receiver_mobile' => $user->mobile,
                'receiver_address' => $user->address,
                'receiver_lat' => $user->lat,
                'receiver_lng' => $user->lng,
            ];
        } else {
            // گیرنده = شخص دیگر → اطلاعات از فرم اندروید
            $receiverData = [
                'receiver_name' => $request->input('receiver_name'),
                'receiver_mobile' => $receiverMobileParam,
                'receiver_address' => $request->input('receiver_address'),
                'receiver_lat' => $request->input('receiver_lat'),
                'receiver_lng' => $request->input('receiver_lng'),
            ];
        }

        try {
            return DB::transaction(function () use (
                $user,
                $senderIsCustomer,
                $receiverIsCustomer,
                $senderData,
                $receiverData,
                $request,
            ) {
                // ---- ایجاد سفارش ----
                $order = Order::create([
                    'customer_id' => $user->id,
                    'sender_is_customer' => $senderIsCustomer,
                    'receiver_is_customer' => $receiverIsCustomer,

                    ...$senderData,
                    ...$receiverData,

                    'package_description' => $request->input('package_description'),
                    'package_weight_kg' => $request->input('package_weight_kg'),
                    'package_size' => $request->input('package_size'),

                    'payment_method' => $request->input('payment_method'),
                    'payment_by' => $request->input('payment_by'),

                    'price' => $request->input('price'),
                    'status' => 'CREATED',
                ]);

                // ---- ثبت اولین رکورد تاریخچه وضعیت ----
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'old_status' => null,   // اولین رکورد، وضعیت قبلی وجود ندارد
                    'new_status' => 'CREATED',
                    'changed_by' => $user->id,
                ]);

                // ---- شروع فرآیند تایید فرستنده/گیرنده (order_verifications) ----
                $this->verificationService->start($order);

                // ---- پرداخت و اطلاع‌رسانی ----
                $paymentResult = $this->handlePayment($order);

                $responseData = [
                    'order_id' => $order->id,
                    'sender_is_customer' => $order->sender_is_customer,
                    'receiver_is_customer' => $order->receiver_is_customer,
                    'status' => $order->status,
                ];

                if ($paymentResult !== null) {
                    $responseData = [...$responseData, ...$paymentResult];
                }

                return response()->json([
                    'status' => true,
                    'message' => 'سفارش با موفقیت ثبت شد.',
                    'data' => $responseData,
                ], 201);
            });
        } catch (SmsSendingException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error_code' => 'sms_send_failed',
            ], 502);
        }
    }

    /**
     * مدیریت پرداخت و اطلاع‌رسانی پس از ثبت سفارش.
     *
     * حالت اول:  payment_by=sender  + payment_method=online  → اگر sender_is_customer=true  → برگرداندن url درگاه
     * حالت دوم:  payment_by=receiver + payment_method=online  → اگر receiver_is_customer=true → برگرداندن url درگاه
     * حالت سوم:  payment_by=sender  + payment_method=cash_on_delivery → ارسال پیامک به sender_mobile
     * حالت چهارم: payment_by=receiver + payment_method=cash_on_delivery → ارسال پیامک به receiver_mobile
     *
     * @return array{url?: string}|null
     */
    private function handlePayment(Order $order): ?array
    {
        $paymentMethod = $order->payment_method;
        $paymentBy = $order->payment_by;

        // ---- حالت اول: پرداخت آنلاین توسط فرستنده ----
        if ($paymentBy === 'sender' && $paymentMethod === 'online') {
            if (! $order->sender_is_customer) {
                return null;
            }

            return $this->generatePaymentUrl($order);
        }

        // ---- حالت دوم: پرداخت آنلاین توسط گیرنده ----
        if ($paymentBy === 'receiver' && $paymentMethod === 'online') {
            if (! $order->receiver_is_customer) {
                return null;
            }

            return $this->generatePaymentUrl($order);
        }

        // ---- حالت سوم: پرداخت نقدی توسط فرستنده ----
        if ($paymentBy === 'sender' && $paymentMethod === 'cash_on_delivery') {
            $this->sendCashOnDeliverySms($order, $order->sender_mobile);

            return null;
        }

        // ---- حالت چهارم: پرداخت نقدی توسط گیرنده ----
        if ($paymentBy === 'receiver' && $paymentMethod === 'cash_on_delivery') {
            $this->sendCashOnDeliverySms($order, $order->receiver_mobile);

            return null;
        }

        return null;
    }

    /**
     * ایجاد تراکنش پرداخت و برگرداندن url درگاه.
     *
     * @return array{url: string}
     */
    private function generatePaymentUrl(Order $order): array
    {
        $callbackUrl = route('payment.callback', [
            'driver' => Setting::getValue('payment_default_driver', config('payment.default', 'zarinpal')),
            'orderId' => $order->id,
        ]);

        $data = new PaymentRequestData(
            amount: (int) $order->price,
            callbackUrl: $callbackUrl,
            description: "پرداخت سفارش شماره {$order->id}",
            mobile: $order->customer?->mobile,
            orderId: (string) $order->id,
        );

        $result = Payment::driver(Setting::getValue('payment_default_driver', config('payment.default', 'zarinpal')))->request($data);

        // ذخیره لینک در دیتابیس برای استفاده‌های بعدی (چه موفق چه ناموفق)
        $order->update(['payment_url' => $result->redirectUrl]);

        if (! $result->success) {
            Log::warning('Payment request failed for order', [
                'order_id' => $order->id,
                'message' => $result->message,
            ]);

            return ['url' => $callbackUrl];
        }

        return ['url' => $result->redirectUrl];
    }

    /**
     * ارسال پیامک پرداخت نقدی (لینک پیگیری سفارش) به شماره مورد نظر.
     */
    private function sendCashOnDeliverySms(Order $order, string $mobile): void
    {
        $paymentUrl = $this->generatePaymentUrl($order)['url'];

        try {
            $this->notificationService->sendCashOnDeliverySms($order, $mobile, $paymentUrl);
        } catch (SmsSendingException $e) {
            Log::warning('sms.cash_on_delivery_failed', [
                'order_id' => $order->id,
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function orderList(Request $request)
    {
        $user = $request->user();

        try {

            $orders = $user->orders()
                ->get()
                ->map(function ($order) {

                    $orderData = $order->toArray();

                    if (
                        $order->payment_method === 'online'
                        &&
                        $order->payment_status === 'paid'
                    ) {
                        $orderData['payment_url'] = $order->payment_url;
                    } else {
                        $orderData['payment_url'] = null;
                    }

                    $orderData['pickup_code'] = $this->extractVerificationCode($order, 'pickup');
                    $orderData['delivery_code'] = $this->extractVerificationCode($order, 'delivery');

                    return $orderData;
                });

            return response()->json([
                'status' => true,
                'message' => 'سفارش ها با موفقیت دریافت شد.',
                'data' => $orders,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'خطا در دریافت سفارش‌ها',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * استخراج کد تایید از verificationهای یک سفارش بر اساس type.
     * فقط زمانی کد برگردانده می‌شود که verification تایید شده باشد.
     */
    private function extractVerificationCode(Order $order, string $type): ?string
    {
        $verification = $order->verifications
            ->first(fn ($v) => $v->type === $type && $v->verified_at !== null);

        return $verification?->code;
    }

    public function orderCourierList(Request $request): JsonResponse
    {
        $user = $request->user();

        try {

            $orders = Order::where('courier_id', $user->id)
                ->with('verifications')
                ->get()
                ->map(function ($order) {

                    $orderData = $order->toArray();

                    $orderData['pickup_code'] = $this->extractVerificationCode($order, 'pickup');
                    $orderData['delivery_code'] = $this->extractVerificationCode($order, 'delivery');

                    return $orderData;
                });

            return response()->json([
                'status' => true,
                'message' => 'سفارش‌های پیک با موفقیت دریافت شد.',
                'data' => $orders,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'خطا در دریافت سفارش‌های پیک',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function orderDetail($order_id)
    {
        try {
            $order = Order::findOrFail($order_id);

            return response()->json([
                'status' => true,
                'message' => 'سفارش با موفقیت دریافت شد.',
                'data' => $order->load('verifications'),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطا در دریافت سفارش.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
