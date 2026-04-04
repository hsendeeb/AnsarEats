<?php


namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\RestaurantCustomerBlock;
use App\Models\User;
use App\Support\PerformanceCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private const REVENUE_ELIGIBLE_STATUSES = [
        'accepted',
        'preparing',
        'out_for_delivery',
        'delivered',
    ];

    private function applyOwnerOrderDateFilter($query, Request $request): void
    {
        if (! $request->filled('filter')) {
            return;
        }

        if ($request->filter === 'day') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($request->filter === 'week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        }
    }

    private function applyOwnerOrderStatusFilter($query, Request $request): void
    {
        if (! $request->filled('status')) {
            return;
        }

        if ($request->status === 'preparing') {
            $query->whereIn('status', ['preparing', 'out_for_delivery']);
        } elseif (in_array($request->status, ['pending', 'accepted', 'out_for_delivery', 'delivered', 'cancelled'], true)) {
            $query->where('status', $request->status);
        }
    }

    private function applyOwnerOrderTextSearch($query, string $search): void
    {
        $digits = preg_replace('/\D+/', '', $search);
        $orderNumber = $digits !== '' ? (int) $digits : null;

        $query->where(function ($searchQuery) use ($search, $orderNumber) {
            $searchQuery->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            })
            ->orWhere('phone', 'like', '%'.$search.'%');

            if ($orderNumber) {
                $searchQuery->orWhere('id', $orderNumber);
            }
        });
    }

    private function applyOwnerOrderSearchFilter($query, Request $request): void
    {
        $search = trim((string) $request->get('q', ''));

        if ($search === '') {
            return;
        }

        $this->applyOwnerOrderTextSearch($query, $search);
    }

    private function applyOwnerOrderSorting($query, Request $request): void
    {
        if ($request->get('sort') === 'total') {
            $query->orderByDesc('total');
            return;
        }

        $query->orderByDesc('created_at');
    }

    private function ownerOrderClearScopeLabel(Request $request): string
    {
        return match ($request->get('status')) {
            'pending' => 'pending',
            'accepted' => 'accepted',
            'preparing' => 'preparing',
            'out_for_delivery' => 'out for delivery',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
            default => 'visible',
        };
    }

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

    private function ownerCustomersQuery(Restaurant $restaurant)
    {
        $latestPhoneSubquery = Order::query()
            ->select('phone')
            ->whereColumn('user_id', 'users.id')
            ->where('restaurant_id', $restaurant->id)
            ->latest('created_at')
            ->limit(1);

        $blockedAtSubquery = RestaurantCustomerBlock::query()
            ->select('created_at')
            ->whereColumn('user_id', 'users.id')
            ->where('restaurant_id', $restaurant->id)
            ->limit(1);

        $isBlockedSubquery = RestaurantCustomerBlock::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('user_id', 'users.id')
            ->where('restaurant_id', $restaurant->id);

        return User::query()
            ->select('users.*')
            ->whereHas('orders', fn ($query) => $query->where('restaurant_id', $restaurant->id))
            ->withCount([
                'orders as restaurant_orders_count' => fn ($query) => $query->where('restaurant_id', $restaurant->id),
            ])
            ->selectSub($latestPhoneSubquery, 'restaurant_phone')
            ->selectSub($blockedAtSubquery, 'blocked_at')
            ->selectSub($isBlockedSubquery, 'is_blocked');
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
            'delivered_orders' => 0,
            'customers_count' => 0,
            'chart_data' => [
                'bar' => ['labels' => [], 'data' => []],
                'pie' => ['labels' => [], 'data' => []]
            ]
        ];

        if ($restaurant) {
            $restaurant->load('menuCategories.menuItems');

            $orders = Order::where('restaurant_id', '=', $restaurant->id)->get();
            $revenueOrders = $orders->whereIn('status', self::REVENUE_ELIGIBLE_STATUSES);
            
            $stats['total_orders'] = $orders->count();
            $stats['total_revenue'] = $revenueOrders->sum('total');
            $stats['pending_orders'] = $orders->where('status', 'pending')->count();
            $stats['delivered_orders'] = $orders->where('status', 'delivered')->count();
            $stats['customers_count'] = $orders->pluck('user_id')->filter()->unique()->count();
            $stats['avg_order_value'] = $revenueOrders->count() > 0
                ? $stats['total_revenue'] / $revenueOrders->count()
                : 0;

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
                'delivered_orders' => [],
                'customers' => [],
                'revenue' => [],
                'avg_order_value' => [],
            ];

            foreach ($sparkDates as $date) {
                $dayOrders = $ordersByDate[$date] ?? collect();
                $dayRevenueOrders = $dayOrders->whereIn('status', self::REVENUE_ELIGIBLE_STATUSES);
                $dayTotalOrders = $dayOrders->count();
                $dayRevenue = $dayRevenueOrders->sum('total');
                $dayPending = $dayOrders->where('status', 'pending')->count();
                $dayCompleted = $dayOrders->where('status', 'delivered')->count();
                $dayCustomers = $dayOrders->pluck('user_id')->filter()->unique()->count();

                $sparkline['total_orders'][] = $dayTotalOrders;
                $sparkline['pending_orders'][] = $dayPending;
                $sparkline['delivered_orders'][] = $dayCompleted;
                $sparkline['customers'][] = $dayCustomers;
                $sparkline['revenue'][] = (float) $dayRevenue;
                $sparkline['avg_order_value'][] = $dayRevenueOrders->count() > 0
                    ? (float) ($dayRevenue / $dayRevenueOrders->count())
                    : 0.0;
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

    public function customers()
    {
        $user = Auth::user();
        $restaurant = $user->restaurant;

        if (! $restaurant) {
            return redirect()
                ->route('owner.dashboard')
                ->with('error', 'Please create your restaurant first.');
        }

        $customers = $this->ownerCustomersQuery($restaurant)
            ->orderByDesc('restaurant_orders_count')
            ->orderBy('name')
            ->paginate(12);

        $summary = [
            'total_customers' => Order::where('restaurant_id', $restaurant->id)
                ->distinct('user_id')
                ->count('user_id'),
            'blocked_customers' => RestaurantCustomerBlock::where('restaurant_id', $restaurant->id)->count(),
            'total_customer_orders' => Order::where('restaurant_id', $restaurant->id)->count(),
        ];

        return view('owner.customers', compact('restaurant', 'customers', 'summary'));
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

        $baseOrdersQuery = Order::query()
            ->where('restaurant_id', $restaurant->id)
            ->unarchived();

        $this->applyOwnerOrderDateFilter($baseOrdersQuery, $request);
        $this->applyOwnerOrderSearchFilter($baseOrdersQuery, $request);

        $statusCounts = [
            'pending' => (clone $baseOrdersQuery)->where('status', 'pending')->count(),
            'accepted' => (clone $baseOrdersQuery)->where('status', 'accepted')->count(),
            'preparing' => (clone $baseOrdersQuery)->whereIn('status', ['preparing', 'out_for_delivery'])->count(),
            'delivered' => (clone $baseOrdersQuery)->where('status', 'delivered')->count(),
        ];

        $orders = PerformanceCache::remember(
            'owner-orders',
            json_encode([
                'restaurant_id' => $restaurant->id,
                'filter' => (string) $request->get('filter', ''),
                'status' => (string) $request->get('status', ''),
                'q' => (string) $request->get('q', ''),
                'sort' => (string) $request->get('sort', ''),
                'page' => (int) $request->get('page', 1),
            ]),
            now()->addSeconds(config('performance.cache_ttl.owner_orders')),
            function () use ($request, $restaurant) {
                $ordersQuery = Order::with(['user', 'orderItems'])
                    ->where('restaurant_id', $restaurant->id)
                    ->unarchived();

                $this->applyOwnerOrderDateFilter($ordersQuery, $request);
                $this->applyOwnerOrderStatusFilter($ordersQuery, $request);
                $this->applyOwnerOrderSearchFilter($ordersQuery, $request);
                $this->applyOwnerOrderSorting($ordersQuery, $request);

                return $ordersQuery->paginate(10)->appends($request->query());
            }
        );

        return view('owner.orders', compact('restaurant', 'orders', 'statusCounts'));
    }

    public function orderSuggestions(Request $request)
    {
        $restaurant = Auth::user()?->restaurant;

        if (! $restaurant) {
            return response()->json(['orders' => []]);
        }

        $search = trim((string) $request->get('q', ''));

        if (mb_strlen($search) < 2) {
            return response()->json(['orders' => []]);
        }

        $payload = PerformanceCache::remember(
            'owner-order-suggestions',
            json_encode([
                'restaurant_id' => $restaurant->id,
                'filter' => (string) $request->get('filter', ''),
                'status' => (string) $request->get('status', ''),
                'q' => mb_strtolower($search),
            ]),
            now()->addSeconds(max(10, (int) config('performance.cache_ttl.owner_orders'))),
            function () use ($request, $restaurant, $search) {
                $ordersQuery = Order::with(['user:id,name,email'])
                    ->where('restaurant_id', $restaurant->id)
                    ->unarchived();

                $this->applyOwnerOrderDateFilter($ordersQuery, $request);
                $this->applyOwnerOrderStatusFilter($ordersQuery, $request);
                $this->applyOwnerOrderTextSearch($ordersQuery, $search);

                $orders = $ordersQuery
                    ->latest()
                    ->limit(6)
                    ->get(['id', 'user_id', 'status', 'phone', 'total', 'created_at']);

                return [
                    'orders' => $orders->map(function ($order) {
                        $statusTone = match ($order->status) {
                            'pending' => 'bg-amber-100 text-amber-600',
                            'accepted' => 'bg-emerald-100 text-emerald-600',
                            'preparing', 'out_for_delivery' => 'bg-indigo-100 text-indigo-600',
                            'delivered' => 'bg-blue-100 text-blue-600',
                            default => 'bg-gray-100 text-gray-600',
                        };

                        return [
                            'id' => $order->id,
                            'order_number' => '#'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
                            'search_value' => '#'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
                            'customer_name' => $order->user?->name ?? 'Guest customer',
                            'phone' => $order->phone ?: 'No phone provided',
                            'created_at' => $order->created_at?->format('M d, h:i A'),
                            'total' => '$'.number_format((float) $order->total, 2),
                            'status_label' => ucwords(str_replace('_', ' ', $order->status)),
                            'status_tone' => $statusTone,
                        ];
                    })->values()->all(),
                ];
            }
        );

        return response()->json($payload);
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

    public function blockCustomer(User $customer)
    {
        $restaurant = Auth::user()?->restaurant;

        if (! $restaurant) {
            abort(404);
        }

        $hasOrderedFromRestaurant = Order::where('restaurant_id', $restaurant->id)
            ->where('user_id', $customer->id)
            ->exists();

        if (! $hasOrderedFromRestaurant) {
            abort(404);
        }

        $block = RestaurantCustomerBlock::firstOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'user_id' => $customer->id,
            ],
            [
                'blocked_by_user_id' => Auth::id(),
            ]
        );

        $message = $block->wasRecentlyCreated
            ? $customer->name.' has been blocked from ordering from your restaurant.'
            : $customer->name.' is already blocked from ordering from your restaurant.';

        return back()->with('success', $message);
    }

    public function unblockCustomer(User $customer)
    {
        $restaurant = Auth::user()?->restaurant;

        if (! $restaurant) {
            abort(404);
        }

        $deletedCount = RestaurantCustomerBlock::where('restaurant_id', $restaurant->id)
            ->where('user_id', $customer->id)
            ->delete();

        $message = $deletedCount > 0
            ? $customer->name.' has been unblocked and can order from your restaurant again.'
            : $customer->name.' is not currently blocked from your restaurant.';

        return back()->with('success', $message);
    }

    public function pollNewOrders(Request $request)
    {
        $restaurant = Auth::user()->restaurant;
        if (!$restaurant) {
            return response()->json(['count' => 0, 'latest_id' => 0]);
        }

        $sinceId = (int) $request->input('since_id', 0);

        $query = Order::where('restaurant_id', $restaurant->id)
                      ->where('status', 'pending')
                      ->unarchived();

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

    public function destroyOrder(Request $request, \App\Models\Order $order)
    {
        if ($order->restaurant->user_id !== Auth::id()) {
            abort(403);
        }

        $orderId = $order->id;
        $order->update(['archived_at' => now()]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order #'.$orderId.' deleted successfully.',
            ]);
        }

        return back()->with('success', 'Order #'.$orderId.' deleted successfully.');
    }

    public function clearOrders(Request $request)
    {
        $restaurant = Auth::user()->restaurant;

        if (! $restaurant) {
            abort(404);
        }

        $ordersQuery = Order::where('restaurant_id', $restaurant->id)
            ->unarchived();

        $this->applyOwnerOrderDateFilter($ordersQuery, $request);
        $this->applyOwnerOrderStatusFilter($ordersQuery, $request);
        $this->applyOwnerOrderSearchFilter($ordersQuery, $request);

        $deletedCount = $ordersQuery->update(['archived_at' => now()]);
        $scopeLabel = $this->ownerOrderClearScopeLabel($request);

        if ($deletedCount === 0) {
            $message = 'No orders matched the current filters.';
        } elseif ($deletedCount === 1) {
            $message = '1 '.$scopeLabel.' order was cleared successfully.';
        } else {
            $message = $deletedCount.' '.$scopeLabel.' orders were cleared successfully.';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroySelectedOrders(Request $request)
    {
        $restaurant = Auth::user()->restaurant;

        if (! $restaurant) {
            abort(404);
        }

        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer'],
        ]);

        $orderIds = collect($data['order_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $deletedCount = Order::where('restaurant_id', $restaurant->id)
            ->unarchived()
            ->whereIn('id', $orderIds)
            ->update(['archived_at' => now()]);

        if ($deletedCount === 0) {
            $message = 'No selected orders could be deleted.';
        } elseif ($deletedCount === 1) {
            $message = '1 selected order was deleted successfully.';
        } else {
            $message = $deletedCount.' selected orders were deleted successfully.';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
            ]);
        }

        return back()->with('success', $message);
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
