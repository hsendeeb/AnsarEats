<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private function cartItemKey(int $menuItemId, ?string $variantLabel, float $price): string
    {
        $label = trim((string) ($variantLabel ?? ''));
        return $menuItemId . '|' . mb_strtolower($label) . '|' . number_format($price, 2, '.', '');
    }

    private function canonicalPriceForItem(MenuItem $menuItem, ?string $variantLabel): ?float
    {
        return $variantLabel
            ? $menuItem->variantPrice($variantLabel)
            : $menuItem->effectivePrice();
    }

    private function syncCart(array $cart): array
    {
        $items = $cart['items'] ?? [];

        if (empty($items)) {
            return array_merge([
                'restaurant_id' => null,
                'restaurant_name' => null,
                'items' => [],
                'promo' => null,
            ], $cart);
        }

        $menuItems = MenuItem::query()
            ->whereIn('id', collect($items)->pluck('id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $syncedItems = [];

        foreach ($items as $item) {
            $menuItem = $menuItems->get($item['id'] ?? null);

            if (!$menuItem) {
                continue;
            }

            $variantLabel = $item['variant'] ?? null;
            $price = $this->canonicalPriceForItem($menuItem, $variantLabel);

            if ($price === null) {
                continue;
            }

            $newKey = $this->cartItemKey($menuItem->id, $variantLabel, $price);

            if (isset($syncedItems[$newKey])) {
                $syncedItems[$newKey]['quantity'] += (int) ($item['quantity'] ?? 0);
                continue;
            }

            $syncedItems[$newKey] = [
                'key' => $newKey,
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'price' => $price,
                'image' => $menuItem->image,
                'quantity' => (int) ($item['quantity'] ?? 0),
                'variant' => $variantLabel,
            ];
        }

        $cart['items'] = $syncedItems;

        if (empty($cart['items'])) {
            $cart['restaurant_id'] = null;
            $cart['restaurant_name'] = null;
            $cart['promo'] = null;
        }

        return $cart;
    }

    private function resolveCartItemKey(array $cart, string $requestedKey): ?string
    {
        if (isset($cart['items'][$requestedKey])) {
            return $requestedKey;
        }

        [$requestedId, $requestedLabel] = array_pad(explode('|', $requestedKey, 3), 3, '');
        $requestedId = (int) $requestedId;
        $requestedLabel = mb_strtolower(trim($requestedLabel));

        foreach ($cart['items'] as $key => $item) {
            $itemLabel = mb_strtolower(trim((string) ($item['variant'] ?? '')));

            if ((int) ($item['id'] ?? 0) === $requestedId && $itemLabel === $requestedLabel) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get cart contents (JSON for Alpine.js)
     */
    public function index()
    {
        $cart = $this->syncCart(
            session('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => [], 'promo' => null])
        );
        session(['cart' => $cart]);
        
        $subtotal = collect($cart['items'])->sum(fn($item) => $item['price'] * $item['quantity']);
        $discountAmount = 0;

        if (!empty($cart['promo'])) {
            $discountAmount = ($subtotal * $cart['promo']['discount_percentage']) / 100;
        }

        $cart['subtotal'] = $subtotal;
        $cart['discount'] = $discountAmount;
        $cart['total'] = $subtotal - $discountAmount;
        $cart['count'] = collect($cart['items'])->sum('quantity');

        return response()->json($cart);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'integer|min:1|max:20',
            'variant_label' => 'nullable|string|max:255',
            'variant_price' => 'nullable|numeric|min:0',
        ]);

        $menuItem = MenuItem::with('menuCategory.restaurant')->findOrFail($request->menu_item_id);

        if (!$menuItem->is_available) {
            return response()->json(['message' => 'This item is currently unavailable.'], 422);
        }

        $restaurant = $menuItem->menuCategory->restaurant;
        
        if (!$restaurant->isOpenNow()) {
            return response()->json(['message' => 'This restaurant is currently closed.'], 422);
        }

        // Prevent owner from ordering from their own restaurant
        if ($restaurant->user_id === Auth::id()) {
            return response()->json(['message' => 'You cannot order from your own restaurant.'], 403);
        }

        $cart = session('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]);
        $quantity = $request->input('quantity', 1);

        // If adding from a different restaurant, clear the cart
        if ($cart['restaurant_id'] && $cart['restaurant_id'] !== $restaurant->id) {
            $cart = ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []];
        }

        $cart['restaurant_id'] = $restaurant->id;
        $cart['restaurant_name'] = $restaurant->name;

        $variantLabel = $request->input('variant_label');
        $price = $this->canonicalPriceForItem($menuItem, $variantLabel);

        if ($variantLabel && $price === null) {
            return response()->json(['message' => 'The selected item option is no longer available.'], 422);
        }

        $itemKey = $this->cartItemKey($menuItem->id, $variantLabel, $price);

        if (isset($cart['items'][$itemKey])) {
            $cart['items'][$itemKey]['quantity'] += $quantity;
        } else {
            $cart['items'][$itemKey] = [
                'key' => $itemKey,
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'price' => $price,
                'image' => $menuItem->image,
                'quantity' => $quantity,
                'variant' => $variantLabel,
            ];
        }

        session(['cart' => $cart]);

        $cart['total'] = collect($cart['items'])->sum(fn($item) => $item['price'] * $item['quantity']);
        $cart['count'] = collect($cart['items'])->sum('quantity');

        return response()->json([
            'message' => $menuItem->name . ' added to cart!',
            'cart' => $cart,
        ]);
    }

    /**
     * Update item quantity
     */
    public function update(Request $request)
    {
        $request->validate([
            'item_key' => 'required|string',
            'quantity' => 'required|integer|min:0|max:20',
        ]);

        $cart = $this->syncCart(session('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]));
        $itemKey = $this->resolveCartItemKey($cart, (string) $request->item_key);

        if ($itemKey === null) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }

        if ($request->quantity === 0) {
            unset($cart['items'][$itemKey]);
        } elseif (isset($cart['items'][$itemKey])) {
            $cart['items'][$itemKey]['quantity'] = $request->quantity;
        }

        // If cart is empty, reset restaurant info
        if (empty($cart['items'])) {
            $cart['restaurant_id'] = null;
            $cart['restaurant_name'] = null;
        }

        session(['cart' => $cart]);

        $cart['total'] = collect($cart['items'])->sum(fn($item) => $item['price'] * $item['quantity']);
        $cart['count'] = collect($cart['items'])->sum('quantity');

        return response()->json(['cart' => $cart]);
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request)
    {
        $request->validate([
            'item_key' => 'required|string',
        ]);

        $cart = $this->syncCart(session('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]));
        $itemKey = $this->resolveCartItemKey($cart, (string) $request->item_key);

        if ($itemKey === null) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }

        unset($cart['items'][$itemKey]);

        if (empty($cart['items'])) {
            $cart['restaurant_id'] = null;
            $cart['restaurant_name'] = null;
        }

        session(['cart' => $cart]);

        $cart['total'] = collect($cart['items'])->sum(fn($item) => $item['price'] * $item['quantity']);
        $cart['count'] = collect($cart['items'])->sum('quantity');

        return response()->json(['cart' => $cart]);
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        session()->forget('cart');

        return response()->json(['cart' => ['restaurant_id' => null, 'restaurant_name' => null, 'items' => [], 'total' => 0, 'subtotal' => 0, 'discount' => 0, 'count' => 0, 'promo' => null]]);
    }

    public function applyPromo(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        
        $cart = $this->syncCart(session('cart', ['restaurant_id' => null, 'items' => []]));
        
        if (!$cart['restaurant_id']) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        $promotion = Promotion::where('restaurant_id', $cart['restaurant_id'])
            ->where('code', $request->code)
            ->where(function($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            })
            ->first();

        if (!$promotion) {
            return response()->json(['message' => 'Invalid or expired promo code.'], 422);
        }

        $cart['promo'] = [
            'id' => $promotion->id,
            'code' => $promotion->code,
            'discount_percentage' => $promotion->discount_percentage,
        ];

        session(['cart' => $cart]);

        return $this->index();
    }

    /**
     * Show checkout page (auth required)
     */
    public function checkout()
    {
        $response = $this->index();
        $cart = $response->getData(true);

        if (empty($cart['items'])) {
            return redirect()->route('home')->with('error', 'Your cart is empty!');
        }

        return view('checkout', compact('cart'));
    }

    /**
     * Place the order (auth required)
     */
    public function placeOrder(Request $request)
    {
        $request->validate([
            'delivery_address' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $cart = session('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]);

        if (empty($cart['items'])) {
            return redirect()->route('home')->with('error', 'Your cart is empty!');
        }

        // Prevent owner from ordering from their own restaurant
        $restaurant = \App\Models\Restaurant::find($cart['restaurant_id']);
        if ($restaurant && $restaurant->user_id === Auth::id()) {
            return redirect()->route('home')->with('error', 'You cannot order from your own restaurant.');
        }

        $response = $this->index();
        $cartData = $response->getData(true);

        $order = Order::create([
            'user_id' => Auth::id(),
            'restaurant_id' => $cartData['restaurant_id'],
            'delivery_address' => $request->delivery_address,
            'phone' => $request->phone,
            'notes' => $request->notes,
            'total' => $cartData['total'],
            'discount_amount' => $cartData['discount'],
            'promotion_id' => $cartData['promo']['id'] ?? null,
            'status' => 'pending',
        ]);

        foreach ($cartData['items'] as $item) {
            $displayName = $item['name'];
            if (!empty($item['variant'])) {
                $displayName .= ' (' . $item['variant'] . ')';
            }

            $order->orderItems()->create([
                'menu_item_id' => $item['id'],
                'name' => $item['name'],
                'variant_label' => $item['variant'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ]);
        }

        session()->forget('cart');

        // Send Order Confirmation Email
        try {
            \Illuminate\Support\Facades\Mail::to($order->user->email)->queue(new \App\Mail\OrderPlacedMail($order));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send order placement email: ' . $e->getMessage());
        }

        return redirect()->route('order.confirmation', $order)->with('success', 'Order placed successfully! 🎉');
    }

    /**
     * Order confirmation page
     */
    public function confirmation(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('orderItems', 'restaurant');

        return view('order-confirmation', compact('order'));
    }

    /**
     * Poll order status (lightweight JSON endpoint for real-time UI)
     */
    public function pollStatus(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        return response()->json([
            'id'     => $order->id,
            'status' => $order->status,
            'estimated_prep_time' => $order->estimated_prep_time,
        ]);
    }

    /**
     * Poll multiple order statuses at once (for profile orders history page)
     */
    public function batchStatus(Request $request)
    {
        $ids = collect(explode(',', $request->input('ids', '')))
            ->map(fn($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->take(20); // safety cap

        $orders = Order::where('user_id', Auth::id())
            ->whereIn('id', $ids)
            ->get(['id', 'status', 'estimated_prep_time']);

        return response()->json(
            $orders->mapWithKeys(fn($o) => [
                $o->id => [
                    'status'              => $o->status,
                    'estimated_prep_time' => $o->estimated_prep_time,
                ]
            ])
        );
    }
}
