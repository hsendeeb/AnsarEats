<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $restaurant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->restaurant = Restaurant::factory()->create(['user_id' => $this->owner->id]);
    }

    public function test_owner_can_toggle_restaurant_status()
    {
        $initialStatus = $this->restaurant->is_open;
        $response = $this->actingAs($this->owner)->post(route('owner.restaurant.toggle-status'));
        
        $response->assertRedirect();
        $this->assertEquals(!$initialStatus, $this->restaurant->fresh()->is_open);
    }

    public function test_owner_can_update_restaurant_delivery_fee()
    {
        $response = $this->actingAs($this->owner)->post(route('owner.restaurant.store'), [
            'name' => $this->restaurant->name,
            'description' => $this->restaurant->description,
            'address' => $this->restaurant->address,
            'phone' => $this->restaurant->phone,
            'delivery_fee' => 4.50,
            'operating_hours' => [],
            'is_open' => 1,
        ]);

        $response->assertRedirect();
        $this->assertEquals(4.5, (float) $this->restaurant->fresh()->delivery_fee);
    }

    public function test_owner_can_accept_order_with_prep_time()
    {
        $order = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.order.accept', $order), [
            'estimated_prep_time' => 30
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('accepted', $order->status);
        $this->assertEquals(30, $order->estimated_prep_time);
    }

    public function test_owner_can_reject_order_with_reason()
    {
        $order = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.order.reject', $order), [
            'rejection_reason' => 'Too busy'
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('Too busy', $order->rejection_reason);
    }

    public function test_owner_can_toggle_category_visibility()
    {
        $category = MenuCategory::factory()->create(['restaurant_id' => $this->restaurant->id]);
        $initialVisibility = $category->fresh()->is_visible;

        $response = $this->actingAs($this->owner)->post(route('owner.category.toggle-visibility', $category));

        $response->assertRedirect();
        $this->assertEquals(!$initialVisibility, $category->fresh()->is_visible);
    }

    public function test_owner_can_toggle_item_featured_status()
    {
        $category = MenuCategory::factory()->create(['restaurant_id' => $this->restaurant->id]);
        $item = MenuItem::factory()->create(['menu_category_id' => $category->id]);
        $initialFeatured = $item->is_featured;

        $response = $this->actingAs($this->owner)->post(route('owner.menu-item.toggle-featured', $item));

        $response->assertRedirect();
        $this->assertEquals(!$initialFeatured, $item->fresh()->is_featured);
    }

    public function test_owner_can_manage_promotions()
    {
        // Store
        $response = $this->actingAs($this->owner)->post(route('owner.promotion.store'), [
            'code' => 'SAVE20',
            'discount_percentage' => 20
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('promotions', [
            'restaurant_id' => $this->restaurant->id,
            'code' => 'SAVE20',
            'discount_percentage' => 20
        ]);

        $promotion = Promotion::where('code', 'SAVE20')->first();

        // Destroy
        $response = $this->actingAs($this->owner)->delete(route('owner.promotion.destroy', $promotion));
        $response->assertRedirect();
        $this->assertDatabaseMissing('promotions', ['id' => $promotion->id]);
    }
    public function test_user_can_apply_promo_at_checkout()
    {
        $user = User::factory()->create();
        $promotion = Promotion::create([
            'restaurant_id' => $this->restaurant->id,
            'code' => 'DISCOUNT10',
            'discount_percentage' => 10,
            'is_active' => true
        ]);

        $category = MenuCategory::factory()->create(['restaurant_id' => $this->restaurant->id]);
        $item = MenuItem::factory()->create(['menu_category_id' => $category->id, 'price' => 100]);

        // Mock session cart
        session(['cart' => [
            'restaurant_id' => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
            'items' => [
                $item->id . '||100.00' => [
                    'key' => $item->id . '||100.00',
                    'id' => $item->id,
                    'name' => 'Pizza',
                    'price' => 100,
                    'quantity' => 1,
                    'variant' => null
                ]
            ]
        ]]);

        $response = $this->actingAs($user)->post(route('cart.promo'), [
            'code' => 'DISCOUNT10'
        ]);

        $response->assertStatus(200);
        $cart = session('cart');
        $this->assertEquals('DISCOUNT10', $cart['promo']['code']);
        
        $responseData = $response->json();
        $this->assertEquals(90, $responseData['total']);
        $this->assertEquals(10, $responseData['discount']);
    }

    public function test_owner_can_archive_a_single_order(): void
    {
        $order = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => null,
            'name' => 'Test Meal',
            'price' => 12.00,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->owner)->delete(route('owner.order.destroy', $order));

        $response->assertRedirect();
        $this->assertNotNull($order->fresh()->archived_at);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('order_items', ['id' => $orderItem->id]);
    }

    public function test_owner_dashboard_revenue_only_counts_accepted_workflow_orders(): void
    {
        Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'pending',
            'total' => 10,
        ]);

        Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'accepted',
            'total' => 25,
        ]);

        Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'preparing',
            'total' => 30,
        ]);

        Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'delivered',
            'total' => 45,
        ]);

        Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'cancelled',
            'total' => 60,
        ]);

        $dashboardResponse = $this->actingAs($this->owner)->get(route('owner.dashboard'));

        $dashboardResponse->assertOk();
        $dashboardResponse->assertViewHas('stats', function (array $stats) {
            return (float) $stats['total_revenue'] === 100.0
                && (float) $stats['avg_order_value'] === (100.0 / 3);
        });
        $dashboardResponse->assertSee('$100.00', false);
        $dashboardResponse->assertSee('$33.33', false);
    }

    public function test_owner_orders_preparing_filter_includes_preparing_and_out_for_delivery_orders(): void
    {
        $preparingOrder = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'preparing',
        ]);

        $outForDeliveryOrder = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'out_for_delivery',
        ]);

        Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.orders', ['status' => 'preparing']));

        $response->assertOk();
        $response->assertViewHas('statusCounts', function (array $statusCounts) {
            return ($statusCounts['preparing'] ?? 0) === 2;
        });
        $response->assertViewHas('orders', function ($orders) use ($preparingOrder, $outForDeliveryOrder) {
            $ids = collect($orders->items())->pluck('id')->sort()->values()->all();

            return $ids === collect([$preparingOrder->id, $outForDeliveryOrder->id])->sort()->values()->all();
        });
    }

    public function test_owner_can_archive_all_visible_restaurant_orders_without_removing_metrics_data(): void
    {
        $orderOne = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'delivered',
            'total' => 10,
        ]);

        $orderTwo = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'delivered',
            'total' => 15,
        ]);

        OrderItem::create([
            'order_id' => $orderOne->id,
            'menu_item_id' => null,
            'name' => 'Meal One',
            'price' => 10.00,
            'quantity' => 1,
        ]);

        OrderItem::create([
            'order_id' => $orderTwo->id,
            'menu_item_id' => null,
            'name' => 'Meal Two',
            'price' => 15.00,
            'quantity' => 1,
        ]);

        $otherRestaurantOrder = Order::factory()->create([
            'status' => 'delivered',
            'total' => 99,
        ]);

        $response = $this->actingAs($this->owner)->delete(route('owner.orders.clear'));

        $response->assertRedirect();
        $this->assertNotNull($orderOne->fresh()->archived_at);
        $this->assertNotNull($orderTwo->fresh()->archived_at);
        $this->assertSame(0, Order::where('restaurant_id', $this->restaurant->id)->unarchived()->count());
        $this->assertSame(2, OrderItem::whereIn('order_id', [$orderOne->id, $orderTwo->id])->count());
        $this->assertDatabaseHas('orders', ['id' => $otherRestaurantOrder->id]);

        $dashboardResponse = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('$25.00', false);
        $dashboardResponse->assertSee('$12.50', false);
    }
}
