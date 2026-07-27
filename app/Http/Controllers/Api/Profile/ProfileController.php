<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;

final class ProfileController extends Controller
{
    /**
     * ثبت اطلاعات تکمیلی پروفایل کاربر (نام، آدرس، مختصات جغرافیایی).
     * این اندپوینت پس از مرحله OTP فراخوانی می‌شود.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->only(['name', 'address', 'lat', 'lng']));

        return response()->json([
            'status' => true,
            'message' => 'اطلاعات پروفایل با موفقیت ثبت شد.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'address' => $user->address,
                'lat' => $user->lat,
                'lng' => $user->lng,
            ],
        ]);
    }
}
