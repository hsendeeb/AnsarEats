<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlaceOrderFromCart
{
    public function handle(User $customer, array $cartData, array $checkoutData): Order
    {
        return DB::transaction(function () use ($customer, $cartData, $checkoutData) {
            $order = Order::create([
                'user_id' => $customer->id,
                'restaurant_id' => $cartData['restaurant_id'],
                'delivery_address' => $checkoutData['delivery_address'],
                'phone' => $checkoutData['phone'],
                'notes' => $checkoutData['notes'] ?? null,
                'total' => $cartData['total'] + $cartData['delivery_fee'],
                'delivery_fee' => $cartData['delivery_fee'],

                'discount_amount' => $cartData['discount'] ?? 0,
                'promotion_id' => $cartData['promo']['id'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($cartData['items'] as $item) {
                $order->orderItems()->create([
                    'menu_item_id' => $item['id'],
                    'name' => $item['name'],
                    'variant_label' => $item['variant'] ?? null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $order;
        }, 3);
    }
}
