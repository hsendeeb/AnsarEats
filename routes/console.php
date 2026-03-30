<?php

use App\Actions\PlaceOrderFromCart;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Str;

const KFC_STRESS_OWNER_NAME = 'hsendeeb';
const KFC_STRESS_OWNER_EMAIL = 'hsendeeb2@gmail.com';

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('stress:kfc-orders
    {--orders=100 : Total number of orders to create}
    {--concurrency=10 : Number of simultaneous workers to run per batch}
    {--quantity=2 : Quantity per order}
    {--driver=process : Concurrency driver to use (process or sync)}', function () {
    $totalOrders = max((int) $this->option('orders'), 1);
    $concurrency = max((int) $this->option('concurrency'), 1);
    $quantity = max((int) $this->option('quantity'), 1);
    $driver = in_array((string) $this->option('driver'), ['process', 'sync'], true)
        ? (string) $this->option('driver')
        : 'process';

    $owner = User::query()->where('email', KFC_STRESS_OWNER_EMAIL)->first();

    if (! $owner) {
        $owner = User::create([
            'name' => KFC_STRESS_OWNER_NAME,
            'email' => KFC_STRESS_OWNER_EMAIL,
            'password' => bcrypt('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);
    } else {
        $ownerUpdates = [];

        if ($owner->name !== KFC_STRESS_OWNER_NAME) {
            $ownerUpdates['name'] = KFC_STRESS_OWNER_NAME;
        }

        if ($owner->role !== 'owner') {
            $ownerUpdates['role'] = 'owner';
        }

        if (! $owner->email_verified_at) {
            $ownerUpdates['email_verified_at'] = now();
        }

        if ($ownerUpdates !== []) {
            $owner->update($ownerUpdates);
        }
    }

    $restaurant = Restaurant::query()->firstOrCreate(
        ['user_id' => $owner->id, 'name' => 'KFC'],
        [
            'description' => 'Stress-test restaurant fixture',
            'address' => 'Beirut',
            'phone' => '70000000',
            'delivery_fee' => 2.50,
            'is_open' => true,
        ]
    );

    $restaurant->update([
        'is_open' => true,
        'delivery_fee' => 2.50,
    ]);

    $category = MenuCategory::query()->firstOrCreate(
        ['restaurant_id' => $restaurant->id, 'name' => 'Load Test Meals'],
        ['sort_order' => 0]
    );

    $menuItem = MenuItem::query()->firstOrCreate(
        ['menu_category_id' => $category->id, 'name' => 'Stress Test Box'],
        [
            'description' => 'Reusable menu item for load testing',
            'price' => 14.00,
            'is_available' => true,
        ]
    );

    $menuItem->update([
        'price' => $menuItem->effectivePrice() ?: 14.00,
        'is_available' => true,
    ]);

    $restaurantId = $restaurant->id;
    $menuItemId = $menuItem->id;
    $initialOrderCount = Order::where('restaurant_id', $restaurantId)->count();

    $this->info("Running {$totalOrders} orders against KFC for owner ".KFC_STRESS_OWNER_EMAIL." using the {$driver} driver...");
    $this->line("Restaurant #{$restaurant->id}, Menu Item #{$menuItem->id}, batch concurrency {$concurrency}, quantity {$quantity}.");

    $progress = $this->output->createProgressBar($totalOrders);
    $progress->start();

    $startedAt = microtime(true);
    $results = [];

    foreach (array_chunk(range(1, $totalOrders), $concurrency) as $batch) {
        $tasks = [];

        foreach ($batch as $orderNumber) {
            $tasks[$orderNumber] = function () use ($orderNumber, $restaurantId, $menuItemId, $quantity) {
                try {
                    $freshRestaurant = Restaurant::findOrFail($restaurantId);
                    $freshMenuItem = MenuItem::findOrFail($menuItemId);

                    $customer = User::create([
                        'name' => "Stress Customer {$orderNumber}",
                        'email' => "stress-kfc-{$orderNumber}-".Str::lower(Str::random(8)).'@example.com',
                        'password' => bcrypt('password'),
                        'email_verified_at' => now(),
                    ]);

                    $unitPrice = $freshMenuItem->effectivePrice();
                    $deliveryFee = (float) ($freshRestaurant->delivery_fee ?? 0);
                    $itemKey = $freshMenuItem->id.'||'.number_format($unitPrice, 2, '.', '');

                    $cartData = [
                        'restaurant_id' => $freshRestaurant->id,
                        'restaurant_name' => $freshRestaurant->name,
                        'items' => [
                            [
                                'key' => $itemKey,
                                'id' => $freshMenuItem->id,
                                'name' => $freshMenuItem->name,
                                'price' => $unitPrice,
                                'image' => $freshMenuItem->image,
                                'quantity' => $quantity,
                                'variant' => null,
                            ],
                        ],
                        'promo' => null,
                        'discount' => 0,
                        'delivery_fee' => $deliveryFee,
                        'total' => round(($unitPrice * $quantity) + $deliveryFee, 2),
                    ];

                    $order = app(PlaceOrderFromCart::class)->handle($customer, $cartData, [
                        'delivery_address' => "Stress Lane {$orderNumber}, Beirut",
                        'phone' => '70000000',
                        'notes' => "Concurrent stress order {$orderNumber}",
                    ]);

                    return [
                        'success' => true,
                        'order_number' => $orderNumber,
                        'order_id' => $order->id,
                        'customer_id' => $customer->id,
                        'total' => (float) $order->total,
                    ];
                } catch (\Throwable $e) {
                    return [
                        'success' => false,
                        'order_number' => $orderNumber,
                        'message' => $e->getMessage(),
                    ];
                }
            };
        }

        foreach (Concurrency::driver($driver)->run($tasks) as $result) {
            $results[] = $result;
            $progress->advance();
        }
    }

    $progress->finish();
    $this->newLine(2);

    $successful = collect($results)->where('success', true)->values();
    $failed = collect($results)->where('success', false)->values();
    $persistedOrders = Order::where('restaurant_id', $restaurantId)->count();
    $newPersistedOrders = $persistedOrders - $initialOrderCount;
    $duration = round(microtime(true) - $startedAt, 2);

    $this->table(
        ['Metric', 'Value'],
        [
            ['Requested Orders', $totalOrders],
            ['Successful Orders', $successful->count()],
            ['Failed Orders', $failed->count()],
            ['New Persisted Orders For KFC', $newPersistedOrders],
            ['Total Persisted Orders For KFC', $persistedOrders],
            ['Duration (seconds)', $duration],
            ['Average Orders / Second', $duration > 0 ? round($successful->count() / $duration, 2) : 'n/a'],
        ]
    );

    if ($failed->isNotEmpty()) {
        $this->error('Some orders failed during the stress run.');
        $this->table(
            ['Order #', 'Error'],
            $failed->take(10)->map(fn (array $failure) => [
                $failure['order_number'],
                Str::limit($failure['message'], 140),
            ])->all()
        );

        return self::FAILURE;
    }

    if ($newPersistedOrders < $successful->count()) {
        $this->error('Order creation reported success, but this run persisted fewer new KFC orders than expected.');

        return self::FAILURE;
    }

    $this->info('KFC stress run completed without recorded failures.');

    return self::SUCCESS;
})->purpose('Create a concurrent stress run of customer orders against KFC owned by hsendeeb2@gmail.com');
