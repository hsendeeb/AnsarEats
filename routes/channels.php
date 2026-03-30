<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId, ['*']);
    return $order && $user->id === $order->user_id;
});

Broadcast::channel('restaurant.{restaurantId}.orders', function ($user, $restaurantId) {
    return (int) optional($user->restaurant)->id === (int) $restaurantId;
});
