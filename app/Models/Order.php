<?php

namespace App\Models;

use App\Events\OrderUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'total' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeUnarchived($query)
    {
        return $query->whereNull('archived_at');
    }

    public function statusLabel(): string
    {
        return str_replace('_', ' ', str($this->status)->title()->toString());
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['delivered', 'cancelled'], true);
    }

    public function broadcastRealtimeUpdate(string $type = 'status_updated', ?string $previousStatus = null): void
    {
        $order = $this->fresh(['restaurant.user', 'user', 'orderItems.menuItem'])
            ?? $this->loadMissing(['restaurant.user', 'user', 'orderItems.menuItem']);

        try {
            broadcast(new OrderUpdated($order, $type, $previousStatus));
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Broadcast failed: ' . $e->getMessage());
        }

        try {
            $pushService = app(\App\Services\PushNotificationService::class);

            if ($type === 'status_updated' && $order->user) {
                \Illuminate\Support\Facades\Log::info('Sending push to customer #' . $order->user->id . ' for order #' . $order->id);
                $pushService->sendToUser($order->user, [
                    'title' => 'Order #' . $order->id . ' Update',
                    'body' => 'Your order status changed to ' . $order->statusLabel(),
                    'url' => route('order.confirmation', ['order' => $order->id], false),
                    'tag' => 'order-' . $order->id,
                ]);
            }

            if ($type === 'created' && $order->restaurant?->user) {
                \Illuminate\Support\Facades\Log::info('Sending push to owner #' . $order->restaurant->user->id . ' for new order #' . $order->id);
                $pushService->sendToUser($order->restaurant->user, [
                    'title' => 'New Order #' . $order->id,
                    'body' => ($order->user?->name ?? 'A customer') . ' placed a new order.',
                    'url' => route('owner.orders', ['status' => 'pending'], false),
                    'tag' => 'owner-order-' . $order->id,
                    'audience' => 'owner',
                ]);
            }
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Push notification failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
