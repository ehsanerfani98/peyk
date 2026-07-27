<div class="min-h-screen flex items-center justify-center bg-base-200 px-4 py-8">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body gap-4">

            @if ($isLoading)
                {{-- Loading State --}}
                <div class="flex flex-col items-center justify-center gap-4 py-8">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                    <p class="text-sm opacity-70">در حال بارگذاری...</p>
                </div>

            @elseif ($errorMessage)
                {{-- Error State --}}
                <div class="flex flex-col items-center text-center gap-4">
                    <div class="w-20 h-20 rounded-full bg-error/10 flex items-center justify-center">
                        <x-icon name="o-exclamation-circle" class="w-12 h-12 text-error" />
                    </div>
                    <h1 class="text-2xl font-bold text-error">خطا</h1>
                    <div class="bg-base-200 rounded-lg p-4 w-full">
                        <p class="text-sm">{{ $errorMessage }}</p>
                    </div>
                    <a href="scheme://app" class="btn btn-error btn-wide">
                        <x-icon name="o-arrow-right" class="w-5 h-5" />
                        بازگشت به اپلیکیشن
                    </a>
                </div>

            @elseif ($isSubmitted)
                {{-- Success State --}}
                <div class="flex flex-col items-center text-center gap-4">
                    <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center">
                        <x-icon name="o-check-circle" class="w-12 h-12 text-success" />
                    </div>
                    <h1 class="text-2xl font-bold text-success">نظر شما ثبت شد</h1>
                    <p class="text-sm opacity-70">
                        از اینکه وقت گذاشتید و نظر خود را ثبت کردید، سپاسگزاریم.
                    </p>
                    <a href="scheme://app" class="btn btn-success btn-wide">
                        <x-icon name="o-arrow-right" class="w-5 h-5" />
                        بازگشت به اپلیکیشن
                    </a>
                </div>

            @else
                {{-- Survey Form --}}
                <div class="text-center">
                    <h1 class="text-2xl font-bold mb-2">نظرسنجی</h1>
                    <p class="text-sm opacity-70">
                        {{ $this->surveyTypeLabel() }} گرامی، لطفاً نظر خود را درباره سفارش ثبت کنید.
                    </p>
                </div>

                {{-- Order Info --}}
                @if ($orderDescription)
                    <div class="bg-base-200 rounded-lg p-3">
                        <span class="text-xs opacity-70">شرح بسته:</span>
                        <p class="text-sm font-medium mt-1">{{ $orderDescription }}</p>
                    </div>
                @endif

                <x-form wire:submit="submit">
                    {{-- Star Rating --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">امتیاز شما</span>
                            <span class="label-text-alt text-error">*</span>
                        </label>
                        <div class="flex justify-center gap-1 py-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    wire:click="setRating({{ $i }})"
                                    class="text-3xl transition-transform hover:scale-110 focus:outline-none
                                        {{ $i <= $rating ? 'text-warning' : 'text-base-300' }}"
                                >
                                    <x-icon name="o-star" class="w-8 h-8" />
                                </button>
                            @endfor
                        </div>
                        @error('rating')
                            <span class="text-error text-xs text-center mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Comment --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">نظر شما (اختیاری)</span>
                        </label>
                        <textarea
                            wire:model="comment"
                            class="textarea textarea-bordered w-full h-24"
                            placeholder="نظر خود را بنویسید..."
                            maxlength="1000"
                        ></textarea>
                        <label class="label">
                            <span class="label-text-alt opacity-50">{{ mb_strlen($comment) }}/۱۰۰۰</span>
                        </label>
                        @error('comment')
                            <span class="text-error text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <x-slot:actions>
                        <x-button
                            label="ثبت نظر"
                            type="submit"
                            class="btn-primary btn-wide"
                            spinner="submit"
                        />
                    </x-slot:actions>
                </x-form>
            @endif

        </div>
    </div>
</div>
