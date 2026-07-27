<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\Auth\Exceptions\OtpException;
use App\Services\Auth\MobileNumberNormalizer;
use App\Services\Auth\OtpService;
use App\Services\Sms\Exceptions\SmsSendingException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService,
    ) {}

    /**
     * ارسال کد تایید به شماره موبایل.
     * برای هر دو حالت ثبت‌نام کاربر جدید و ورود کاربر قبلی از همین متد استفاده می‌شود.
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $mobile = MobileNumberNormalizer::toLocal($request->validated('mobile'));

        try {
            $this->otpService->issue($mobile);
        } catch (OtpException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->errorCode(),
            ], 429);
        } catch (SmsSendingException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error_code' => 'sms_send_failed',
            ], 502);
        }

        return response()->json([
            'status' => true,
            'message' => 'کد تایید ارسال شد.',
        ]);
    }

    /**
     * تایید کد ارسالی. در صورت صحیح بودن کد:
     * - اگر کاربری با این شماره موبایل وجود نداشته باشد، ساخته می‌شود و نقش customer دریافت می‌کند.
     * - یک توکن sanctum جدید صادر می‌شود.
     * - وضعیت خالی/پر بودن فیلد name در قالب has_name برگردانده می‌شود.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $mobile = MobileNumberNormalizer::toLocal($request->validated('mobile'));
        $code = $request->validated('code');

        try {
            $this->otpService->verify($mobile, $code);
        } catch (OtpException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->errorCode(),
            ], 422);
        }

        $user = User::firstOrCreate(
            ['mobile' => $mobile],
            ['status' => true],
        );

        if ($user->wasRecentlyCreated) {
            $user->assignRole('customer');
        }

        // مشابه اکثر اپلیکیشن‌های موبایل: با هر لاگین جدید، توکن‌های قبلیِ همین اپ باطل می‌شوند
        // و کاربر پس از logout باید مجددا شماره موبایل و کد تایید را وارد کند.
        $user->tokens()->where('name', 'mobile-app')->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'ورود با موفقیت انجام شد.',
            'data' => [
                'token' => $token,
                'profile_completed' => $user->isProfileCompleted(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                ],
            ],
        ]);
    }

    /**
     * خروج کاربر: فقط توکن جاری باطل می‌شود.
     * برای ورود مجدد، کاربر باید دوباره مراحل ارسال/تایید کد را طی کند.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'خروج با موفقیت انجام شد.',
        ]);
    }
}
