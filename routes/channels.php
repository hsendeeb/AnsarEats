<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId, ['*']);
    return $order && $user->id === $order->user_id;
});
