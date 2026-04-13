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
        $order = $this->fresh(['restaurant', 'user', 'orderItems.menuItem']) ?? $this->loadMissing(['restaurant', 'user', 'orderItems.menuItem']);

        try {
            $pendingBroadcast = broadcast(new OrderUpdated($order, $type, $previousStatus))->toOthers();
            unset($pendingBroadcast);

            // Send actual Web Push Background Notification
            if ($type === 'status_updated' && $this->user) {
                app(\App\Services\PushNotificationService::class)->sendToUser($this->user, [
                    'title' => 'Order #' . $this->id . ' Update',
                    'body' => 'Your order status changed to ' . $this->statusLabel(),
                    'url' => route('profile.orders', [], false),
                    'tag' => 'order-' . $this->id,
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Realtime order broadcast failed.', [
                'order_id' => $this->id,
                'type' => $type,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
