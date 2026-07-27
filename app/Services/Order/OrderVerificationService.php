<?php

namespace App\Services\Order;

use App\Helpers\SmsSender;
use App\Models\Order;
use App\Models\OrderVerification;
use App\Models\Setting;
use App\Services\Order\Exceptions\OrderVerificationException;
use App\Services\Sms\Exceptions\SmsSendingException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class OrderVerificationService
{
    public function __construct(
        private readonly SmsSender $smsSender,
    ) {}

    /**
     * شروع فرآیند تایید سفارش تازه ایجادشده.
     * طبق قرارداد پروژه، همیشه ابتدا فرستنده بررسی می‌شود و بعد گیرنده.
     *
     * @throws SmsSendingException
     */
    public function start(Order $order): void
    {
        $this->initiateSenderVerification($order);
    }

    /**
     * تایید یک مرحله (sender_verify / receiver_verify) از طریق توکن لینک پیامکی.
     * صرفا باز شدن لینک، تایید را به صورت خودکار انجام می‌دهد.
     *
     * @throws OrderVerificationException
     * @throws SmsSendingException
     */
    public function confirmByToken(string $token): OrderVerification
    {
        $verification = OrderVerification::query()->where('token', $token)->first();

        if (! $verification) {
            throw OrderVerificationException::notFound();
        }

        if ($verification->isVerified()) {
            // درخواست تکراری روی لینکی که قبلا تایید شده - پاسخ یکسان و بدون اثر جانبی برگردانده می‌شود
            return $verification;
        }

        if ($verification->isLocked()) {
            throw OrderVerificationException::locked();
        }

        if ($verification->isExpired()) {
            throw OrderVerificationException::expired();
        }

        $this->markVerified($verification);

        return $verification;
    }

    /**
     * @throws SmsSendingException
     */
    private function initiateSenderVerification(Order $order): void
    {
        $verification = $this->createVerificationRecord(
            order: $order,
            isCustomer: $order->sender_is_customer,
            typeForNonCustomer: 'sender_verify',
            typeForCustomer: 'pickup',
            mobile: $order->sender_mobile,
        );

        $order->changeStatus('WAITING_SENDER_VERIFY');

        if ($order->sender_is_customer) {
            // هویت فرستنده از طریق ورود کاربر به سیستم (sanctum) همان لحظه احراز شده - نیازی به لینک نیست
            $this->markVerified($verification);
        } else {
            $this->sendVerificationSms($verification);
        }
    }

    /**
     * @throws SmsSendingException
     */
    private function initiateReceiverVerification(Order $order): void
    {
        $verification = $this->createVerificationRecord(
            order: $order,
            isCustomer: $order->receiver_is_customer,
            typeForNonCustomer: 'receiver_verify',
            typeForCustomer: 'delivery',
            mobile: $order->receiver_mobile,
        );

        $order->changeStatus('WAITING_RECEIVER_VERIFY');

        if ($order->receiver_is_customer) {
            $this->markVerified($verification);
        } else {
            $this->sendVerificationSms($verification);
        }
    }

    private function createVerificationRecord(
        Order $order,
        bool $isCustomer,
        string $typeForNonCustomer,
        string $typeForCustomer,
        string $mobile,
    ): OrderVerification {
        return OrderVerification::create([
            'order_id' => $order->id,
            'type' => $isCustomer ? $typeForCustomer : $typeForNonCustomer,
            'delivery_channel' => $isCustomer ? 'in_app' : 'sms',
            'mobile' => $isCustomer ? null : $mobile,
            'code' => $this->generateCode(),
            'token' => $isCustomer ? null : Str::random(64),
            'expires_at' => now()->addMinutes((int) Setting::getValue('order_verification.expire_minutes', config('order_verification.expire_minutes', 1440))),
        ]);
    }

    /**
     * @throws SmsSendingException
     */
    private function markVerified(OrderVerification $verification): void
    {
        $verification->update(['verified_at' => now()]);

        $order = $verification->order;

        match ($verification->type) {
            'sender_verify', 'pickup' => $this->onSenderVerified($order),
            'receiver_verify', 'delivery' => $this->onReceiverVerified($order),
        };
    }

    /**
     * @throws SmsSendingException
     */
    private function onSenderVerified(Order $order): void
    {
        $order->changeStatus('SENDER_VERIFIED');

        $this->initiateReceiverVerification($order);
    }

    private function onReceiverVerified(Order $order): void
    {
        $order->changeStatus('RECEIVER_VERIFIED');

        // شروع جستجوی پیک: وضعیت -> SEARCHING_COURIER + دیسپچ Job جستجو (App\Jobs\SearchCourierForOrderJob)
        $order->startCourierSearch();
    }

    /**
     * ارسال لینک تایید فرستنده/گیرنده از طریق sendOtp.
     * لینک تایید به عنوان مقدار پارامتر پترن ارسال می‌شود.
     *
     * @throws SmsSendingException
     */
    private function sendVerificationSms(OrderVerification $verification): void
    {
        $link = url("/verify/{$verification->token}");

        // کد پترن و کلید پارامتر برای لینک تایید از setting (DB) با فال‌بک به config خوانده می‌شود
        $patternCode = Setting::getValue('ippanel.verification_link_pattern_code', config('ippanel.verification_link_pattern_code'));
        $paramKey = Setting::getValue('ippanel.verification_link_param_key', config('ippanel.verification_link_param_key', 'code'));

        if (! $patternCode) {
            Log::info('sms.verification_pattern_not_configured', [
                'order_id' => $verification->order_id,
            ]);

            return;
        }

        $this->smsSender->send(
            localMobile: $verification->mobile,
            paramValue: $link,
            patternCode: $patternCode,
            paramKey: $paramKey,
        );
    }

    private function generateCode(): string
    {
        $length = (int) Setting::getValue('order_verification.code_length', config('order_verification.code_length', 4));
        $min = (int) str_pad('1', $length, '0');
        $max = (int) str_pad('', $length, '9');

        return (string) random_int($min, $max);
    }
}
