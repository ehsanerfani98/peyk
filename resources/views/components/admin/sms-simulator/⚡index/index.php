<?php

use App\Models\Setting;
use App\Models\SmsMessage;
use App\Services\Admin\ActivityLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('شبیه‌ساز پیامک')] class extends Component
{
    use Toast;
    use WithPagination;

    #[Url]
    public string $search = '';

    public bool $showClearModal = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'isSimulatorMode' => $this->isSimulatorMode(),
            'messages' => SmsMessage::query()
                ->when($this->search, fn ($q) => $q->where(function ($q) {
                    $q->where('receiver', 'like', "%{$this->search}%")
                        ->orWhere('sender', 'like', "%{$this->search}%")
                        ->orWhere('content', 'like', "%{$this->search}%")
                        ->orWhere('pattern_code', 'like', "%{$this->search}%");
                }))
                ->latest()
                ->paginate(20),
        ];
    }

    /**
     * بررسی فعال بودن حالت شبیه‌ساز پیامک.
     */
    public function isSimulatorMode(): bool
    {
        return Setting::getValue('sms_mode', config('sms_simulator.mode', 'simulator')) !== 'real';
    }

    /**
     * خالی کردن کامل جدول پیامک‌های شبیه‌ساز.
     */
    public function clearMessages(): void
    {
        abort_unless(auth()->user()->can('clear sms simulator'), 403);

        SmsMessage::query()->delete();

        app(ActivityLogger::class)->log('sms_simulator.cleared', auth()->user());

        $this->showClearModal = false;
        $this->resetPage();

        $this->success('جدول پیامک‌های شبیه‌ساز خالی شد', position: 'toast-bottom toast-end');
    }
};
