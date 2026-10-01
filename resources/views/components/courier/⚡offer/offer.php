<?php

use App\Services\Order\CourierOfferService;
use App\Services\Order\Exceptions\OrderStateException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.guest')] #[Title('پیشنهاد سفارش')] class extends Component
{
    public string $token;

    public ?int $orderId = null;

    public ?string $pickupAddress = null;

    public ?string $deliveryAddress = null;

    public ?string $price = null;

    public string $state = 'loading';

    public bool $isAccepted = false;

    public ?string $errorMessage = null;

    public function mount(string $token, CourierOfferService $offerService): void
    {
        $this->token = $token;

        $this->loadOffer($offerService);
    }

    /**
     * پذیرش پیشنهاد با کلیک پیک روی دکمه تایید در مرورگر (لینک پیامکی).
     */
    public function confirm(CourierOfferService $offerService): void
    {
        if ($this->state !== 'offered') {
            return;
        }

        try {
            $offerService->acceptByOfferToken($this->token);

            $this->isAccepted = true;
            $this->state = 'accepted';
        } catch (OrderStateException $e) {
            $this->loadOffer($offerService);

            if ($this->state === 'offered') {
                $this->errorMessage = $e->getMessage();
            }
        }
    }

    /**
     * خواندن سفارش از روی توکن لینک و تعیین وضعیت پیشنهاد.
     */
    private function loadOffer(CourierOfferService $offerService): void
    {
        $order = $offerService->findByOfferToken($this->token);

        if (! $order) {
            $this->state = 'invalid';
            $this->errorMessage = 'این لینک پیشنهاد سفارش معتبر نیست یا منقضی شده است.';

            return;
        }

        $this->orderId = $order->id;
        $this->pickupAddress = $order->sender_address;
        $this->deliveryAddress = $order->receiver_address;
        $this->price = number_format((float) $order->price);

        $this->state = $offerService->offerLinkState($order);

        if ($this->state === 'accepted') {
            $this->isAccepted = true;
            $this->errorMessage = null;
        } elseif ($this->state === 'expired') {
            $this->errorMessage = 'این پیشنهاد دیگر معتبر نیست (رد شده، منقضی شده یا سفارش لغو شده است).';
        }
    }
};
