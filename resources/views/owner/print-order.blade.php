<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #{{ $order->id }} - Ticket</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            padding: 20px;
            max-width: 400px;
            margin: 0 auto;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .restaurant-name {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .order-info {
            margin-bottom: 15px;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 5px 0;
        }
        .items-table td {
            padding: 8px 0;
        }
        .qty { width: 40px; }
        .price { text-align: right; }
        .total-section {
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 18px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="background: #f3f4f6; padding: 10px; margin-bottom: 20px; border-radius: 8px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #000; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Print Ticket</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #fff; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; margin-left: 10px;">Close</button>
    </div>

    <div class="header">
        <h1 class="restaurant-name">{{ $order->restaurant->name }}</h1>
        <p>{{ $order->restaurant->address }}</p>
        <p>{{ $order->restaurant->phone }}</p>
    </div>

    <div class="order-info">
        <div class="details-row">
            <span>Order ID:</span>
            <strong>#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong>
        </div>
        <div class="details-row">
            <span>Date:</span>
            <span>{{ $order->created_at->format('M d, Y h:i A') }}</span>
        </div>
        <div class="details-row">
            <span>Customer:</span>
            <span>{{ $order->user->name }}</span>
        </div>
        <div class="details-row">
            <span>Phone:</span>
            <span>{{ $order->phone }}</span>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="qty">Qty</th>
                <th>Item</th>
                <th class="price">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
                <tr>
                    <td class="qty">{{ $item->quantity }}x</td>
                    <td>
                        {{ $item->name }}
                        @if($item->variant_label)
                            <br><small>({{ $item->variant_label }})</small>
                        @endif
                    </td>
                    <td class="price">${{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span>Total</span>
            <span>${{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    @if($order->estimated_prep_time)
        <div style="margin-top: 15px; padding: 10px; border: 1px solid #000; text-align: center;">
            <strong>Est. Prep Time: {{ $order->estimated_prep_time }} mins</strong>
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your order!</p>
        <p>Ordered via AnsarEats</p>
    </div>
</body>
</html>
