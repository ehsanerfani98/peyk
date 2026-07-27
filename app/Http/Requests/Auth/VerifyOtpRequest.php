<?php

namespace App\Http\Requests\Auth;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

final class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^(09[0-9]{9}|\+989[0-9]{9})$/'],
            'code' => ['required', 'string', 'digits:'.Setting::getValue('otp.length', config('otp.length'))],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'code.required' => 'کد تایید الزامی است.',
            'code.digits' => 'کد تایید باید :digits رقم باشد.',
        ];
    }
}
