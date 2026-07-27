<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام و نام خانوادگی الزامی است.',
            'name.max' => 'نام نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'address.required' => 'آدرس الزامی است.',
            'address.max' => 'آدرس نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'lat.required' => 'عرض جغرافیایی الزامی است.',
            'lat.numeric' => 'عرض جغرافیایی باید عدد باشد.',
            'lat.between' => 'عرض جغرافیایی باید بین -90 و 90 باشد.',
            'lng.required' => 'طول جغرافیایی الزامی است.',
            'lng.numeric' => 'طول جغرافیایی باید عدد باشد.',
            'lng.between' => 'طول جغرافیایی باید بین -180 و 180 باشد.',
        ];
    }
}
