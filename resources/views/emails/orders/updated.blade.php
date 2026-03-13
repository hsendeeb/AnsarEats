<x-mail::message>
# Order Status Update

Hi {{ $order->user->name }},

Your order from **{{ $order->restaurant->name }}** has been updated!

# Current Status: **{{ strtoupper(str_replace('_', ' ', $order->status)) }}**

@if($order->status === 'accepted')
The restaurant has accepted your order and is starting to prepare it soon.
@elseif($order->status === 'preparing')
The restaurant is currently cooking/preparing your delicious meal!
@elseif($order->status === 'out_for_delivery')
🚀 **Hold tight! Your food has left the restaurant and is on its way to you.**
@elseif($order->status === 'delivered')
✅ Your food has been delivered! Enjoy your meal.
@elseif($order->status === 'cancelled' || $order->status === 'rejected')
Unfortunately, the restaurant could not fulfill your order at this time.
@if($order->rejection_reason)
**Reason:** {{ $order->rejection_reason }}
@endif
@endif

<x-mail::button :url="route('order.confirmation', $order)">
Track Your Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
