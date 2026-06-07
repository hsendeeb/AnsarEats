<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OwnerNewOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_order_push_notification_is_sent_to_restaurant_owner(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $customer = User::factory()->create(['name' => 'Hungry Customer']);
        $restaurant = Restaurant::factory()->create(['user_id' => $owner->id]);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'status' => 'pending',
        ]);

        $pushNotifications = Mockery::mock(PushNotificationService::class);
        $pushNotifications->shouldReceive('sendToUser')
            ->once()
            ->withArgs(function (User $notifiedUser, array $payload) use ($owner, $order): bool {
                return $notifiedUser->is($owner)
                    && $payload['title'] === 'New Order #' . $order->id
                    && $payload['body'] === 'Hungry Customer placed a new order.'
                    && $payload['url'] === route('owner.orders', ['status' => 'pending'], false)
                    && $payload['tag'] === 'owner-order-' . $order->id
                    && $payload['audience'] === 'owner';
            });

        $this->app->instance(PushNotificationService::class, $pushNotifications);

        $order->broadcastRealtimeUpdate('created');
    }
}
