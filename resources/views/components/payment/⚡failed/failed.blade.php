<div class="min-h-screen flex items-center justify-center bg-base-200 px-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body items-center text-center gap-4">
            {{-- Error Icon --}}
            <div class="w-20 h-20 rounded-full bg-error/10 flex items-center justify-center">
                <x-icon name="o-exclamation-circle" class="w-12 h-12 text-error" />
            </div>

            {{-- Title --}}
            <h1 class="text-2xl font-bold text-error">پرداخت ناموفق بود</h1>

            {{-- Order Info --}}
            <div class="bg-base-200 rounded-lg p-4 w-full space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm opacity-70">شماره سفارش</span>
                    <span class="font-bold">{{ $orderId }}</span>
                </div>
            </div>

            {{-- Message --}}
            <p class="text-xs opacity-70">
                متاسفانه پرداخت سفارش شما با مشکل مواجه شد. لطفاً مجدداً از طریق اپلیکیشن اقدام به پرداخت نمایید.
            </p>

            {{-- Back to App Button --}}
            <a href="shetabito://order-details?order_id={{ $orderId }}" class="btn btn-error btn-wide">
                <x-icon name="o-arrow-right" class="w-5 h-5" />
                بازگشت به اپلیکیشن
            </a>
        </div>
    </div>
</div>
