<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $restaurant = $user->restaurant;

        $stats = [
            'total_orders' => 0,
            'total_revenue' => 0,
            'pending_orders' => 0,
            'completed_orders' => 0,
            'chart_data' => [
                'bar' => ['labels' => [], 'data' => []],
                'pie' => ['labels' => [], 'data' => []]
            ]
        ];

        if ($restaurant) {
            $restaurant->load('menuCategories.menuItems');

            $orders = \App\Models\Order::where('restaurant_id', $restaurant->id)->get();
            
            $stats['total_orders'] = $orders->count();
            $stats['total_revenue'] = $orders->where('status', '!=', 'cancelled')->sum('total');
            $stats['pending_orders'] = $orders->where('status', 'pending')->count();
            $stats['completed_orders'] = $orders->where('status', 'delivered')->count();
            $stats['avg_order_value'] = $stats['total_orders'] > 0 ? $stats['total_revenue'] / $stats['total_orders'] : 0;

            // Bar Chart: Orders over last 7 days
            $last7Days = collect(range(0, 6))->map(function($i) {
                return now()->subDays($i)->format('Y-m-d');
            })->reverse();

            $dailyOrders = \App\Models\Order::where('restaurant_id', $restaurant->id)
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->pluck('count', 'date');

            $stats['chart_data']['bar']['labels'] = $last7Days->map(fn($date) => Carbon::parse($date)->format('M d'))->values()->toArray();
            $stats['chart_data']['bar']['data'] = $last7Days->map(fn($date) => $dailyOrders[$date] ?? 0)->values()->toArray();

            // Pie Chart: Orders by Status
            $statusCounts = $orders->groupBy('status')->map->count();
            $stats['chart_data']['pie']['labels'] = $statusCounts->keys()->map(fn($s) => ucfirst($s))->toArray();
            $stats['chart_data']['pie']['data'] = $statusCounts->values()->toArray();

            // Top Selling Items
            $stats['top_items'] = \App\Models\OrderItem::whereHas('order', function($query) use ($restaurant) {
                    $query->where('restaurant_id', $restaurant->id);
                })
                ->select('name', DB::raw('SUM(quantity) as total_quantity'))
                ->groupBy('name')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get();
        }

        return view('owner.dashboard', compact('restaurant', 'stats'));
    }

    public function storeOrUpdate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'is_open' => 'sometimes|boolean',
            'logo' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:4096'
        ]);

        $data['is_open'] = $request->has('is_open');

        $user = Auth::user();
        $restaurant = $user->restaurant;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('restaurants', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('restaurants', 'public');
        }

        if ($restaurant) {
            $restaurant->update($data);
        } else {
            $user->restaurant()->create($data);
            $user->update(['role' => 'owner']);
        }

        return back()->with('success', 'Restaurant saved successfully!');
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $restaurant = Auth::user()->restaurant;

        if (!$restaurant) {
            return back()->with('error', 'You need a restaurant first!');
        }

        $restaurant->menuCategories()->create([
            'name' => $request->name,
            'sort_order' => $restaurant->menuCategories()->count()
        ]);

        return back()->with('success', 'Category added!');
    }

    public function updateCategory(Request $request, MenuCategory $category)
    {
        if ($category->restaurant->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['name' => 'required|string|max:255']);
        $category->update($request->only('name'));

        return back()->with('success', 'Category updated!');
    }

    public function destroyCategory(MenuCategory $category)
    {
        if ($category->restaurant->user_id !== Auth::id()) {
            abort(403);
        }

        $category->delete();
        return back()->with('success', 'Category deleted!');
    }
}
