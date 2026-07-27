<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\SubmitReviewRequest;
use App\Models\Order;
use App\Services\Order\SurveyService;
use Illuminate\Http\JsonResponse;

final class ReviewController extends Controller
{
    public function __construct(
        private readonly SurveyService $surveyService,
    ) {}

    /**
     * ثبت نظر توسط مشتری (احراز هویت شده).
     * مشتری از داخل اپ نظر خود را ثبت می‌کند.
     */
    public function submitCustomerReview(SubmitReviewRequest $request, Order $order): JsonResponse
    {
        try {
            $review = $this->surveyService->submitCustomerReview(
                order: $order,
                userId: $request->user()->id,
                rating: $request->input('rating'),
                comment: $request->input('comment'),
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'نظر با موفقیت ثبت شد.',
            'data' => [
                'review_id' => $review->id,
                'order_id' => $review->order_id,
                'reviewer_type' => $review->reviewer_type,
                'rating' => $review->rating,
            ],
        ], 201);
    }

    /**
     * دریافت اطلاعات نظرسنجی برای نمایش فرم (بدون نیاز به احراز هویت).
     * فرانت‌اند با این اطلاعات فرم نظرسنجی را نمایش می‌دهد.
     */
    public function showSurveyForm(string $token): JsonResponse
    {
        try {
            $surveyToken = $this->surveyService->getSurveyData($token);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'order_id' => $surveyToken->order_id,
                'type' => $surveyToken->type,
                'order_description' => $surveyToken->order?->package_description,
            ],
        ]);
    }

    /**
     * ثبت نظر از طریق توکن نظرسنجی (فرستنده/گیرنده غیرمشتری).
     * این مسیر بدون نیاز به احراز هویت در دسترس است.
     */
    public function submitTokenReview(SubmitReviewRequest $request, string $token): JsonResponse
    {
        try {
            $review = $this->surveyService->submitReviewByToken(
                token: $token,
                rating: $request->input('rating'),
                comment: $request->input('comment'),
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'نظر با موفقیت ثبت شد.',
            'data' => [
                'review_id' => $review->id,
                'order_id' => $review->order_id,
                'reviewer_type' => $review->reviewer_type,
                'rating' => $review->rating,
            ],
        ], 201);
    }
}
