<?php

namespace App\Livewire\Front;

use App\Models\Order;
use App\Support\CustomerOrders;
use Livewire\Component;

class MyOrders extends Component
{
    public function render()
    {
        return view('livewire.front.my-orders', [
            'orders' => Order::whereIn('tracking_token', CustomerOrders::tokens())->latest('id')->get(),
        ])->layout('layouts.app');
    }
}
