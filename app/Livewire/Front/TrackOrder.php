<?php

namespace App\Livewire\Front;

use App\Models\Order;
use App\Support\CustomerOrders;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class TrackOrder extends Component
{
    #[Locked]
    public string $token;

    public function mount(string $token): void
    {
        $this->token = $token;
        $order = Order::where('tracking_token', $token)->firstOrFail();
        CustomerOrders::remember($order);
    }

    #[On('order-status-changed')]
    public function refreshOrder(): void {}

    public function render()
    {
        return view('livewire.front.track-order', ['order' => Order::with(['items', 'updates'])->where('tracking_token', $this->token)->firstOrFail()])->layout('layouts.app');
    }
}
