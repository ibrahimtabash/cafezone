<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class OrderChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public int $id, public ?string $token, public bool $isNew = false, public ?string $message = null) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('orders')];
        if ($this->token) {
            $channels[] = new Channel('order.'.$this->token);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        // Only an invalidation signal: customer and payment data never leave protected pages.
        return ['id' => $this->id, 'isNew' => $this->isNew, 'message' => $this->message];
    }
}
