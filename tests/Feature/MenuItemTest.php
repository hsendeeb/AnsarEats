<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $restaurant;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'owner']);
        $this->restaurant = Restaurant::factory()->create(['user_id' => $this->user->id]);
        $this->category = MenuCategory::factory()->create(['restaurant_id' => $this->restaurant->id]);
    }

    public function test_owner_can_create_menu_item_with_variants()
    {
        $response = $this->actingAs($this->user)->post(route('owner.menu-item.store'), [
            'menu_category_id' => $this->category->id,
            'name' => 'Burger',
            'description' => 'Delicious burger',
            'price' => 10.00,
            'has_variants' => 1,
            'variant_type' => 'Size',
            'variant_names' => ['Small', 'Large'],
            'variant_prices' => [8.00, 12.00]
        ]);

        $response->assertRedirect();
        
        $item = MenuItem::whereName('Burger')->first();
        $this->assertNotNull($item->variants);
        $this->assertEquals('Size', $item->variants['type']);
        $this->assertCount(2, $item->variants['options']);
        $this->assertEquals('Small', $item->variants['options'][0]['label']);
        $this->assertEquals(8.00, $item->variants['options'][0]['price']);
    }

    public function test_owner_can_update_menu_item_and_variants()
    {
        $item = MenuItem::factory()->create([
            'menu_category_id' => $this->category->id,
            'name' => 'Old Name',
            'variants' => [
                'type' => 'Size',
                'options' => [['label' => 'S', 'price' => 5]]
            ]
        ]);

        $response = $this->actingAs($this->user)->put(route('owner.menu-item.update', $item), [
            'name' => 'Pizza',
            'description' => 'New Description',
            'price' => 15.00,
            'has_variants' => 1,
            'variant_type' => 'Topping',
            'variant_names' => ['Cheese', 'Pepperoni'],
            'variant_prices' => [2.00, 3.00]
        ]);

        $response->assertRedirect();
        
        $item->refresh();
        $this->assertEquals('Pizza', $item->name);
        $this->assertEquals('Topping', $item->variants['type']);
        $this->assertCount(2, $item->variants['options']);
        $this->assertEquals('Cheese', $item->variants['options'][0]['label']);
    }

    public function test_owner_can_remove_variants_during_update()
    {
        $item = MenuItem::factory()->create([
            'menu_category_id' => $this->category->id,
            'variants' => [
                'type' => 'Size',
                'options' => [['label' => 'S', 'price' => 5]]
            ]
        ]);

        $response = $this->actingAs($this->user)->put(route('owner.menu-item.update', $item), [
            'name' => 'Pizza',
            'price' => 15.00,
            'has_variants' => 0 // Disable variants
        ]);

        $response->assertRedirect();
        
        $item->refresh();
        $this->assertNull($item->variants);
    }

    public function test_owner_can_toggle_availability()
    {
        $item = MenuItem::factory()->create([
            'menu_category_id' => $this->category->id,
            'is_available' => true
        ]);

        $response = $this->actingAs($this->user)->post(route('owner.menu-item.toggle', $item));
        
        $response->assertRedirect();
        $this->assertFalse($item->fresh()->is_available);
    }

    public function test_owner_can_delete_menu_item()
    {
        $item = MenuItem::factory()->create(['menu_category_id' => $this->category->id]);

        $response = $this->actingAs($this->user)->delete(route('owner.menu-item.destroy', $item));
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }
}
