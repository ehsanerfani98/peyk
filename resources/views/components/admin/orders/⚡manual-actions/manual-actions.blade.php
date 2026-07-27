<div>
    <x-header title="اقدامات دستی - سفارش #{{ $order->id }}" separator progress-indicator>
        <x-slot:actions>
            <x-button label="بازگشت به جزئیات" icon="o-arrow-right" link="{{ route('admin.orders.show', $order) }}"
                class="btn-ghost btn-sm" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- لغو سفارش --}}
        <x-card title="لغو سفارش" icon="o-x-circle" shadow>
            <p class="text-sm text-base-500 mb-2">با لغو سفارش، وضعیت به CANCELLED تغییر می‌کند.</p>
            <x-form wire:submit="cancelOrder">
                <x-input label="دلیل لغو" wire:model="cancelReason" placeholder="دلیل لغو را وارد کنید..." />
                <x-slot:actions>
                    <x-button label="لغو سفارش" type="submit" class="btn-error" spinner="cancelOrder"
                        wire:confirm="از لغو این سفارش اطمینان دارید؟" />
                </x-slot:actions>
            </x-form>
        </x-card>

        {{-- تغییر وضعیت --}}
        <x-card title="تغییر وضعیت" icon="o-arrows-right-left" shadow>
            <p class="text-sm text-base-500 mb-2">وضعیت فعلی: <x-badge :value="$order->status" /></p>
            <x-form wire:submit="changeStatus">
                <x-select label="وضعیت جدید" wire:model="newStatus" :options="array_map(fn($s) => ['id' => $s, 'name' => $s], $this->availableStatuses)"
                    placeholder="انتخاب وضعیت" />
                <x-slot:actions>
                    <x-button label="تغییر وضعیت" type="submit" class="btn-warning" spinner="changeStatus" />
                </x-slot:actions>
            </x-form>
        </x-card>

        {{-- تخصیص پیک --}}
        <x-card title="تخصیص پیک" icon="o-bolt" shadow>
            <p class="text-sm text-base-500 mb-2">یک پیک را به این سفارش تخصیص دهید.</p>
            <x-form wire:submit="assignCourier">
                <x-select label="انتخاب پیک" wire:model="selectedCourierId" :options="$this->availableCouriers"
                    placeholder="انتخاب پیک..." />
                <x-slot:actions>
                    <x-button label="تخصیص پیک" type="submit" class="btn-primary" spinner="assignCourier" />
                </x-slot:actions>
            </x-form>
        </x-card>

        {{-- تغییر قیمت --}}
        <x-card title="تغییر قیمت" icon="o-banknotes" shadow>
            <p class="text-sm text-base-500 mb-2">قیمت فعلی: {{ number_format($order->price) }} تومان</p>
            <x-form wire:submit="updatePrice">
                <x-input label="قیمت جدید" wire:model="newPrice" type="number" prefix="تومان" />
                <x-slot:actions>
                    <x-button label="به‌روزرسانی قیمت" type="submit" class="btn-primary" spinner="updatePrice" />
                </x-slot:actions>
            </x-form>
        </x-card>

        {{-- بازپرداخت --}}
        <x-card title="بازپرداخت" icon="o-arrow-uturn-left" shadow>
            <p class="text-sm text-base-500 mb-2">حداکثر مبلغ: {{ number_format($order->price) }} تومان</p>
            <x-form wire:submit="processRefund">
                <x-input label="مبلغ بازپرداخت" wire:model="refundAmount" type="number" prefix="تومان" />
                <x-input label="دلیل بازپرداخت" wire:model="refundReason" placeholder="دلیل را وارد کنید..." />
                <x-slot:actions>
                    <x-button label="ثبت بازپرداخت" type="submit" class="btn-error" spinner="processRefund"
                        wire:confirm="از ثبت بازپرداخت اطمینان دارید؟" />
                </x-slot:actions>
            </x-form>
        </x-card>

        {{-- تایید دستی تحویل --}}
        <x-card title="تایید دستی تحویل" icon="o-check-circle" shadow>
            <p class="text-sm text-base-500 mb-2">بدون نیاز به کد تایید، سفارش را تحویل شده اعلام کنید.</p>
            <x-button label="تایید تحویل" wire:click="forceConfirmDelivery" class="btn-success"
                wire:confirm="از تحویل این سفارش اطمینان دارید؟" spinner="forceConfirmDelivery" />
        </x-card>

        {{-- ارسال مجدد پیامک --}}
        <x-card title="ارسال مجدد پیامک" icon="o-chat-bubble-left-ellipsis" shadow>
            <x-form wire:submit="resendVerificationSms">
                <x-select label="نوع تایید" wire:model="verificationType" placeholder="انتخاب کنید..."
                    :options="[['id' => 'sender', 'name' => 'فرستنده'], ['id' => 'receiver', 'name' => 'گیرنده']]" />
                <x-slot:actions>
                    <x-button label="ارسال مجدد" type="submit" class="btn-primary" spinner="resendVerificationSms" />
                </x-slot:actions>
            </x-form>
        </x-card>
    </div>
</div>
