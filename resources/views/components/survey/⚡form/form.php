<?php

use App\Services\Order\SurveyService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.guest')] #[Title('نظرسنجی')] class extends Component
{
    public string $token;

    public ?int $orderId = null;

    public ?string $surveyType = null;

    public ?string $orderDescription = null;

    public int $rating = 0;

    public string $comment = '';

    public ?string $errorMessage = null;

    public bool $isSubmitted = false;

    public bool $isLoading = true;

    public function mount(string $token, SurveyService $surveyService): void
    {
        $this->token = $token;

        try {
            $surveyToken = $surveyService->getSurveyData($token);

            $this->orderId = $surveyToken->order_id;
            $this->surveyType = $surveyToken->type;
            $this->orderDescription = $surveyToken->order?->package_description;
            $this->isLoading = false;
        } catch (InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->isLoading = false;
        }
    }

    public function submit(SurveyService $surveyService): void
    {
        $this->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'لطفاً امتیاز خود را انتخاب کنید.',
            'rating.min' => 'حداقل امتیاز ۱ است.',
            'rating.max' => 'حداکثر امتیاز ۵ است.',
            'comment.max' => 'متن نظر نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ]);

        try {
            $surveyService->submitReviewByToken(
                token: $this->token,
                rating: $this->rating,
                comment: $this->comment ?: null,
            );

            $this->isSubmitted = true;
        } catch (InvalidArgumentException $e) {
            $this->addError('rating', $e->getMessage());
        }
    }

    public function setRating(int $value): void
    {
        $this->rating = $value;
    }

    public function surveyTypeLabel(): string
    {
        return match ($this->surveyType) {
            'sender' => 'فرستنده',
            'receiver' => 'گیرنده',
            'customer' => 'مشتری',
            default => 'کاربر',
        };
    }
};
