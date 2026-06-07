<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantIndexSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_index_filters_results_using_query_search(): void
    {
        $matchingRestaurant = Restaurant::factory()->create([
            'name' => 'Burger House',
        ]);

        Restaurant::factory()->create([
            'name' => 'Pizza Palace',
        ]);

        $response = $this->get(route('restaurants.index', ['q' => 'Burger']));

        $response->assertOk();
        $response->assertSee('Burger House');
        $response->assertDontSee('Pizza Palace');
        $response->assertSee('Search');
        $response->assertSee((string) $matchingRestaurant->name);
    }
}
