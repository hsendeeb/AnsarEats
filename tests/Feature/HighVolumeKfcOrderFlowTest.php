<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HighVolumeKfcOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_kfc_can_receive_one_hundred_orders_without_breaking_the_checkout_flow(): void
    {
        Mail::fake();

        $owner = User::factory()->create([
            'name' => 'hsendeeb',
            'email' => 'hsendeeb2@gmail.com',
            'role' => 'owner',
        ]);

        $restaurant = Restaurant::factory()->create([
            'user_id' => $owner->id,
            'name' => 'KFC',
            'is_open' => true,
            'delivery_fee' => 2.50,
        ]);

        $category = MenuCategory::factory()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Buckets',
        ]);

        $menuItem = MenuItem::factory()->create([
            'menu_category_id' => $category->id,
            'name' => 'Zinger Box',
            'price' => 14.00,
            'is_available' => true,
        ]);

        for ($i = 1; $i <= 100; $i++) {
            $customer = User::factory()->create([
                'name' => "Customer {$i}",
                'email' => "customer{$i}@example.com",
            ]);

            $addToCartResponse = $this->actingAs($customer)->postJson(route('cart.add'), [
                'menu_item_id' => $menuItem->id,
                'quantity' => 2,
            ]);

            $addToCartResponse->assertOk();
            $addToCartResponse->assertJsonPath('cart.restaurant_id', $restaurant->id);
            $addToCartResponse->assertJsonPath('cart.total', 30.5);

            $checkoutResponse = $this->actingAs($customer)->post(route('checkout.place'), [
                'delivery_address' => "Beirut Street {$i}",
                'phone' => '70123456',
                'notes' => "Stress test order {$i}",
            ]);

            $checkoutResponse->assertRedirect();
        }

        $restaurant->refresh();

        $this->assertSame(100, Order::where('restaurant_id', $restaurant->id)->count());
        $this->assertSame(100, OrderItem::whereHas('order', fn ($query) => $query->where('restaurant_id', $restaurant->id))->count());
        $this->assertSame(100, Order::where('restaurant_id', $restaurant->id)->where('status', 'pending')->count());
        $this->assertEquals(3050.0, (float) Order::where('restaurant_id', $restaurant->id)->sum('total'));

        $ownerDashboardResponse = $this->actingAs($owner)->get(route('owner.dashboard'));
        $ownerDashboardResponse->assertOk();
        $ownerDashboardResponse->assertSee('KFC');
        $ownerDashboardResponse->assertSee('100');

        $ownerOrdersResponse = $this->actingAs($owner)->get(route('owner.orders'));
        $ownerOrdersResponse->assertOk();
        $ownerOrdersResponse->assertSee('Order #');
    }
}
