<x-mail::message>
# Order Confirmation

Hi {{ $order->user->name }},

Thank you for your order from **{{ $order->restaurant->name }}**! We've received your request and the restaurant is currently reviewing it.

<x-mail::table>
| Item | Qty | Price |
| :--- | :---: | :--- |
@foreach($order->orderItems as $item)
| {{ $item->name }} {{ $item->variant_label ? "($item->variant_label)" : "" }} | {{ $item->quantity }} | ${{ number_format($item->price * $item->quantity, 2) }} |
@endforeach
| **Total** | | **${{ number_format($order->total, 2) }}** |
</x-mail::table>

**Delivery Address:**
{{ $order->delivery_address }}

**Notes:**
{{ $order->notes ?? 'No special notes.' }}

<x-mail::button :url="route('order.confirmation', $order)">
View Order Status
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
