<div>
    <x-header title="تنظیمات OTP" separator progress-indicator />

    <x-card>
        <x-form wire:submit="save" class="max-w-xl">
            <x-input label="تعداد رقم کد تایید" wire:model="otp_length" type="number" hint="پیش‌فرض: 5" />
            <x-input label="مدت اعتبار کد (ثانیه)" wire:model="otp_expire_seconds" type="number" hint="پیش‌فرض: 120" />
            <x-input label="حداقل فاصله ارسال مجدد (ثانیه)" wire:model="otp_resend_seconds" type="number" hint="پیش‌فرض: 60" />
            <x-input label="حداکثر تعداد تلاش" wire:model="otp_max_attempts" type="number" hint="پیش‌فرض: 5" />

            <x-slot:actions>
                <x-button label="ذخیره" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
