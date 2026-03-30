<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_uses_sale_price_for_single_price_items(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $customer = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['user_id' => $owner->id, 'is_open' => true]);
        $category = MenuCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        $menuItem = MenuItem::factory()->create([
            'menu_category_id' => $category->id,
            'price' => 18.00,
            'is_on_sale' => true,
            'sale_price' => 12.50,
            'variants' => null,
        ]);

        $response = $this->actingAs($customer)->postJson(route('cart.add'), [
            'menu_item_id' => $menuItem->id,
            'quantity' => 1,
        ]);

        $response->assertOk();

        $cart = $response->json('cart.items');
        $this->assertCount(1, $cart);
        $this->assertSame(12.5, array_values($cart)[0]['price']);
    }

    public function test_cart_uses_variant_price_from_database_instead_of_frontend_payload(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $customer = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['user_id' => $owner->id, 'is_open' => true]);
        $category = MenuCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        $menuItem = MenuItem::factory()->create([
            'menu_category_id' => $category->id,
            'price' => 10.00,
            'variants' => [
                'type' => 'Size',
                'options' => [
                    ['label' => 'Small', 'price' => 8.00],
                    ['label' => 'Large', 'price' => 14.00],
                ],
            ],
        ]);

        $response = $this->actingAs($customer)->postJson(route('cart.add'), [
            'menu_item_id' => $menuItem->id,
            'quantity' => 1,
            'variant_label' => 'Large',
            'variant_price' => 999.99,
        ]);

        $response->assertOk();

        $cart = $response->json('cart.items');
        $this->assertCount(1, $cart);
        $this->assertSame('Large', array_values($cart)[0]['variant']);
        $this->assertEquals(14.0, array_values($cart)[0]['price']);
    }

    public function test_existing_cart_items_are_repriced_from_database(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $customer = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['user_id' => $owner->id, 'is_open' => true]);
        $category = MenuCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        $menuItem = MenuItem::factory()->create([
            'menu_category_id' => $category->id,
            'price' => 20.00,
            'is_on_sale' => true,
            'sale_price' => 15.00,
            'variants' => null,
        ]);

        $staleKey = $menuItem->id . '||20.00';

        $this->withSession([
            'cart' => [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'items' => [
                    $staleKey => [
                        'key' => $staleKey,
                        'id' => $menuItem->id,
                        'name' => $menuItem->name,
                        'price' => 20.00,
                        'image' => $menuItem->image,
                        'quantity' => 2,
                        'variant' => null,
                    ],
                ],
                'promo' => null,
            ],
        ]);

        $response = $this->actingAs($customer)->getJson(route('cart.index'));

        $response->assertOk();
        $response->assertJsonPath('subtotal', 30);
        $response->assertJsonPath('total', 30);

        $cart = $response->json('items');
        $this->assertCount(1, $cart);
        $this->assertEquals(15.0, array_values($cart)[0]['price']);
    }

    public function test_cart_total_includes_restaurant_delivery_fee(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $customer = User::factory()->create();
        $restaurant = Restaurant::factory()->create([
            'user_id' => $owner->id,
            'is_open' => true,
            'delivery_fee' => 3.75,
        ]);
        $category = MenuCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        $menuItem = MenuItem::factory()->create([
            'menu_category_id' => $category->id,
            'price' => 12.00,
        ]);

        $this->withSession([
            'cart' => [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'items' => [
                    $menuItem->id . '||12.00' => [
                        'key' => $menuItem->id . '||12.00',
                        'id' => $menuItem->id,
                        'name' => $menuItem->name,
                        'price' => 12.00,
                        'image' => $menuItem->image,
                        'quantity' => 2,
                        'variant' => null,
                    ],
                ],
                'promo' => null,
            ],
        ]);

        $response = $this->actingAs($customer)->getJson(route('cart.index'));

        $response->assertOk();
        $response->assertJsonPath('subtotal', 24);
        $response->assertJsonPath('delivery_fee', 3.75);
        $response->assertJsonPath('total', 27.75);
    }
}
