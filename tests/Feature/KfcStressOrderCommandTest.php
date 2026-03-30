<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KfcStressOrderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_stress_command_creates_the_requested_number_of_kfc_orders(): void
    {
        $this->artisan('stress:kfc-orders', [
            '--orders' => 5,
            '--concurrency' => 2,
            '--quantity' => 2,
            '--driver' => 'sync',
        ])
            ->expectsOutputToContain('Running 5 orders against KFC for owner hsendeeb2@gmail.com using the sync driver...')
            ->expectsOutputToContain('KFC stress run completed without recorded failures.')
            ->assertExitCode(0);

        $owner = User::where('email', 'hsendeeb2@gmail.com')->first();
        $restaurant = Restaurant::where('name', 'KFC')->first();

        $this->assertNotNull($owner);
        $this->assertSame('owner', $owner->role);
        $this->assertNotNull($restaurant);
        $this->assertSame($owner->id, $restaurant->user_id);
        $this->assertSame(5, Order::where('restaurant_id', $restaurant->id)->count());
        $this->assertSame(5, Order::where('restaurant_id', $restaurant->id)->where('status', 'pending')->count());
    }
}
