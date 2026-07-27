<div class="min-h-screen flex items-center justify-center bg-base-200 px-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body items-center text-center gap-4">

            @if ($isSuccess)
                {{-- Success Icon --}}
                <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center">
                    <x-icon name="o-check-circle" class="w-12 h-12 text-success" />
                </div>

                {{-- Title --}}
                <h1 class="text-2xl font-bold text-success">تایید با موفقیت انجام شد</h1>

                {{-- Verification Info --}}
                <div class="bg-base-200 rounded-lg p-4 w-full space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm opacity-70">نوع تایید</span>
                        <span class="font-bold">
                            @if (in_array($verificationType, ['sender_verify', 'pickup']))
                                تایید فرستنده
                            @else
                                تایید گیرنده
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm opacity-70">کد تایید</span>
                        <span class="font-bold font-mono text-lg tracking-wider">{{ $verificationCode }}</span>
                    </div>
                </div>

                {{-- Message --}}
                <p class="text-xs opacity-70">
                    @if (in_array($verificationType, ['sender_verify', 'pickup']))
                        تایید فرستنده با موفقیت انجام شد. کد تایید را هنگام تحویل بسته به پیک ارائه دهید.
                    @else
                        تایید گیرنده با موفقیت انجام شد. کد تایید را هنگام دریافت بسته از پیک ارائه دهید.
                    @endif
                </p>
            @else
                {{-- Error Icon --}}
                @if ($errorCode === 'expired')
                    <div class="w-20 h-20 rounded-full bg-warning/10 flex items-center justify-center">
                        <x-icon name="o-clock" class="w-12 h-12 text-warning" />
                    </div>
                    <h1 class="text-2xl font-bold text-warning">لینک منقضی شده است</h1>
                @elseif ($errorCode === 'locked')
                    <div class="w-20 h-20 rounded-full bg-error/10 flex items-center justify-center">
                        <x-icon name="o-lock-closed" class="w-12 h-12 text-error" />
                    </div>
                    <h1 class="text-2xl font-bold text-error">لینک قفل شده است</h1>
                @else
                    <div class="w-20 h-20 rounded-full bg-error/10 flex items-center justify-center">
                        <x-icon name="o-exclamation-circle" class="w-12 h-12 text-error" />
                    </div>
                    <h1 class="text-2xl font-bold text-error">خطا در تایید</h1>
                @endif

                {{-- Error Message --}}
                <div class="bg-base-200 rounded-lg p-4 w-full">
                    <p class="text-sm">{{ $errorMessage }}</p>
                </div>
            @endif

        </div>
    </div>
</div>
