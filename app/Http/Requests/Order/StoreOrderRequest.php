<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use App\Services\Auth\MobileNumberNormalizer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        if (! $user) {
            return [];
        }

        $userMobile = $user->mobile;

        // نرمال‌سازی شماره‌ها
        $senderMobile = MobileNumberNormalizer::toLocal($this->input('sender_mobile'));
        $receiverMobile = MobileNumberNormalizer::toLocal($this->input('receiver_mobile'));

        // تشخیص اینکه فرستنده/گیرنده خود کاربر هست یا نه
        $senderIsCustomer = ($userMobile === $senderMobile);
        $receiverIsCustomer = ($userMobile === $receiverMobile);

        // آیا شماره‌ها یکسان هستند؟
        $sameMobileNumbers = ($senderMobile === $receiverMobile);

        return [
            // ---- فرستنده ----
            'sender_mobile' => ['required', 'string', 'regex:/^(09[0-9]{9}|\+989[0-9]{9})$/'],
            'sender_name' => [
                Rule::requiredIf(! $senderIsCustomer),
                'nullable', 'string', 'max:255',
            ],
            'sender_address' => [
                Rule::requiredIf(! $senderIsCustomer),
                'nullable', 'string', 'max:1000',
            ],
            'sender_lat' => [
                Rule::requiredIf(! $senderIsCustomer),
                'nullable', 'numeric', 'between:-90,90',
            ],
            'sender_lng' => [
                Rule::requiredIf(! $senderIsCustomer),
                'nullable', 'numeric', 'between:-180,180',
            ],

            // ---- گیرنده ----
            'receiver_mobile' => [
                'required',
                'string',
                'regex:/^(09[0-9]{9}|\+989[0-9]{9})$/',
                // اگر شماره‌ها یکسان باشند، خطای اختصاصی نمایش بده
                function ($attribute, $value, $fail) use ($sameMobileNumbers) {
                    if ($sameMobileNumbers) {
                        $fail('شماره موبایل فرستنده و گیرنده نمی‌تواند یکسان باشد.');
                    }
                },
            ],
            'receiver_name' => [
                Rule::requiredIf(! $receiverIsCustomer),
                'nullable', 'string', 'max:255',
            ],
            'receiver_address' => [
                Rule::requiredIf(! $receiverIsCustomer),
                'nullable', 'string', 'max:1000',
            ],
            'receiver_lat' => [
                Rule::requiredIf(! $receiverIsCustomer),
                'nullable', 'numeric', 'between:-90,90',
            ],
            'receiver_lng' => [
                Rule::requiredIf(! $receiverIsCustomer),
                'nullable', 'numeric', 'between:-180,180',
            ],

            // ---- اطلاعات مرسوله ----
            'package_description' => ['nullable', 'string', 'max:2000'],
            'package_weight_kg' => ['nullable', 'numeric', 'min:0.01', 'max:10000'],
            'package_size' => ['required', 'string', 'in:small,medium,large'],

            // ---- پرداخت ----
            'payment_method' => ['required', 'string', 'in:cash_on_delivery,online'],
            'payment_by' => ['required', 'string', 'in:sender,receiver'],

            // ---- قیمت ----
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * قانون: تا زمانی که آخرین سفارش کاربر به یکی از وضعیت‌های نهایی نرسیده باشد
     * (DELIVERED / DELIVERY_FAILED / RETURNED_TO_SENDER / CANCELLED)
     * امکان ثبت سفارش جدید برای او وجود ندارد.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();

            if (! $user) {
                return;
            }

            $hasActiveOrder = Order::query()
                ->where('customer_id', $user->id)
                ->whereNotIn('status', Order::TERMINAL_STATUSES)
                ->exists();

            if ($hasActiveOrder) {
                $validator->errors()->add(
                    'order',
                    'شما یک سفارش فعال دارید و تا تکمیل یا لغو آن، امکان ثبت سفارش جدید وجود ندارد.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'sender_mobile.required' => 'شماره موبایل فرستنده الزامی است.',
            'sender_mobile.regex' => 'شماره موبایل فرستنده معتبر نیست.',
            'sender_name.required' => 'نام فرستنده الزامی است.',
            'sender_address.required' => 'آدرس فرستنده الزامی است.',
            'sender_lat.required' => 'عرض جغرافیایی فرستنده الزامی است.',
            'sender_lng.required' => 'طول جغرافیایی فرستنده الزامی است.',

            'receiver_mobile.required' => 'شماره موبایل گیرنده الزامی است.',
            'receiver_mobile.regex' => 'شماره موبایل گیرنده معتبر نیست.',
            'receiver_name.required' => 'نام گیرنده الزامی است.',
            'receiver_address.required' => 'آدرس گیرنده الزامی است.',
            'receiver_lat.required' => 'عرض جغرافیایی گیرنده الزامی است.',
            'receiver_lng.required' => 'طول جغرافیایی گیرنده الزامی است.',

            'package_size.required' => 'اندازه مرسوله الزامی است.',
            'package_size.in' => 'اندازه مرسوله باید یکی از مقادیر small, medium, large باشد.',
            'package_weight_kg.numeric' => 'وزن مرسوله باید عدد باشد.',
            'package_weight_kg.min' => 'وزن مرسوله باید حداقل ۰.۰۱ کیلوگرم باشد.',

            'payment_method.required' => 'روش پرداخت الزامی است.',
            'payment_method.in' => 'روش پرداخت نامعتبر است.',
            'payment_by.required' => 'پرداخت توسط الزامی است.',
            'payment_by.in' => 'مقدار پرداخت توسط نامعتبر است.',

            'price.required' => 'قیمت الزامی است.',
            'price.numeric' => 'قیمت باید عدد باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
        ];
    }
}
