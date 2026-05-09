<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\User;
use App\Models\UserLocation;
use App\Support\PerformanceCache;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

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

        $request->merge([
            'phone' => preg_replace('/\s+/', '', (string) $request->input('phone')),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'phone')->ignore($user->id),
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    $exists = User::query()
                        ->where('id', '!=', $user->id)
                        ->whereNotNull('phone')
                        ->whereRaw("REPLACE(phone, ' ', '') = ?", [$value])
                        ->exists();

                    if ($exists) {
                        $fail('This phone number is already used by another customer.');
                    }
                },
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'phone.unique' => 'This phone number is already used by another customer.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->input('phone');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    // ── Location Management ─────────────────────────────────────────

    public function locations()
    {
        $locations = Auth::user()->locations()->latest()->get();

        return view('profile.locations', [
            'locations' => $locations,
        ]);
    }

    public function storeLocation(Request $request)
    {
        $request->validate([
            'alias' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'boolean',
        ]);

        $user = Auth::user();

        // If setting as default, unset other defaults
        if ($request->boolean('is_default')) {
            $user->locations()->update(['is_default' => false]);
        }

        // If this is the first location, make it default
        $isFirst = $user->locations()->count() === 0;

        $location = $user->locations()->create([
            'alias' => $request->alias,
            'address' => $request->address,
            'latitude' => $request->filled('latitude') ? $request->latitude : null,
            'longitude' => $request->filled('longitude') ? $request->longitude : null,
            'is_default' => $request->boolean('is_default') || $isFirst,
        ]);

        // Sync default location to user's delivery fields
        $this->syncDefaultLocationToUser($user);

        return back()->with('success', 'Location "' . $location->alias . '" saved!');
    }

    public function updateLocation(Request $request, UserLocation $location)
    {
        if ($location->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'alias' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'boolean',
        ]);

        $user = Auth::user();

        if ($request->boolean('is_default')) {
            $user->locations()->where('id', '!=', $location->id)->update(['is_default' => false]);
        }

        $location->update([
            'alias' => $request->alias,
            'address' => $request->address,
            'latitude' => $request->filled('latitude') ? $request->latitude : null,
            'longitude' => $request->filled('longitude') ? $request->longitude : null,
            'is_default' => $request->boolean('is_default'),
        ]);

        $this->syncDefaultLocationToUser($user);

        return back()->with('success', 'Location "' . $location->alias . '" updated!');
    }

    public function destroyLocation(UserLocation $location)
    {
        if ($location->user_id != Auth::id()) {
            abort(403);
        }

        $wasDefault = $location->is_default;
        $alias = $location->alias;
        $location->delete();

        $user = Auth::user();

        // If we deleted the default, promote the most recent one
        if ($wasDefault) {
            $nextDefault = $user->locations()->latest()->first();
            if ($nextDefault) {
                $nextDefault->update(['is_default' => true]);
            }
        }

        $this->syncDefaultLocationToUser($user);

        return back()->with('success', 'Location "' . $alias . '" removed.');
    }

    public function setDefaultLocation(UserLocation $location)
    {
        if ($location->user_id != Auth::id()) {
            abort(403);
        }

        $user = Auth::user();
        $user->locations()->update(['is_default' => false]);
        $location->update(['is_default' => true]);

        $this->syncDefaultLocationToUser($user);

        return back()->with('success', '"' . $location->alias . '" is now your default location.');
    }

    /**
     * Sync the default location to the user's delivery_address / lat / lng fields
     * so checkout can auto-fill from the user record.
     */
    private function syncDefaultLocationToUser(User $user): void
    {
        $default = $user->locations()->where('is_default', true)->first();

        $user->update([
            'delivery_address' => $default?->address,
            'delivery_latitude' => $default?->latitude,
            'delivery_longitude' => $default?->longitude,
        ]);
    }

    // ── Orders ──────────────────────────────────────────────────────

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

