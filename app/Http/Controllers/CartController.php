<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get cart contents (JSON for Alpine.js)
     */
    public function index()
    {
        $cart = session('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]);
        $cart['total'] = collect($cart['items'])->sum(fn($item) => $item['price'] * $item['quantity']);
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
        ]);

        $menuItem = MenuItem::with('menuCategory.restaurant')->findOrFail($request->menu_item_id);

        if (!$menuItem->is_available) {
            return response()->json(['message' => 'This item is currently unavailable.'], 422);
        }

        $restaurant = $menuItem->menuCategory->restaurant;
        
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

        $itemId = (string) $menuItem->id;

        if (isset($cart['items'][$itemId])) {
            $cart['items'][$itemId]['quantity'] += $quantity;
        } else {
            $cart['items'][$itemId] = [
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'price' => (float) $menuItem->price,
                'image' => $menuItem->image,
                'quantity' => $quantity,
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
            'menu_item_id' => 'required',
            'quantity' => 'required|integer|min:0|max:20',
        ]);

        $cart = session('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]);
        $itemId = (string) $request->menu_item_id;

        if ($request->quantity === 0) {
            unset($cart['items'][$itemId]);
        } elseif (isset($cart['items'][$itemId])) {
            $cart['items'][$itemId]['quantity'] = $request->quantity;
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
        $cart = session('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]);
        $itemId = (string) $request->menu_item_id;

        unset($cart['items'][$itemId]);

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

        return response()->json(['cart' => ['restaurant_id' => null, 'restaurant_name' => null, 'items' => [], 'total' => 0, 'count' => 0]]);
    }

    /**
     * Show checkout page (auth required)
     */
    public function checkout()
    {
        $cart = session('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]);

        if (empty($cart['items'])) {
            return redirect()->route('home')->with('error', 'Your cart is empty!');
        }

        $cart['total'] = collect($cart['items'])->sum(fn($item) => $item['price'] * $item['quantity']);
        $cart['count'] = collect($cart['items'])->sum('quantity');

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

        $total = collect($cart['items'])->sum(fn($item) => $item['price'] * $item['quantity']);

        $order = Order::create([
            'user_id' => Auth::id(),
            'restaurant_id' => $cart['restaurant_id'],
            'delivery_address' => $request->delivery_address,
            'phone' => $request->phone,
            'notes' => $request->notes,
            'total' => $total,
            'status' => 'pending',
        ]);

        foreach ($cart['items'] as $item) {
            $order->orderItems()->create([
                'menu_item_id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ]);
        }

        session()->forget('cart');

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
}
