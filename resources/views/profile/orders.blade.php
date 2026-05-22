@extends('layouts.app')

@section('skeleton')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
        <div>
            <div class="w-48 h-10 bg-gray-200 dark:bg-gray-800 rounded-xl mb-3 animate-pulse"></div>
            <div class="w-64 h-5 bg-gray-200 dark:bg-gray-800 rounded-lg animate-pulse"></div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-1 space-y-6">
            <div class="w-full h-14 bg-gray-200 dark:bg-gray-800 rounded-2xl animate-pulse"></div>
            <div class="w-full h-48 bg-gray-200 dark:bg-gray-800 rounded-3xl animate-pulse"></div>
        </div>
        <div class="md:col-span-2 space-y-6">
            @for ($i = 0; $i < 4; $i++)
            <div class="w-full h-24 bg-gray-200 dark:bg-gray-800 rounded-3xl animate-pulse"></div>
            @endfor
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8" x-data="{ showClearModal: false }">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 outfit tracking-tight mb-2">Order History</h1>
            <p class="text-gray-500 font-medium">Review your past orders and metrics.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Sidebar Navigation & Filters -->
        <div class="md:col-span-1">
            <div class="sticky top-28 space-y-6">
                
                <!-- Filter Dropdown (Dashboard Style) -->
                @php
                    $activeFilterLabel = 'All Orders';
                    if ($activeFilter === 'today') $activeFilterLabel = 'Today';
                    elseif ($activeFilter === 'week') $activeFilterLabel = 'This Week';
                    elseif ($activeFilter === 'month') $activeFilterLabel = 'This Month';
                @endphp
                
                <div class="flex items-stretch gap-3">
                    <div class="relative z-20 flex-1" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" type="button"
                        class="w-full flex items-center justify-between gap-2 px-5 py-4 rounded-2xl text-sm font-bold transition-all
                        {{ $activeFilter != 'all' ? 'bg-gray-900 text-white shadow-lg shadow-gray-900/30' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            <span>{{ $activeFilterLabel }}</span>
                        </div>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute left-0 right-0 mt-3 hidden w-full md:w-56 rounded-2xl bg-white shadow-2xl border border-gray-100 overflow-hidden"
                         :class="{'hidden': !open}"
                         style="display: none;">
                        <div class="p-2 flex flex-col gap-1">
                            <a href="{{ route('profile.orders') }}" class="px-4 py-2.5 rounded-xl text-sm font-bold {{ $activeFilter == 'all' ? 'bg-emerald-50 text-emerald-600' : 'text-gray-500 hover:bg-gray-50' }} transition-all flex items-center justify-between">
                                All Orders
                                @if($activeFilter == 'all')<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>@endif
                            </a>
                            <a href="{{ route('profile.orders', ['filter' => 'today']) }}" class="px-4 py-2.5 rounded-xl text-sm font-bold {{ $activeFilter == 'today' ? 'bg-emerald-50 text-emerald-600' : 'text-gray-500 hover:bg-gray-50' }} transition-all flex items-center justify-between">
                                Today
                                @if($activeFilter == 'today')<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>@endif
                            </a>
                            <a href="{{ route('profile.orders', ['filter' => 'week']) }}" class="px-4 py-2.5 rounded-xl text-sm font-bold {{ $activeFilter == 'week' ? 'bg-emerald-50 text-emerald-600' : 'text-gray-500 hover:bg-gray-50' }} transition-all flex items-center justify-between">
                                This Week
                                @if($activeFilter == 'week')<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>@endif
                            </a>
                            <a href="{{ route('profile.orders', ['filter' => 'month']) }}" class="px-4 py-2.5 rounded-xl text-sm font-bold {{ $activeFilter == 'month' ? 'bg-emerald-50 text-emerald-600' : 'text-gray-500 hover:bg-gray-50' }} transition-all flex items-center justify-between">
                                This Month
                                @if($activeFilter == 'month')<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>@endif
                            </a>
                        </div>
                    </div>
                    </div>

                    @if($orders->count() > 0)
                        <button
                            @click="showClearModal = true"
                            type="button"
                            aria-label="Clear order history"
                            title="Clear order history"
                            class="md:hidden inline-flex h-[3.5rem] w-[3.5rem] shrink-0 items-center justify-center rounded-2xl  text-red-600 transition-all"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    @endif
                </div>

                <!-- Simple Merit Metrics -->
                <div class="p-6 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl text-white shadow-xl shadow-emerald-500/20">
                    <p class="text-xs font-black uppercase tracking-widest opacity-80 mb-4">Total Spent</p>
                    <p class="text-4xl font-extrabold outfit mb-1">${{ number_format($ordersSummary['total'] ?? 0, 2) }}</p>
                    <p class="text-sm font-bold opacity-80 mb-6">{{ $ordersSummary['count'] ?? 0 }} total orders</p>
                    
                    @if(($ordersSummary['count'] ?? 0) > 0)
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20"
                         x-data="{
                            initChart() {
                                const ctx = this.$refs.canvas;
                                const lastOrders = {{ $orders->getCollection()->take(7)->reverse()->values() }};
                                const labels = lastOrders.map(o => new Date(o.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }));
                                const data = lastOrders.map(o => parseFloat(o.total));

                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            label: 'Spent',
                                            data: data,
                                            borderColor: 'rgba(255, 255, 255, 1)',
                                            backgroundColor: 'rgba(255, 255, 255, 0.1)',
                                            borderWidth: 3,
                                            fill: true,
                                            tension: 0.4,
                                            pointRadius: lastOrders.length === 1 ? 4 : 0
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false }, tooltip: { enabled: true } },
                                        scales: { 
                                            x: { display: false }, 
                                            y: { display: false, beginAtZero: true } 
                                        }
                                    }
                                });
                            }
                         }"
                         x-init="initChart()">
                        <canvas x-ref="canvas" height="150"></canvas>
                    </div>
                    @endif
                </div>

            </div>
        </div>

        <!-- Orders List Section -->
        <div class="md:col-span-2 space-y-6"
             x-data="ordersTracker({
                page: {{ $orders->currentPage() }},
                hasMore: {{ $orders->hasMorePages() ? 'true' : 'false' }},
                requestUrl: @js(route('profile.orders', request()->query()))
             })"
             x-init="init()">
            @if($orders->count() > 0)
            <div id="orders-grid" class="space-y-6">
                @include('profile.partials.order-cards', ['orders' => $orders])
            </div>
            <div x-show="hasMore" x-intersect.full="loadMore()" class="pt-2 pb-4">
                <div x-show="loadingMore" class="flex items-center justify-center py-6">
                    <svg class="animate-spin h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            @else
            <div class="bg-white rounded-3xl border-2 border-dashed border-gray-200 p-12 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No orders found</h3>
                <p class="text-gray-500 font-medium mb-6">Looks like you haven't placed any orders yet, or the filter returned no results.</p>
                <a href="{{ route('home') }}" class="inline-block px-8 py-3 bg-emerald-500 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-400 transition-all">Start Exploring</a>
            </div>
            @endif
        </div>
    </div>

    @if(($ordersSummary['count'] ?? 0) > 0)
        <div x-show="showClearModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-950/50 backdrop-blur-sm"
             x-cloak>
            <div @click.outside="showClearModal = false"
                 x-show="showClearModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center">
                
                <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <h3 class="text-xl font-bold text-gray-900 mb-2">Clear Order History?</h3>
                <p class="text-gray-500 mb-8">This will permanently delete your entire order history. This action cannot be undone.</p>

                <div class="flex items-center justify-center gap-3">
                    <button @click="showClearModal = false" type="button" class="flex-1 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all text-sm">
                        Cancel
                    </button>
                    <form action="{{ route('profile.clear') }}" method="POST" class="flex-1" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <button type="submit" :disabled="submitting" class="w-full px-4 py-2.5 bg-red-600 text-white font-bold rounded-xl hover:bg-red-500 transition-all text-sm disabled:opacity-70 disabled:cursor-not-allowed">
                            <span x-show="!submitting">Delete</span>
                            <span x-show="submitting" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Deleting
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function ordersTracker(initialState = {}) {
    const ORDER_STEPS = ['pending', 'accepted', 'preparing', 'out_for_delivery', 'delivered'];
    const TERMINAL    = ['delivered', 'cancelled'];

    return {
        page: initialState.page ?? 1,
        hasMore: initialState.hasMore ?? false,
        requestUrl: initialState.requestUrl ?? window.location.href,
        loadingMore: false,
        statuses: {},
        channels: {},
        usingEcho: false,
        pollTimer: null,
        pollInFlight: false,
        pollingFallbackBound: false,
        pollConfig: {
            visible: {{ (int) config('performance.polling.profile_visible_ms') }},
            hidden: {{ (int) config('performance.polling.profile_hidden_ms') }},
            retry: {{ (int) config('performance.polling.profile_retry_ms') }},
            focus: {{ (int) config('performance.polling.profile_focus_ms') }},
        },

        init() {
            this.collectStatusesFromDom();

            console.log('Order Tracker initialized with IDs:', Object.keys(this.statuses));

            this.usingEcho = this.subscribeToRealtime();

            window.addEventListener('realtime:connected', () => {
                console.log('Realtime connected signal received');
                this.usingEcho = true;
                this.stopPolling();
                // Re-subscribe if needed
                Object.keys(this.statuses).forEach((orderId) => this.subscribeToOrder(orderId));
            });

            if (this.usingEcho) {
                window.waitForRealtimeConnection?.(3000).then((connected) => {
                    this.usingEcho = connected;
                    console.log(connected ? 'Realtime connection verified' : 'Realtime connection timed out, using fallback');

                    if (!connected && this.getActiveIds().length > 0) {
                        this.enablePollingFallback();
                    }
                });
            } else {
                this.enablePollingFallback();
            }
        },

        collectStatusesFromDom(scope = document) {
            scope.querySelectorAll('[data-order-id]').forEach((el) => {
                this.statuses[el.dataset.orderId] = el.dataset.orderStatus;
            });
            this.statuses = { ...this.statuses };
        },

        async loadMore() {
            if (this.loadingMore || !this.hasMore) {
                return;
            }

            this.loadingMore = true;

            try {
                const url = new URL(this.requestUrl, window.location.origin);
                url.searchParams.set('page', this.page + 1);

                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                const grid = document.getElementById('orders-grid');

                if (grid && data.html) {
                    grid.insertAdjacentHTML('beforeend', data.html);
                    this.page = data.nextPage - 1;
                    this.hasMore = !!data.hasMore;
                    this.collectStatusesFromDom(grid);

                    Object.keys(this.statuses).forEach((orderId) => this.subscribeToOrder(orderId));

                    if (!this.usingEcho && this.getActiveIds().length > 0) {
                        this.enablePollingFallback();
                    }
                } else {
                    this.hasMore = false;
                }
            } catch (error) {
                console.error('Error loading more orders:', error);
            } finally {
                this.loadingMore = false;
            }
        },

        getStatus(id, fallback) {
            return this.statuses[id] ?? fallback;
        },

        getActiveIds() {
            return Object.entries(this.statuses)
                .filter(([, status]) => !TERMINAL.includes(status))
                .map(([id]) => id);
        },

        isLiveStatus(status) {
            return !TERMINAL.includes(status);
        },

        subscribeToRealtime() {
            if (!window.Echo) {
                console.warn('Laravel Echo not found on window');
                return false;
            }

            try {
                Object.keys(this.statuses).forEach((orderId) => this.subscribeToOrder(orderId));
                return true;
            } catch (error) {
                console.error('Failed to subscribe via Echo:', error);
                return false;
            }
        },

        subscribeToOrder(orderId) {
            if (this.channels[orderId] || !window.Echo) {
                return;
            }

            console.log(`Subscribing to private channel: order.${orderId}`);
            this.channels[orderId] = window.Echo.private(`order.${orderId}`);
            
            this.channels[orderId]
                .listen('.order.updated', (payload) => {
                    console.log(`Received order.updated for #${orderId}:`, payload);
                    this.handleRealtimeUpdate(payload);
                })
                .subscribed(() => {
                    console.log(`Successfully subscribed to order.${orderId}`);
                })
                .error((error) => {
                    console.error(`Subscription error for order.${orderId}:`, error);
                });
        },

        handleRealtimeUpdate(payload) {
            const order = payload?.order;

            if (!order?.id) return;

            const prevStatus = this.statuses[order.id];
            
            // Force Alpine reactivity by creating a new object reference
            this.statuses = { ...this.statuses, [order.id]: order.status };

            console.log(`Status updated for #${order.id}: ${prevStatus} -> ${order.status}`);

            // Send browser notification for status change
            if (prevStatus !== order.status && window.sendOrderNotification) {
                window.sendOrderNotification(order.id, order.status, payload.message);
            }

            const orderCard = document.querySelector(`[data-order-id="${order.id}"]`);
            if (orderCard) {
                orderCard.dataset.orderStatus = order.status;
            }
        },

        currentPollDelay() {
            return document.hidden ? this.pollConfig.hidden : this.pollConfig.visible;
        },

        enablePollingFallback() {
            if (this.pollingFallbackBound) {
                if (this.getActiveIds().length > 0) {
                    this.schedulePoll(this.pollConfig.visible);
                }
                return;
            }

            console.log('Enabling polling fallback...');
            this.pollingFallbackBound = true;

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.stopPolling();
                } else {
                    this.schedulePoll(this.pollConfig.focus);
                }
            });

            window.addEventListener('online', () => this.schedulePoll(this.pollConfig.focus));

            if (this.getActiveIds().length > 0) {
                this.schedulePoll(this.pollConfig.visible);
            }
        },

        stopPolling() {
            if (this.pollTimer) {
                clearTimeout(this.pollTimer);
                this.pollTimer = null;
            }
        },

        schedulePoll(delay = null) {
            this.stopPolling();

            const activeIds = this.getActiveIds();
            const hasSubscribedChannel = activeIds.some(id => {
                const channel = window.Echo?.connector?.channels[`private-order.${id}`];
                return channel && channel.subscribed;
            });

            if (hasSubscribedChannel) {
                this.usingEcho = true;
            }

            if (this.usingEcho || document.hidden || !navigator.onLine || activeIds.length === 0) {
                return;
            }

            this.pollTimer = setTimeout(() => this.pollStatuses(), delay ?? this.currentPollDelay());
        },

        async pollStatuses() {
            const activeIds = this.getActiveIds();
            if (activeIds.length === 0 || this.pollInFlight) return;

            this.pollInFlight = true;
            try {
                const res = await fetch('{{ route("orders.batch-status") }}?ids=' + activeIds.join(','), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) {
                    this.schedulePoll(this.pollConfig.retry);
                    return;
                }
                const data = await res.json();
                
                // Update statuses reactively
                let updated = false;
                Object.entries(data).forEach(([id, info]) => {
                    if (this.statuses[id] !== info.status) {
                        this.statuses[id] = info.status;
                        updated = true;
                    }
                });
                
                if (updated) {
                    this.statuses = { ...this.statuses }; // Force reactivity
                }

                this.schedulePoll(this.currentPollDelay());
            } catch(e) {
                this.schedulePoll(this.pollConfig.retry);
            } finally {
                this.pollInFlight = false;
            }
        },

        isStepDone(stepIndex, currentStatus) {
            const currentIdx = ORDER_STEPS.indexOf(currentStatus);
            return stepIndex <= currentIdx;
        },

        isStepActive(stepIndex, currentStatus) {
            return ORDER_STEPS.indexOf(currentStatus) === stepIndex;
        },

        getStatusClass(status) {
            const map = {
                'pending':          'bg-amber-100 text-amber-600',
                'accepted':         'bg-blue-100 text-blue-600',
                'preparing':        'bg-indigo-100 text-indigo-600',
                'out_for_delivery': 'bg-teal-100 text-teal-600',
                'delivered':        'bg-emerald-100 text-emerald-600',
                'cancelled':        'bg-red-100 text-red-600',
            };
            return map[status] ?? 'bg-gray-100 text-gray-600';
        },

        formatStatus(status) {
            return status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        },

        destroy() {
            this.stopPolling();
            Object.keys(this.channels).forEach((orderId) => {
                window.Echo?.leaveChannel(`private-order.${orderId}`);
            });
        }
    };
}
</script>
@endpush

