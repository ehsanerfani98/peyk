<?php

use App\Models\Order;
use App\Models\Refund;
use App\Services\Admin\ActivityLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('جزئیات تراکنش')] class extends Component
{
    use Toast;

    public Order $order;

    public function mount(): void
    {
        $this->order->load('customer', 'refunds.processedBy');
    }

    public function markAsPaid(): void
    {
        abort_unless(auth()->user()->can('manage payments'), 403);

        $this->order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        app(ActivityLogger::class)->log('payment.manual_mark_paid', $this->order);
        $this->success('پرداخت به صورت دستی ثبت شد', position: 'toast-bottom toast-end');
    }

    public function processRefund(): void
    {
        abort_unless(auth()->user()->can('process refunds'), 403);

        if ($this->order->payment_status !== 'paid') {
            $this->error('این سفارش هنوز پرداخت نشده است', position: 'toast-bottom toast-end');

            return;
        }

        Refund::create([
            'order_id' => $this->order->id,
            'amount' => $this->order->price,
            'reason' => 'بازپرداخت دستی توسط مدیر',
            'processed_by' => auth()->id(),
            'status' => 'completed',
            'refunded_at' => now(),
        ]);

        $this->order->update(['payment_status' => 'pending']);

        app(ActivityLogger::class)->log('payment.refund_processed', $this->order);
        $this->success('بازپرداخت با موفقیت انجام شد', position: 'toast-bottom toast-end');
    }
};
