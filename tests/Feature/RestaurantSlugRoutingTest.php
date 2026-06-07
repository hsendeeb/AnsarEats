<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantSlugRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_routes_generate_slugs_instead_of_ids(): void
    {
        $restaurant = Restaurant::factory()->create([
            'name' => 'Best Burgers & More',
        ]);

        $this->assertSame('best-burgers-more', $restaurant->slug);
        $this->assertSame('/restaurants/best-burgers-more', route('restaurant.show', $restaurant, false));
    }

    public function test_canonical_restaurant_slug_route_loads_successfully(): void
    {
        $restaurant = Restaurant::factory()->create([
            'name' => 'KFC Downtown',
        ]);

        $response = $this->get(route('restaurant.show', $restaurant));

        $response->assertOk();
        $response->assertSee('KFC Downtown');
    }

    public function test_legacy_restaurant_id_url_redirects_to_canonical_slug_url(): void
    {
        $restaurant = Restaurant::factory()->create([
            'name' => 'Pizza Palace',
        ]);

        $response = $this->get('/restaurant/'.$restaurant->id);

        $response->assertStatus(301);
        $response->assertRedirect(route('restaurant.show', $restaurant));
    }
}
