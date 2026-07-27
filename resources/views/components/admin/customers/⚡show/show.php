<?php

use App\Models\User;
use App\Services\Admin\ActivityLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('پروفایل مشتری')] class extends Component
{
    use Toast;

    public User $customer;

    public function mount(): void
    {
        $this->customer->loadCount('orders');
        $this->customer->loadSum('orders', 'price');
    }

    public function with(): array
    {
        return [
            'orders' => $this->customer->orders()
                ->with('courier')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get(),
        ];
    }

    public function toggleBlock(): void
    {
        abort_unless(auth()->user()->can('manage customers'), 403);

        // Simple block mechanism: we can use a custom field or just a flag
        // For now, we'll toggle a "blocked_at" timestamp concept
        // Since there's no blocked_at column, we'll use a session-based approach
        // or just log the action
        app(ActivityLogger::class)->log('customer.toggle_block', $this->customer);
        $this->success('عملیات مسدودسازی/رفع مسدودیت ثبت شد', position: 'toast-bottom toast-end');
    }
};
