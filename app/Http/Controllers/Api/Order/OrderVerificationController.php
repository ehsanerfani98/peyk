<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Services\Order\Exceptions\OrderVerificationException;
use App\Services\Order\OrderVerificationService;
use App\Services\Sms\Exceptions\SmsSendingException;
use Illuminate\Http\JsonResponse;

final class OrderVerificationController extends Controller
{
    public function __construct(
        private readonly OrderVerificationService $verificationService,
    ) {}

    /**
     * تایید لینک ارسال‌شده به فرستنده/گیرنده سفارش
     * این مسیر بدون نیاز به احراز هویت (auth:sanctum) در دسترس است، چون طرف مقابل
     * لزوما کاربر ثبت‌نام‌شده در اپلیکیشن نیست.
     *
     * صرفا باز شدن این آدرس تایید را انجام می‌دهد و کد لازم برای تحویل فیزیکی بسته
     * به پیک، در پاسخ برگردانده می‌شود تا در صفحه به کاربر نمایش داده شود.
     */
    public function confirm(string $token): JsonResponse
    {
        try {
            $verification = $this->verificationService->confirmByToken($token);
        } catch (OrderVerificationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->errorCode(),
            ], 422);
        } catch (SmsSendingException $e) {
            // در صورتی که این تایید، ارسال لینک مرحله بعد (گیرنده) را تریگر کند و آن ارسال با خطا مواجه شود
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error_code' => 'sms_send_failed',
            ], 502);
        }

        return response()->json([
            'status' => true,
            'message' => 'تایید با موفقیت انجام شد.',
            'data' => [
                'type' => $verification->type,
                'code' => $verification->code,
                'verified_at' => $verification->verified_at,
            ],
        ]);
    }
}
