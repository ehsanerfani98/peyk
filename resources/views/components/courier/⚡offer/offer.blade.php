<div class="min-h-screen flex items-center justify-center bg-base-200 px-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body items-center text-center gap-4">

            @if ($state === 'accepted')
                <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center">
                    <x-icon name="o-check-circle" class="w-12 h-12 text-success" />
                </div>

                <h1 class="text-2xl font-bold text-success">سفارش با موفقیت پذیرفته شد</h1>

                <div class="bg-base-200 rounded-lg p-4 w-full space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm opacity-70">کد سفارش</span>
                        <span class="font-bold font-mono text-lg">{{ $orderId }}</span>
                    </div>
                </div>

                <p class="text-xs opacity-70">
                    این سفارش به شما تخصیص یافت. لطفاً برای تحویل گرفتن بسته به مبدأ مراجعه کنید.
                </p>
            @elseif ($state === 'offered')
                <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center">
                    <x-icon name="o-truck" class="w-12 h-12 text-primary" />
                </div>

                <h1 class="text-2xl font-bold">سفارش جدید برای شما</h1>

                <div class="bg-base-200 rounded-lg p-4 w-full space-y-2 text-right">
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-sm opacity-70 shrink-0">کد سفارش</span>
                        <span class="font-bold font-mono">{{ $orderId }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-sm opacity-70 shrink-0">مبدأ</span>
                        <span class="text-sm font-bold">{{ $pickupAddress }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-sm opacity-70 shrink-0">مقصد</span>
                        <span class="text-sm font-bold">{{ $deliveryAddress }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-sm opacity-70 shrink-0">کرایه</span>
                        <span class="font-bold">{{ $price }} تومان</span>
                    </div>
                </div>

                <button
                    type="button"
                    class="btn btn-primary btn-block"
                    wire:click="confirm"
                    wire:loading.attr="disabled"
                >
                    تایید سفارش
                </button>

                <p class="text-xs opacity-70">
                    با تایید، این سفارش به شما تخصیص داده می‌شود.
                </p>
            @else
                @if ($state === 'expired')
                    <div class="w-20 h-20 rounded-full bg-warning/10 flex items-center justify-center">
                        <x-icon name="o-clock" class="w-12 h-12 text-warning" />
                    </div>

                    <h1 class="text-2xl font-bold text-warning">این پیشنهاد معتبر نیست</h1>
                @else
                    <div class="w-20 h-20 rounded-full bg-error/10 flex items-center justify-center">
                        <x-icon name="o-exclamation-circle" class="w-12 h-12 text-error" />
                    </div>

                    <h1 class="text-2xl font-bold text-error">خطا در تایید پیشنهاد</h1>
                @endif

                <div class="bg-base-200 rounded-lg p-4 w-full">
                    <p class="text-sm">{{ $errorMessage }}</p>
                </div>
            @endif

        </div>
    </div>
</div>
