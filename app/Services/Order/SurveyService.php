<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Review;
use App\Models\SurveyToken;
use App\Services\Sms\IppanelSmsService;
use Illuminate\Support\Str;

/**
 * سرویس نظرسنجی: تولید توکن، ارسال لینک پیامکی و ثبت نظر.
 *
 * نظرسنجی به سفارش وابسته است. سه نقش ممکن وجود دارد:
 * - customer:   مشتری (داخل اپ، بدون نیاز به توکن)
 * - sender:     فرستنده غیرمشتری (توکن + پیامک)
 * - receiver:   گیرنده غیرمشتری (توکن + پیامک)
 */
final class SurveyService
{
    public function __construct(
        private readonly IppanelSmsService $smsService,
        private readonly OrderNotificationService $notificationService,
    ) {}

    /**
     * شروع فرآیند نظرسنجی پس از تحویل سفارش.
     *
     * ۱. رکورد customer همیشه ایجاد می‌شود (حتی اگر داخل اپ پر شود).
     * ۲. اگر فرستنده غیرمشتری باشد → توکن + پیامک
     * ۳. اگر گیرنده غیرمشتری باشد → توکن + پیامک
     */
    public function initiateSurvey(Order $order): void
    {
        // مشتری: رکورد بدون توکن (داخل اپ)
        SurveyToken::create([
            'order_id' => $order->id,
            'type' => 'customer',
            'token' => null,
            'expires_at' => now()->addDays(7),
        ]);

        // فرستنده غیرمشتری
        if (! $order->sender_is_customer) {
            $this->createAndSendSurveyToken($order, 'sender');
        }

        // گیرنده غیرمشتری
        if (! $order->receiver_is_customer) {
            $this->createAndSendSurveyToken($order, 'receiver');
        }
    }

    /**
     * ثبت نظر مشتری (احراز هویت شده).
     *
     * @throws \InvalidArgumentException
     */
    public function submitCustomerReview(Order $order, int $userId, int $rating, ?string $comment = null): Review
    {
        $this->validateRating($rating);

        // بررسی تکراری نبودن: هر مشتری فقط یک نظر برای هر سفارش
        $exists = Review::query()
            ->where('order_id', $order->id)
            ->where('reviewer_type', 'customer')
            ->exists();

        if ($exists) {
            throw new \InvalidArgumentException('شما قبلا برای این سفارش نظر ثبت کرده‌اید.');
        }

        return Review::create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'reviewer_type' => 'customer',
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }

    /**
     * ثبت نظر از طریق توکن نظرسنجی (فرستنده/گیرنده غیرمشتری).
     *
     * @throws \InvalidArgumentException
     */
    public function submitReviewByToken(string $token, int $rating, ?string $comment = null): Review
    {
        $this->validateRating($rating);

        $surveyToken = SurveyToken::query()
            ->where('token', $token)
            ->first();

        if (! $surveyToken) {
            throw new \InvalidArgumentException('توکن نظرسنجی یافت نشد.');
        }

        if ($surveyToken->isUsed()) {
            throw new \InvalidArgumentException('این توکن قبلا استفاده شده است.');
        }

        if ($surveyToken->isExpired()) {
            throw new \InvalidArgumentException('توکن نظرسنجی منقضی شده است.');
        }

        // بررسی تکراری نبودن برای این نوع reviewer
        $exists = Review::query()
            ->where('order_id', $surveyToken->order_id)
            ->where('reviewer_type', $surveyToken->type)
            ->exists();

        if ($exists) {
            throw new \InvalidArgumentException('نظرسنجی برای این سفارش قبلا ثبت شده است.');
        }

        // ثبت نظر (user_id برای فرستنده/گیرنده غیرمشتری null است)
        $review = Review::create([
            'order_id' => $surveyToken->order_id,
            'user_id' => null,
            'reviewer_type' => $surveyToken->type,
            'rating' => $rating,
            'comment' => $comment,
        ]);

        // علامت‌گذاری توکن به عنوان استفاده‌شده
        $surveyToken->update(['used_at' => now()]);

        return $review;
    }

    /**
     * دریافت اطلاعات سفارش و توکن برای صفحه نظرسنجی عمومی.
     * فرانت‌اند با این اطلاعات فرم نظرسنجی را نمایش می‌دهد.
     *
     * @throws \InvalidArgumentException
     */
    public function getSurveyData(string $token): SurveyToken
    {
        $surveyToken = SurveyToken::query()
            ->where('token', $token)
            ->first();

        if (! $surveyToken) {
            throw new \InvalidArgumentException('توکن نظرسنجی یافت نشد.');
        }

        if ($surveyToken->isUsed()) {
            throw new \InvalidArgumentException('این توکن قبلا استفاده شده است.');
        }

        if ($surveyToken->isExpired()) {
            throw new \InvalidArgumentException('توکن نظرسنجی منقضی شده است.');
        }

        return $surveyToken->load('order');
    }

    /**
     * ایجاد توکن نظرسنجی و ارسال پیامک لینک.
     */
    private function createAndSendSurveyToken(Order $order, string $type): void
    {
        $token = Str::random(64);

        SurveyToken::create([
            'order_id' => $order->id,
            'type' => $type,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        $surveyLink = url("/survey/{$token}");

        if ($type === 'sender') {
            $this->notificationService->sendSurveyLinkToSender($order, $surveyLink);
        } else {
            $this->notificationService->sendSurveyLinkToReceiver($order, $surveyLink);
        }
    }

    private function validateRating(int $rating): void
    {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('امتیاز باید بین ۱ تا ۵ باشد.');
        }
    }
}
