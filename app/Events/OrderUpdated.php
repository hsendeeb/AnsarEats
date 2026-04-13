<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcast   
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $type = 'status_updated',
        public ?string $previousStatus = null,
    ) {
    }

    public function broadcastAs(): string
    {
        return 'order.updated';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->order->id),
            new PrivateChannel('restaurant.' . $this->order->restaurant_id . '.orders'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'previous_status' => $this->previousStatus,
            'order' => [
                'id' => $this->order->id,
                'restaurant_id' => $this->order->restaurant_id,
                'user_id' => $this->order->user_id,
                'status' => $this->order->status,
                'status_label' => $this->order->statusLabel(),
                'estimated_prep_time' => $this->order->estimated_prep_time,
                'is_terminal' => $this->order->isTerminal(),
                'created_at' => optional($this->order->created_at)->toIso8601String(),
                'updated_at' => optional($this->order->updated_at)->toIso8601String(),
            ],
            'message' => $this->type === 'created'
                ? 'A new order has been placed.'
                : 'Order status updated to ' . $this->order->statusLabel() . '.',
        ];
    }
}
