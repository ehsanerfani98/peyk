<?php

use App\Services\Order\Exceptions\OrderVerificationException;
use App\Services\Order\OrderVerificationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.guest')] #[Title('تایید سفارش')] class extends Component
{
    public string $token;

    public ?string $verificationType = null;

    public ?string $verificationCode = null;

    public ?string $errorMessage = null;

    public ?string $errorCode = null;

    public bool $isSuccess = false;

    public function mount(string $token, OrderVerificationService $verificationService): void
    {
        $this->token = $token;

        try {
            $verification = $verificationService->confirmByToken($token);

            $this->isSuccess = true;
            $this->verificationType = $verification->type;
            $this->verificationCode = $verification->code;
        } catch (OrderVerificationException $e) {
            $this->errorMessage = $e->getMessage();
            $this->errorCode = $e->errorCode();
        }
    }
};
