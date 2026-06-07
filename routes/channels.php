<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId);
    return $order && (int) $user->id == (int) $order->user_id;
});

Broadcast::channel('restaurant.{restaurantId}.orders', function ($user, $restaurantId) {
    return $user->restaurant && (int) $user->restaurant->id == (int) $restaurantId;
});
