<?php


namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function buildOrdersBarChartData(int $restaurantId, string $period): array
    {
        $period = in_array($period, ['day', 'week', 'month'], true) ? $period : 'week';

        if ($period === 'day') {
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
            $orders = Order::where('restaurant_id', $restaurantId)
                ->whereBetween('created_at', [$start, $end])
                ->get(['created_at']);

            $countsByHour = $orders
                ->groupBy(fn($o) => Carbon::parse($o->created_at)->format('H'))
                ->map(fn($g) => $g->count());

            $labels = collect(range(0, 23))
                ->map(fn($h) => Carbon::today()->copy()->setHour($h)->format('g A'))
                ->values()
                ->toArray();

            $data = collect(range(0, 23))
                ->map(function($h) use ($countsByHour) {
                    $key = str_pad((string) $h, 2, '0', STR_PAD_LEFT);
                    return $countsByHour[$key] ?? 0;
                })
                ->values()
                ->toArray();

            return [
                'title' => 'Today Orders',
                'labels' => $labels,
                'data' => $data,
            ];
        }

        if ($period === 'month') {
            $days = 30;
            $start = now()->subDays($days - 1)->startOfDay();
            $end = now()->endOfDay();
            $orders = Order::where('restaurant_id', $restaurantId)
                ->whereBetween('created_at', [$start, $end])
                ->get(['created_at']);

            $countsByDate = $orders
                ->groupBy(fn($o) => Carbon::parse($o->created_at)->format('Y-m-d'))
                ->map(fn($g) => $g->count());

            $dates = collect(range(0, $days - 1))
                ->map(fn($i) => now()->subDays($days - 1 - $i)->format('Y-m-d'));

            $labels = $dates->map(fn($d) => Carbon::parse($d)->format('M d'))->values()->toArray();
            $data = $dates->map(fn($d) => $countsByDate[$d] ?? 0)->values()->toArray();

            return [
                'title' => 'Last 30 Days Orders',
                'labels' => $labels,
                'data' => $data,
            ];
        }

        // week (default): last 7 days
        $days = 7;
        $start = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at']);

        $countsByDate = $orders
            ->groupBy(fn($o) => Carbon::parse($o->created_at)->format('Y-m-d'))
            ->map(fn($g) => $g->count());

        $dates = collect(range(0, $days - 1))
            ->map(fn($i) => now()->subDays($days - 1 - $i)->format('Y-m-d'));

        return [
            'title' => 'Weekly Orders',
            'labels' => $dates->map(fn($d) => Carbon::parse($d)->format('M d'))->values()->toArray(),
            'data' => $dates->map(fn($d) => $countsByDate[$d] ?? 0)->values()->toArray(),
        ];
    }

    public function index(Request $request)

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

            $orders = Order::where('restaurant_id', $restaurant->id)->get();
            
            $stats['total_orders'] = $orders->count();
            $stats['total_revenue'] = $orders->where('status', '!=', 'cancelled')->sum('total');
            $stats['pending_orders'] = $orders->where('status', 'pending')->count();
            $stats['completed_orders'] = $orders->whereIn('status', ['delivered'])->count();
            $stats['avg_order_value'] = $stats['total_orders'] > 0 ? $stats['total_revenue'] / $stats['total_orders'] : 0;

            // Bar Chart default: weekly (last 7 days)
            $bar = $this->buildOrdersBarChartData($restaurant->id, 'week');
            $stats['chart_data']['bar']['labels'] = $bar['labels'];
            $stats['chart_data']['bar']['data'] = $bar['data'];
            $stats['chart_data']['bar']['title'] = $bar['title'];

            // Pie Chart: Orders by Status
            $statusCounts = $orders->groupBy('status')->map(fn($group) => $group->count());
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

            // Fetch Orders with Filters
            $ordersQuery = Order::with(['user', 'orderItems'])
                ->where('restaurant_id', $restaurant->id);

            if ($request->has('filter')) {
                if ($request->filter === 'day') {
                    $ordersQuery->whereDate('created_at', Carbon::today());
                } elseif ($request->filter === 'week') {
                    $ordersQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                }
            }

            if ($request->has('status')) {
                if (in_array($request->status, ['pending', 'accepted', 'delivered', 'cancelled'])) {
                    $ordersQuery->where('status', $request->status);
                }
            }

            if ($request->has('sort') && $request->sort === 'total') {
                $ordersQuery->orderByDesc('total');
            } else {
                $ordersQuery->orderByDesc('created_at');
            }

            $orders = $ordersQuery->paginate(10)->appends($request->query());
        } else {
            $orders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        }

        return view('owner.dashboard', compact('restaurant', 'stats', 'orders'));
    }

    public function chartData(Request $request)
    {
        $restaurant = Auth::user()->restaurant;
        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found.'], 404);
        }

        $period = $request->get('period', 'week');
        if (!in_array($period, ['day', 'week', 'month'], true)) {
            return response()->json(['message' => 'Invalid period.'], 422);
        }

        $bar = $this->buildOrdersBarChartData($restaurant->id, $period);

        return response()->json([
            'period' => $period,
            'title' => $bar['title'],
            'labels' => $bar['labels'],
            'data' => $bar['data'],
        ]);
    }

    public function acceptOrder(\App\Models\Order $order)
    {
        if ($order->restaurant->user_id !== Auth::id()) {
            abort(403);
        }

        $order->update(['status' => 'accepted']);
        return back()->with('success', 'Order #' . $order->id . ' has been accepted!');
    }

    public function deliverOrder(\App\Models\Order $order)
    {
        if ($order->restaurant->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'accepted') {
            return back()->with('error', 'Only accepted orders can be marked as delivered.');
        }

        $order->update(['status' => 'delivered']);
        return back()->with('success', 'Order #' . $order->id . ' has been marked as delivered!');
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
