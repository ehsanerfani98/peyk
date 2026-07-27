<div class="min-h-screen flex items-center justify-center bg-base-200 px-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body items-center text-center gap-4">
            {{-- Success Icon --}}
            <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center">
                <x-icon name="o-check-circle" class="w-12 h-12 text-success" />
            </div>

            {{-- Title --}}
            <h1 class="text-2xl font-bold text-success">پرداخت با موفقیت انجام شد</h1>

            {{-- Order Info --}}
            <div class="bg-base-200 rounded-lg p-4 w-full space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm opacity-70">شماره سفارش</span>
                    <span class="font-bold">{{ $orderId }}</span>
                </div>
                @if ($refId)
                    <div class="flex justify-between items-center">
                        <span class="text-sm opacity-70">کد پیگیری</span>
                        <span class="font-bold font-mono">{{ $refId }}</span>
                    </div>
                @endif
            </div>

            {{-- Message --}}
            <p class="text-xs opacity-70">
                سفارش شما با موفقیت پرداخت شد. می‌توانید وضعیت سفارش را از طریق اپلیکیشن پیگیری کنید.
            </p>

            {{-- Back to App Button --}}
            <a href="shetabito://order-details?order_id={{ $orderId }}" class="btn btn-success btn-wide">

                <x-icon name="o-arrow-right" class="w-5 h-5" />

                بازگشت به اپلیکیشن

            </a>
        </div>
    </div>
</div>
