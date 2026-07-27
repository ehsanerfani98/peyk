<div>
    <x-header title="تنظیمات پیامک" separator progress-indicator />

    <x-form wire:submit="save" class="max-w-2xl">
        {{-- حالت ارسال --}}
        <x-card title="حالت ارسال" icon="o-cog-6-tooth" shadow class="mb-4">
            <x-select label="حالت ارسال پیامک" wire:model="sms_mode"
                :options="[
                    ['id' => 'simulator', 'name' => 'شبیه‌ساز (تست)'],
                    ['id' => 'real', 'name' => 'واقعی (ippanel)'],
                ]"
                hint="در حالت شبیه‌ساز، پیامک‌ها به سرور محلی ارسال می‌شوند. در حالت واقعی از طریق ippanel ارسال می‌شوند." />
            <x-input label="آدرس پایه شبیه‌ساز" wire:model="sms_simulator_base_url"
                hint="آدرس سرور شبیه‌ساز محلی (پیش‌فرض: http://localhost:3000)" />
        </x-card>

        {{-- اطلاعات سرویس --}}
        <x-card title="اطلاعات سرویس ippanel" icon="o-server" shadow class="mb-4">
            <x-input label="آدرس پایه API" wire:model="base_url" />
            <x-input label="توکن احراز هویت" wire:model="token" type="password" />
            <x-input label="شماره فرستنده" wire:model="from_number" />
        </x-card>

        {{-- OTP --}}
        <x-card title="تنظیمات OTP (ورود/ثبت‌نام)" icon="o-key" shadow class="mb-4">
            <x-input label="کد پترن OTP" wire:model="otp_pattern_code" />
            <x-input label="کلید پارامتر OTP" wire:model="otp_param_key" />
        </x-card>

        {{-- لینک تایید --}}
        <x-card title="تنظیمات لینک تایید (فرستنده/گیرنده)" icon="o-shield-check" shadow class="mb-4">
            <x-input label="کد پترن لینک تایید" wire:model="verification_link_pattern_code" />
            <x-input label="کلید پارامتر لینک تایید" wire:model="verification_link_param_key" />
        </x-card>

        {{-- کلیدهای پارامتر وضعیت --}}
        <x-card title="کلیدهای پارامتر وضعیت سفارش" icon="o-adjustments-horizontal" shadow class="mb-4">
            <x-input label="کلید پارامتر وضعیت (مشتری)" wire:model="order_status_param_key" />
            <x-input label="کلید پارامتر وضعیت (غیرمشتری)" wire:model="non_customer_status_param_key" />
        </x-card>

        {{-- پترن‌های تغییر وضعیت به مشتری --}}
        <x-card title="پترن‌های اطلاع‌رسانی به مشتری" icon="o-users" shadow class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="در حال جستجوی پیک" wire:model="pattern_order_searching" />
                <x-input label="پیک تخصیص یافت" wire:model="pattern_order_courier_assigned" />
                <x-input label="منتظر تحویل بسته" wire:model="pattern_order_waiting_pickup" />
                <x-input label="بسته دریافت شد" wire:model="pattern_order_picked_up" />
                <x-input label="در مسیر تحویل" wire:model="pattern_order_in_transit" />
                <x-input label="تحویل شد" wire:model="pattern_order_delivered" />
                <x-input label="لغو شد" wire:model="pattern_order_cancelled" />
            </div>
        </x-card>

        {{-- پترن‌های تغییر وضعیت به فرستنده/گیرنده غیرمشتری --}}
        <x-card title="پترن‌های اطلاع‌رسانی به فرستنده/گیرنده غیرمشتری" icon="o-user-group" shadow class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="منتظر تحویل بسته" wire:model="pattern_sender_waiting_pickup" />
                <x-input label="بسته دریافت شد" wire:model="pattern_sender_picked_up" />
                <x-input label="در مسیر تحویل" wire:model="pattern_sender_in_transit" />
                <x-input label="تحویل شد" wire:model="pattern_sender_delivered" />
                <x-input label="لغو شد" wire:model="pattern_sender_cancelled" />
            </div>
        </x-card>

        {{-- پترن‌های پیک --}}
        <x-card title="پترن‌های اطلاع‌رسانی به پیک" icon="o-bolt" shadow class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="کد پترن پیشنهاد به پیک" wire:model="pattern_courier_offer" />
                <x-input label="کلید پارامتر پیشنهاد" wire:model="courier_offer_param_key" />
                <x-input label="کد پترن لغو به پیک" wire:model="pattern_courier_cancelled" />
                <x-input label="کلید پارامتر لغو پیک" wire:model="courier_cancelled_param_key" />
            </div>
        </x-card>

        {{-- لغو سیستم --}}
        <x-card title="پترن لغو سیستم (تایم‌اوت)" icon="o-clock" shadow class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="کد پترن لغو سیستم" wire:model="pattern_system_cancellation" />
                <x-input label="کلید پارامتر لغو سیستم" wire:model="system_cancellation_param_key" />
            </div>
        </x-card>

        {{-- نظرسنجی و پرداخت نقدی --}}
        <x-card title="پترن‌های نظرسنجی و پرداخت نقدی" icon="o-document-text" shadow class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="کد پترن لینک نظرسنجی" wire:model="pattern_survey_link" />
                <x-input label="کلید پارامتر نظرسنجی" wire:model="survey_link_param_key" />
                <x-input label="کد پترن پرداخت نقدی" wire:model="pattern_cash_on_delivery" />
                <x-input label="کلید پارامتر پرداخت نقدی" wire:model="cash_on_delivery_param_key" />
            </div>
        </x-card>

        <x-card>
            <x-slot:actions>
                <x-button label="ذخیره تمام تنظیمات" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-card>
    </x-form>
</div>
