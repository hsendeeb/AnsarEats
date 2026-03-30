<?php


namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Support\PerformanceCache;
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
            $orders = Order::where('restaurant_id', '=', $restaurantId)
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
            $orders = Order::where('restaurant_id', '=', $restaurantId)
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

        $orders = Order::where('restaurant_id', '=', $restaurantId)
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

        if (! $restaurant) {
            return redirect()->route('partner.with.us');
        }

        $latestRequest = $user->restaurantRegistrationRequests()
            ->latest()
            ->first();
        $pendingRequest = $latestRequest?->status === 'pending' ? $latestRequest : null;

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

            $orders = Order::where('restaurant_id', '=', $restaurant->id)->get();
            
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

            // Sparklines (last 14 days)
            $sparkDays = 14;
            $sparkStart = now()->subDays($sparkDays - 1)->startOfDay();
            $sparkEnd = now()->endOfDay();
            $ordersRecent = Order::where('restaurant_id', '=', $restaurant->id)
                ->whereBetween('created_at', [$sparkStart, $sparkEnd])
                ->get(['created_at', 'status', 'total']);

            $ordersByDate = $ordersRecent->groupBy(fn($o) => Carbon::parse($o->created_at)->format('Y-m-d'));
            $sparkDates = collect(range(0, $sparkDays - 1))
                ->map(fn($i) => now()->subDays($sparkDays - 1 - $i)->format('Y-m-d'));

            $sparkline = [
                'total_orders' => [],
                'pending_orders' => [],
                'completed_orders' => [],
                'revenue' => [],
                'avg_order_value' => [],
            ];

            foreach ($sparkDates as $date) {
                $dayOrders = $ordersByDate[$date] ?? collect();
                $dayTotalOrders = $dayOrders->count();
                $dayRevenue = $dayOrders->where('status', '!=', 'cancelled')->sum('total');
                $dayPending = $dayOrders->where('status', 'pending')->count();
                $dayCompleted = $dayOrders->where('status', 'delivered')->count();

                $sparkline['total_orders'][] = $dayTotalOrders;
                $sparkline['pending_orders'][] = $dayPending;
                $sparkline['completed_orders'][] = $dayCompleted;
                $sparkline['revenue'][] = (float) $dayRevenue;
                $sparkline['avg_order_value'][] = $dayTotalOrders > 0 ? (float) ($dayRevenue / $dayTotalOrders) : 0.0;
            }

            $stats['sparklines'] = $sparkline;

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
        
        return view('owner.dashboard', compact('restaurant', 'latestRequest', 'pendingRequest', 'stats'));
    }

    public function showPartnerForm()
    {
        $user = Auth::user();

        if ($user->restaurant) {
            return redirect()->route('owner.dashboard');
        }

        $latestRequest = $user->restaurantRegistrationRequests()
            ->latest()
            ->first();

        return view('owner.partner', compact('latestRequest'));
    }

    public function orders(Request $request)
    {
        $user = Auth::user();
        $restaurant = $user->restaurant;

        if (!$restaurant) {
            $pendingRequestExists = $user->restaurantRegistrationRequests()
                ->where('status', 'pending')
                ->exists();

            return redirect()
                ->route('owner.dashboard')
                ->with('error', $pendingRequestExists
                    ? 'Your restaurant request is still pending super admin approval.'
                    : 'Please submit a restaurant registration request first.');
        }

        $orders = PerformanceCache::remember(
            'owner-orders',
            json_encode([
                'restaurant_id' => $restaurant->id,
                'filter' => (string) $request->get('filter', ''),
                'status' => (string) $request->get('status', ''),
                'sort' => (string) $request->get('sort', ''),
                'page' => (int) $request->get('page', 1),
            ]),
            now()->addSeconds(config('performance.cache_ttl.owner_orders')),
            function () use ($request, $restaurant) {
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
                    if (in_array($request->status, ['pending', 'accepted', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'])) {
                        $ordersQuery->where('status', $request->status);
                    }
                }

                if ($request->has('sort') && $request->sort === 'total') {
                    $ordersQuery->orderByDesc('total');
                } else {
                    $ordersQuery->orderByDesc('created_at');
                }

                return $ordersQuery->paginate(5)->appends($request->query());
            }
        );

        return view('owner.orders', compact('restaurant', 'orders'));
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

    public function pollNewOrders(Request $request)
    {
        $restaurant = Auth::user()->restaurant;
        if (!$restaurant) {
            return response()->json(['count' => 0, 'latest_id' => 0]);
        }

        $sinceId = (int) $request->input('since_id', 0);

        $query = Order::where('restaurant_id', $restaurant->id)
                      ->where('status', 'pending');

        if ($sinceId > 0) {
            $query->where('id', '>', $sinceId);
        }

        $count = (clone $query)->count();
        $latestId = (clone $query)->max('id') ?? $sinceId;

        return response()->json([
            'count'     => $count,
            'latest_id' => $latestId,
        ]);
    }

    public function acceptOrder(Request $request, \App\Models\Order $order)
    {
        if ($order->restaurant->user_id !== Auth::id()) {
            abort(403);
        }

        $previousStatus = $order->status;
        $order->update([
            'status' => 'accepted',
            'estimated_prep_time' => $request->input('estimated_prep_time')
        ]);

        $this->sendOrderStatusUpdateEmail($order);
        $order->broadcastRealtimeUpdate('status_updated', $previousStatus);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Order #' . $order->id . ' has been accepted!']);
        }

        return back()->with('success', 'Order #' . $order->id . ' has been accepted!');
    }

    public function prepareOrder(\App\Models\Order $order)
    {
        if ($order->restaurant->user_id !== Auth::id()) abort(403);

        $previousStatus = $order->status;
        $order->update(['status' => 'preparing']);

        $this->sendOrderStatusUpdateEmail($order);
        $order->broadcastRealtimeUpdate('status_updated', $previousStatus);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Order #' . $order->id . ' is now being prepared!']);
        }

        return back()->with('success', 'Order #' . $order->id . ' is now being prepared!');
    }

    public function outForDeliveryOrder(\App\Models\Order $order)
    {
        if ($order->restaurant->user_id !== Auth::id()) abort(403);

        $previousStatus = $order->status;
        $order->update(['status' => 'out_for_delivery']);

        $this->sendOrderStatusUpdateEmail($order);
        $order->broadcastRealtimeUpdate('status_updated', $previousStatus);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Order #' . $order->id . ' is out for delivery!']);
        }

        return back()->with('success', 'Order #' . $order->id . ' is out for delivery!');
    }

    public function rejectOrder(Request $request, \App\Models\Order $order)
    {
        if ($order->restaurant->user_id !== Auth::id()) {
            abort(403);
        }

        $previousStatus = $order->status;
        $order->update([
            'status' => 'cancelled',
            'rejection_reason' => $request->input('rejection_reason', 'Cancelled by restaurant')
        ]);

        $this->sendOrderStatusUpdateEmail($order);
        $order->broadcastRealtimeUpdate('status_updated', $previousStatus);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Order #' . $order->id . ' has been rejected.']);
        }

        return back()->with('success', 'Order #' . $order->id . ' has been rejected.');
    }

    public function deliverOrder(\App\Models\Order $order)
    {
        if ($order->restaurant->user_id !== Auth::id()) {
            abort(403);
        }

        $previousStatus = $order->status;
        $order->update(['status' => 'delivered']);

        $this->sendOrderStatusUpdateEmail($order);
        $order->broadcastRealtimeUpdate('status_updated', $previousStatus);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Order #' . $order->id . ' has been marked as delivered!']);
        }

        return back()->with('success', 'Order #' . $order->id . ' has been marked as delivered!');
    }

    public function printOrder(\App\Models\Order $order)
    {
        if ($order->restaurant->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['restaurant', 'user', 'orderItems']);
        return view('owner.print-order', compact('order'));
    }

    public function storeOrUpdate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'phone' => 'required|string|max:50',
            'free_delivery' => 'sometimes|boolean',
            'delivery_fee' => 'nullable|required_unless:free_delivery,1|numeric|min:0',
            'is_open' => 'sometimes|boolean',
            'logo' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:4096',
            'operating_hours' => 'nullable|array'
        ]);

        $data['is_open'] = $request->has('is_open');
        $data['delivery_fee'] = $request->boolean('free_delivery')
            ? 0
            : (float) $request->input('delivery_fee', 0);
        unset($data['free_delivery']);

        $user = Auth::user();
        $restaurant = $user->restaurant;
        $pendingRequest = $user->restaurantRegistrationRequests()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('restaurants', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('restaurants', 'public');
        }

        $requestPayload = $data;
        $requestPayload['restaurant_name'] = $data['name'];
        unset($requestPayload['name']);

        if ($restaurant) {
            $restaurant->update($data);
            $user->update(['role' => 'owner']);
        } else {
            if ($pendingRequest) {
                $pendingRequest->update($requestPayload);

                return back()->with(
                    'success',
                    'Your restaurant registration request was updated and is waiting for super admin approval.'
                );
            }

            $user->restaurantRegistrationRequests()->create(array_merge($requestPayload, [
                'status' => 'pending',
            ]));

            return back()->with(
                'success',
                'Restaurant registration request submitted. A super admin must approve it before the restaurant is created.'
            );
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

        $category->deleteOrFail();
        return back()->with('success', 'Category deleted!');
    }
    public function toggleRestaurantStatus()
    {
        $restaurant = Auth::user()->restaurant;
        if (!$restaurant) abort(404);

        $restaurant->update(['is_open' => !$restaurant->is_open]);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_open' => $restaurant->is_open,
                'message' => 'Restaurant is now ' . ($restaurant->is_open ? 'Open' : 'Closed') . '!'
            ]);
        }
        
        return back()->with('success', 'Restaurant is now ' . ($restaurant->is_open ? 'Open' : 'Closed') . '!');
    }

    public function toggleCategoryVisibility(MenuCategory $category)
    {
        if ($category->restaurant->user_id !== Auth::id()) abort(403);
        
        $category->update(['is_visible' => !$category->is_visible]);
        return back()->with('success', 'Category is now ' . ($category->is_visible ? 'Visible' : 'Hidden') . '!');
    }

    public function toggleItemAvailability(\App\Models\MenuItem $item)
    {
        if ($item->menuCategory->restaurant->user_id !== Auth::id()) abort(403);

        $item->update(['is_available' => !$item->is_available]);
        return back()->with('success', 'Menu item is now ' . ($item->is_available ? 'Available' : 'Out of Stock') . '!');
    }

    public function toggleItemFeatured(\App\Models\MenuItem $item)
    {
        if ($item->menuCategory->restaurant->user_id !== Auth::id()) abort(403);

        $item->update(['is_featured' => !$item->is_featured]);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_featured' => $item->is_featured,
                'message' => 'Menu item is ' . ($item->is_featured ? 'now Featured' : 'no longer Featured') . '!'
            ]);
        }
        
        return back()->with('success', 'Menu item is ' . ($item->is_featured ? 'now Featured' : 'no longer Featured') . '!');
    }

    public function storePromotion(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'discount_percentage' => 'required|integer|min:1|max:100',
            'valid_until' => 'nullable|date',
        ]);

        $restaurant = Auth::user()->restaurant;
        if (!$restaurant) abort(404);

        $restaurant->promotions()->create($request->only('code', 'discount_percentage', 'valid_until'));

        return back()->with('success', 'Promotion added successfully!');
    }

    public function destroyPromotion(\App\Models\Promotion $promotion)
    {
        if ($promotion->restaurant->user_id !== Auth::id()) abort(403);
        $promotion->delete();
        return back()->with('success', 'Promotion removed!');
    }

    private function sendOrderStatusUpdateEmail(Order $order): void
    {
        try {
            \Illuminate\Support\Facades\Mail::to($order->user->email)->queue(new \App\Mail\OrderStatusUpdatedMail($order));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send order status email: ' . $e->getMessage());
        }
    }
}
