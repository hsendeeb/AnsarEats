<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Support\PerformanceCache;
use Carbon\Carbon;

class ProfileController extends Controller
{
    private function deliveryDefaults($user): array
    {
        $latestOrder = $user->orders()
            ->latest()
            ->first(['delivery_address', 'phone']);

        return [
            'phone' => $user->phone ?: $latestOrder?->phone,
            'delivery_address' => $user->delivery_address ?: $latestOrder?->delivery_address,
            'delivery_latitude' => $user->delivery_latitude,
            'delivery_longitude' => $user->delivery_longitude,
        ];
    }

    public function account()
    {
        return view('profile.account', [
            'user' => Auth::user(),
        ]);
    }

    public function show()
    {
        return view('profile.show', [
            'user' => Auth::user(),
            'deliveryDefaults' => $this->deliveryDefaults(Auth::user()),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'delivery_address' => 'nullable|string|max:255',
            'delivery_latitude' => 'nullable|numeric',
            'delivery_longitude' => 'nullable|numeric',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->input('phone');
        $user->delivery_address = $request->input('delivery_address');
        $user->delivery_latitude = $request->filled('delivery_latitude')
            ? $request->input('delivery_latitude')
            : null;
        $user->delivery_longitude = $request->filled('delivery_longitude')
            ? $request->input('delivery_longitude')
            : null;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    public function orders(Request $request)
    {
        $user = Auth::user();
        $activeFilter = $request->filter ?? 'all';

        $orders = PerformanceCache::remember(
            'profile-orders',
            json_encode(['user_id' => $user->id, 'filter' => $activeFilter]),
            now()->addSeconds(config('performance.cache_ttl.profile_orders')),
            function () use ($user, $request) {
                $query = Order::where('user_id', $user->id)->with('restaurant', 'orderItems.menuItem');

                if ($request->filled('filter')) {
                    switch ($request->filter) {
                        case 'today':
                            $query->whereDate('created_at', Carbon::today());
                            break;
                        case 'week':
                            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                            break;
                        case 'month':
                            $query->whereMonth('created_at', Carbon::now()->month)
                                ->whereYear('created_at', Carbon::now()->year);
                            break;
                    }
                }

                return $query->latest()->get();
            }
        );

        return view('profile.orders', [
            'orders' => $orders,
            'activeFilter' => $activeFilter
        ]);
    }

    public function clearHistory()
    {
        $user = Auth::user();
        Order::where('user_id', $user->id)->delete();

        return back()->with('success', 'Order history cleared!');
    }
}
