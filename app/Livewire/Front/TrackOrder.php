<?php

namespace App\Livewire\Front;

use App\Models\Order;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TrackOrder extends Component
{
    #[Locked]
    public string $token;

    public function mount(string $token): void
    {
        $this->token = $token;
        $order = Order::where('tracking_token', $token)->firstOrFail();
        \App\Support\CustomerOrders::remember($order);
    }

    public function getListeners(): array
    {
        return ["echo:order.{$this->token},OrderChanged" => '$refresh'];
    }

    public function render()
    {
        return view('livewire.front.track-order', ['order' => Order::with(['items', 'updates'])->where('tracking_token', $this->token)->firstOrFail()])->layout('layouts.app');
    }
}
