<?php

use App\Jobs\SearchCourierForOrderJob;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use App\Services\Admin\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('اقدامات دستی')] class extends Component
{
    use Toast;

    public Order $order;

    // لغو سفارش
    public string $cancelReason = '';

    // تغییر وضعیت
    public string $newStatus = '';

    public array $availableStatuses = [];

    // تخصیص پیک
    public ?int $selectedCourierId = null;

    public array $availableCouriers = [];

    // تغییر قیمت
    public ?float $newPrice = null;

    // بازپرداخت
    public ?float $refundAmount = null;

    public string $refundReason = '';

    // ارسال مجدد پیامک
    public string $verificationType = '';

    public function mount(): void
    {
        $this->newPrice = (float) $this->order->price;
        $this->availableStatuses = [
            'CREATED', 'SEARCHING_COURIER', 'COURIER_ASSIGNED',
            'WAITING_PICKUP', 'PICKED_UP', 'IN_TRANSIT',
            'DELIVERED', 'CANCELLED',
        ];
        $this->availableCouriers = User::role('courier')
            ->with('courierProfile')
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name.' ('.$u->mobile.')'])
            ->toArray();
    }

    public function cancelOrder(): void
    {
        abort_unless(auth()->user()->can('force cancel orders'), 403);

        $this->validate([
            'cancelReason' => 'required|string|min:3',
        ], [], ['cancelReason' => 'دلیل لغو']);

        DB::transaction(function () {
            $this->order->update([
                'cancelled_by' => 'admin',
                'cancel_reason' => $this->cancelReason,
                'cancelled_at' => now(),
            ]);
            $this->order->changeStatus('CANCELLED', auth()->id());
        });

        app(ActivityLogger::class)->log('order.cancelled', $this->order, null, [
            'reason' => $this->cancelReason,
        ]);
        $this->success('سفارش با موفقیت لغو شد', position: 'toast-bottom toast-end');
        $this->cancelReason = '';
    }

    public function changeStatus(): void
    {
        abort_unless(auth()->user()->can('manual order actions'), 403);

        $this->validate([
            'newStatus' => 'required|string|in:'.implode(',', $this->availableStatuses),
        ], [], ['newStatus' => 'وضعیت جدید']);

        $oldStatus = $this->order->status;
        $this->order->changeStatus($this->newStatus, auth()->id());

        app(ActivityLogger::class)->log('order.status_changed', $this->order, [
            'old_status' => $oldStatus,
        ], [
            'new_status' => $this->newStatus,
        ]);
        $this->success('وضعیت سفارش تغییر کرد', position: 'toast-bottom toast-end');
    }

    public function assignCourier(): void
    {
        abort_unless(auth()->user()->can('manual order actions'), 403);

        $this->validate([
            'selectedCourierId' => 'required|exists:users,id',
        ], [], ['selectedCourierId' => 'پیک']);

        $this->order->update([
            'courier_id' => $this->selectedCourierId,
            'assigned_at' => now(),
        ]);
        $this->order->changeStatus('COURIER_ASSIGNED', auth()->id());

        app(ActivityLogger::class)->log('order.courier_assigned', $this->order, null, [
            'courier_id' => $this->selectedCourierId,
        ]);
        $this->success('پیک با موفقیت تخصیص یافت', position: 'toast-bottom toast-end');
    }

    public function updatePrice(): void
    {
        abort_unless(auth()->user()->can('manual order actions'), 403);

        $this->validate([
            'newPrice' => 'required|numeric|min:0',
        ], [], ['newPrice' => 'قیمت جدید']);

        $oldPrice = $this->order->price;
        $this->order->update(['price' => $this->newPrice]);

        app(ActivityLogger::class)->log('order.price_changed', $this->order, [
            'old_price' => $oldPrice,
        ], [
            'new_price' => $this->newPrice,
        ]);
        $this->success('قیمت سفارش به‌روزرسانی شد', position: 'toast-bottom toast-end');
    }

    public function processRefund(): void
    {
        abort_unless(auth()->user()->can('process refunds'), 403);

        $this->validate([
            'refundAmount' => 'required|numeric|min:0|max:'.$this->order->price,
            'refundReason' => 'required|string|min:3',
        ], [], ['refundAmount' => 'مبلغ بازپرداخت', 'refundReason' => 'دلیل بازپرداخت']);

        DB::transaction(function () {
            $refund = Refund::create([
                'order_id' => $this->order->id,
                'amount' => $this->refundAmount,
                'reason' => $this->refundReason,
                'processed_by' => auth()->id(),
                'status' => 'completed',
                'refunded_at' => now(),
            ]);

            app(ActivityLogger::class)->log('order.refunded', $this->order, null, [
                'refund_id' => $refund->id,
                'amount' => $this->refundAmount,
                'reason' => $this->refundReason,
            ]);
        });

        $this->success('بازپرداخت با موفقیت ثبت شد', position: 'toast-bottom toast-end');
        $this->reset('refundAmount', 'refundReason');
    }

    public function forceConfirmDelivery(): void
    {
        abort_unless(auth()->user()->can('manual order actions'), 403);

        $this->order->update(['delivered_at' => now()]);
        $this->order->changeStatus('DELIVERED', auth()->id());

        app(ActivityLogger::class)->log('order.force_delivered', $this->order);
        $this->success('تحویل سفارش تایید شد', position: 'toast-bottom toast-end');
    }

    public function resendVerificationSms(): void
    {
        abort_unless(auth()->user()->can('manual order actions'), 403);

        $this->validate([
            'verificationType' => 'required|in:sender,receiver',
        ], [], ['verificationType' => 'نوع تایید']);

        // Dispatch a job to resend verification SMS
        SearchCourierForOrderJob::dispatch($this->order->id);

        app(ActivityLogger::class)->log('order.verification_sms_resent', $this->order, null, [
            'type' => $this->verificationType,
        ]);
        $this->success('پیامک تایید مجدداً ارسال شد', position: 'toast-bottom toast-end');
    }
};
