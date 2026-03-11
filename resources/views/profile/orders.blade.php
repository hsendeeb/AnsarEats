@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900 outfit tracking-tight mb-2">Order History</h1>
            <p class="text-gray-500 font-medium">Review your past orders and metrics.</p>
        </div>
        
        <div class="flex items-center gap-2">
            @if($orders->count() > 0)
            <form action="{{ route('profile.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear your entire order history? This cannot be undone.')">
                @csrf
                <button type="submit" class="px-5 py-2.5 bg-red-50 text-red-600 font-bold rounded-xl hover:bg-red-100 transition-all text-sm border border-red-100 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Clear History
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Sidebar Navigation -->
        <div class="md:col-span-1">
            <nav class="space-y-2 sticky top-28">
                <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-5 py-4 rounded-2xl bg-white text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 font-bold transition-all border border-transparent hover:border-emerald-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Account Info
                </a>
                <a href="{{ route('profile.orders') }}" class="flex items-center gap-3 px-5 py-4 rounded-2xl bg-emerald-500 text-white font-bold shadow-lg shadow-emerald-500/20 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Order History
                </a>
                
                <hr class="my-4 border-gray-100">
                
                <div class="p-2">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3 px-3">Filter by Time</p>
                    <div class="flex flex-col gap-1">
                        <a href="{{ route('profile.orders') }}" class="px-4 py-2 rounded-xl text-sm font-bold {{ $activeFilter == 'all' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100' }} transition-all">All Orders</a>
                        <a href="{{ route('profile.orders', ['filter' => 'today']) }}" class="px-4 py-2 rounded-xl text-sm font-bold {{ $activeFilter == 'today' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100' }} transition-all">Today</a>
                        <a href="{{ route('profile.orders', ['filter' => 'week']) }}" class="px-4 py-2 rounded-xl text-sm font-bold {{ $activeFilter == 'week' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100' }} transition-all">This Week</a>
                        <a href="{{ route('profile.orders', ['filter' => 'month']) }}" class="px-4 py-2 rounded-xl text-sm font-bold {{ $activeFilter == 'month' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100' }} transition-all">This Month</a>
                    </div>
                </div>

                <!-- Simple Merit Metrics -->
                <div class="mt-8 p-6 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl text-white shadow-xl shadow-emerald-500/20">
                    <p class="text-xs font-black uppercase tracking-widest opacity-80 mb-4">Total Spent</p>
                    <p class="text-4xl font-extrabold outfit mb-1">${{ number_format($orders->sum('total'), 2) }}</p>
                    <p class="text-sm font-bold opacity-80 mb-6">{{ $orders->count() }} total orders</p>
                    
                    @if($orders->count() > 0)
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20"
                         x-data="{
                            initChart() {
                                const ctx = this.$refs.canvas;
                                const lastOrders = {{ $orders->take(7)->reverse()->values() }};
                                const labels = lastOrders.map(o => new Date(o.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }));
                                const data = lastOrders.map(o => o.total);

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
                                            pointRadius: 0
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false }, tooltip: { enabled: true } },
                                        scales: { x: { display: false }, y: { display: false } }
                                    }
                                });
                            }
                         }"
                         x-init="initChart()">
                        <canvas x-ref="canvas" height="150"></canvas>
                    </div>
                    @endif
                </div>
            </nav>
        </div>

        <!-- Orders List Section -->
        <div class="md:col-span-2 space-y-6">
            @forelse($orders as $order)
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100">
                            @if($order->restaurant->logo)
                                <img src="{{ Storage::url($order->restaurant->logo) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-lg font-black text-emerald-500">{{ substr($order->restaurant->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-extrabold text-gray-900">{{ $order->restaurant->name }}</h3>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-tighter">{{ $order->created_at->format('M d, Y • h:i A') }}</p>
                        </div>
                    </div>
                    <div class="">
                        <div class="font-black text-gray-900">${{ number_format($order->total, 2) }}</div>
                        <span class="inline-block ms-2 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                            {{ $order->status === 'accepted' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}">
                            {{ $order->status }}
                        </span>
                    </div>
                </div>
                <div class="p-6 bg-gray-50/50">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Items Ordered</p>
                    <div class="space-y-2">
                        @foreach($order->orderItems as $item)
                        <div class="flex justify-between items-center bg-white p-2 rounded-xl mb-2 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 flex-shrink-0">
                                    @if($item->menuItem && $item->menuItem->image)
                                        <img src="{{ Storage::url($item->menuItem->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-emerald-50 text-emerald-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900 line-clamp-1">{{ $item->name }}</span>
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $item->quantity }}x @ ${{ number_format($item->price, 2) }}</span>
                                </div>
                            </div>
                            <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">${{ number_format($item->price * $item->quantity, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-3xl border-2 border-dashed border-gray-200 p-12 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No orders found</h3>
                <p class="text-gray-500 font-medium mb-6">Looks like you haven't placed any orders yet, or the filter returned no results.</p>
                <a href="{{ route('home') }}" class="inline-block px-8 py-3 bg-emerald-500 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-400 transition-all">Start Exploring</a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
