<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'امتیاز الزامی است.',
            'rating.integer' => 'امتیاز باید عدد صحیح باشد.',
            'rating.min' => 'حداقل امتیاز ۱ است.',
            'rating.max' => 'حداکثر امتیاز ۵ است.',
            'comment.max' => 'متن نظر نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ];
    }
}
