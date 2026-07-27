<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.guest')] #[Title('پرداخت موفق')] class extends Component
{
    public int $orderId;

    public ?string $refId = null;

    public function mount(int $orderId, ?string $refId = null): void
    {
        $this->orderId = $orderId;
        $this->refId = $refId;
    }
};
