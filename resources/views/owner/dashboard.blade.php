@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 relative" x-data="{ 
    showRestaurantModal: false, 
    showCategoryModal: false,
    showEditCategoryModal: false,
    showMenuItemModal: false,
    showEditMenuItemModal: false,
    selectedCategoryId: null,
    editingCategory: { id: null, name: '' },
    editingMenuItem: { id: null, name: '', description: '', price: '', category_id: null, image_url: '', variants: null, variant_type: '' },
    isRestaurantOpen: {{ $restaurant && $restaurant->is_open ? 'true' : 'false' }},
    togglingStatus: false,
    async toggleRestaurantStatus() {
        if (this.togglingStatus) return;
        this.togglingStatus = true;
        try {
            const res = await fetch('{{ route('owner.restaurant.toggle-status') }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.isRestaurantOpen = data.is_open;
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message } }));
            }
        } catch (e) {
            console.error('Toggle failed', e);
        } finally {
            this.togglingStatus = false;
        }
    },
    hasOpenModal() {
        return this.showRestaurantModal
            || this.showCategoryModal
            || this.showEditCategoryModal
            || this.showMenuItemModal
            || this.showEditMenuItemModal;
    }
}" x-effect="document.documentElement.classList.toggle('modal-open', hasOpenModal()); document.body.classList.toggle('modal-open', hasOpenModal());">

    <div class="max-w-7xl mx-auto">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 rounded-[2rem] p-8 md:p-12 mb-10 text-white relative overflow-hidden shadow-2xl shadow-purple-500/30">
            <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full"></div>
            <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-white/10 rounded-full"></div>
            <div class="absolute top-8 right-8 w-12 h-12 bg-white/20 rounded-xl rotate-12 animate-bounce" style="animation-duration: 3s;"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-4xl md:text-5xl font-black outfit tracking-tight leading-tight">
                        {{ $restaurant ? $restaurant->name : Auth::user()->name }}
                    </h1>
                    <p class="mt-3 text-purple-200 font-medium text-lg max-w-lg">
                        Manage your restaurant, categories, and menu items from this dashboard.
                    </p>
                </div>
                
                <div class="flex flex-col lg:flex-row lg:items-center gap-4 w-full lg:w-auto">
                    @if($restaurant)
                    <div class="flex-shrink-0">
                        <button type="button" @click="toggleRestaurantStatus()" :disabled="togglingStatus" class="group w-full lg:w-auto flex items-center gap-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold py-3 px-5 rounded-2xl transition-all shadow-lg backdrop-blur-sm disabled:opacity-50">
                            <div class="relative w-12 h-6 rounded-full transition-colors" :class="isRestaurantOpen ? 'bg-emerald-500' : 'bg-gray-400'">
                                <div class="absolute top-1 left-1 w-4 h-4 rounded-full bg-white transition-transform" :class="isRestaurantOpen ? 'translate-x-6' : ''"></div>
                            </div>
                            <span x-text="isRestaurantOpen ? 'Accepting Orders' : 'Closed'"></span>
                        </button>
                    </div>
                    @endif

                    <button @click="showRestaurantModal = true" class="flex-shrink-0 w-full lg:w-auto bg-white/20 backdrop-blur-sm hover:bg-white/30 border border-white/30 text-white font-bold py-3 px-6 rounded-2xl transition-all transform hover:-translate-y-0.5 active:scale-95 shadow-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        {{ $restaurant ? 'Edit Restaurant' : 'Create Restaurant' }}
                    </button>
                </div>
            </div>
        </div>

        @if(!$restaurant)
            <!-- Empty State -->
            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-xl p-16 text-center">
                <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-purple-100 text-purple-500 mb-8 animate-bounce" style="animation-duration: 2s;">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <h3 class="text-3xl font-black outfit text-gray-900 mb-3">Start Your Journey</h3>
                <p class="text-gray-500 text-lg font-medium max-w-md mx-auto mb-8">
                    Create your restaurant profile first. Then you can add categories and menu items.
                </p>
                <button @click="showRestaurantModal = true" class="inline-flex items-center gap-2 px-8 py-4 bg-gray-900 hover:bg-purple-600 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl hover:shadow-purple-500/30 transition-all transform hover:-translate-y-0.5 active:scale-95 text-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Create Your Restaurant
                </button>
            </div>
        @else
            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 mb-10">
                <button @click="showCategoryModal = true" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Add Category
                </button>
                
                @if($restaurant->menuCategories->count() > 0)
                <button @click="showMenuItemModal = true" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-400 text-white font-bold rounded-2xl shadow-lg shadow-amber-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Add Menu Item
                </button>
                @endif
                
                <a href="{{ route('restaurant.show', $restaurant) }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white border-2 border-gray-200 hover:border-emerald-500 text-gray-700 hover:text-emerald-600 font-bold rounded-2xl transition-all transform hover:-translate-y-0.5 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    View Public Page
                </a>
            </div>

            <!-- Analytics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
                <!-- Total Revenue -->
                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 group hover:shadow-xl hover:shadow-emerald-500/10 transition-all relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 4v16m8-8H4"></path></svg>
                        </div>
                        <span class="text-xs font-black px-2 py-1 bg-emerald-50 text-emerald-600 rounded-lg">Revenue</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400">Total Revenue</p>
                        <p class="text-2xl lg:text-3xl font-black text-gray-900 outfit">${{ number_format($stats['total_revenue'], 2) }}</p>
                    </div>
                    <div class="absolute bottom-4 right-4 w-24 h-12">
                        <canvas class="sparkline" data-color="#10b981" data-sparkline='@json($stats["sparklines"]["revenue"] ?? $stats["chart_data"]["bar"]["data"] ?? [])'></canvas>
                    </div>
                </div>

                <!-- Avg Order Value -->
                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 group hover:shadow-xl hover:shadow-teal-500/10 transition-all relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-teal-100 rounded-2xl flex items-center justify-center text-teal-500 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="text-xs font-black px-2 py-1 bg-teal-50 text-teal-600 rounded-lg">AOV</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400">Avg. Order</p>
                        <p class="text-2xl lg:text-3xl font-black text-gray-900 outfit">${{ number_format($stats['avg_order_value'], 2) }}</p>
                    </div>
                    <div class="absolute bottom-4 right-4 w-24 h-12">
                        <canvas class="sparkline" data-color="#14b8a6" data-sparkline='@json($stats["sparklines"]["avg_order_value"] ?? $stats["chart_data"]["bar"]["data"] ?? [])'></canvas>
                    </div>
                </div>

                <!-- Total Orders -->
                <a href="{{ route('owner.orders') }}" class="block bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 group hover:shadow-xl hover:shadow-indigo-500/10 transition-all text-left cursor-pointer relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <span class="text-xs font-black px-2 py-1 bg-indigo-50 text-indigo-600 rounded-lg">Total</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400">Total Orders</p>
                        <p class="text-2xl lg:text-3xl font-black text-gray-900 outfit">{{ $stats['total_orders'] }}</p>
                    </div>
                    <div class="absolute bottom-4 right-4 w-24 h-12">
                        <canvas class="sparkline" data-color="#6366f1" data-sparkline='@json($stats["sparklines"]["total_orders"] ?? $stats["chart_data"]["bar"]["data"] ?? [])'></canvas>
                    </div>
                </a>

                <!-- Pending Orders -->
                <a href="{{ route('owner.orders', ['status' => 'pending']) }}" class="block bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 group hover:shadow-xl hover:shadow-amber-500/10 transition-all text-left cursor-pointer relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-xs font-black px-2 py-1 bg-amber-50 text-amber-600 rounded-lg">Active</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400">Pending</p>
                        <p class="text-2xl lg:text-3xl font-black text-gray-900 outfit">{{ $stats['pending_orders'] }}</p>
                    </div>
                    <div class="absolute bottom-4 right-4 w-24 h-12">
                        <canvas class="sparkline" data-color="#f59e0b" data-sparkline='@json($stats["sparklines"]["pending_orders"] ?? $stats["chart_data"]["bar"]["data"] ?? [])'></canvas>
                    </div>
                </a>

                <!-- Completed Orders -->
                <a href="{{ route('owner.orders', ['status' => 'out_for_delivery']) }}" class="block bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 group hover:shadow-xl hover:shadow-blue-500/10 transition-all text-left cursor-pointer relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-xs font-black px-2 py-1 bg-blue-50 text-blue-600 rounded-lg">Done</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400">Completed</p>
                        <p class="text-2xl lg:text-3xl font-black text-gray-900 outfit">{{ $stats['completed_orders'] }}</p>
                    </div>
                    <div class="absolute bottom-4 right-4 w-24 h-12">
                        <canvas class="sparkline" data-color="#3b82f6" data-sparkline='@json($stats["sparklines"]["completed_orders"] ?? $stats["chart_data"]["bar"]["data"] ?? [])'></canvas>
                    </div>
                </a>
            </div>

            <!-- Charts & Popular Items Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                <!-- Bar Chart -->
                <div class="lg:col-span-2 bg-white rounded-[2.5rem] border border-gray-100 shadow-xl p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <h3 class="text-xl font-black outfit text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-8 bg-indigo-500 rounded-full"></span>
                            <span id="barChartTitle">{{ $stats['chart_data']['bar']['title'] ?? 'Weekly Orders' }}</span>
                        </h3>

                        <div class="inline-flex bg-gray-50 border border-gray-100 rounded-2xl p-1">
                            <button type="button" data-period="day" class="chart-period-btn px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all text-gray-500 hover:text-emerald-600 hover:bg-white">
                                Daily
                            </button>
                            <button type="button" data-period="week" class="chart-period-btn px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all bg-white text-emerald-600 shadow-sm">
                                Weekly
                            </button>
                            <button type="button" data-period="month" class="chart-period-btn px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all text-gray-500 hover:text-emerald-600 hover:bg-white">
                                Monthly
                            </button>
                        </div>
                    </div>

                    <div class="h-64 relative" data-chart-url="{{ route('owner.dashboard.chart-data') }}">
                        <div id="barChartLoader" class="absolute inset-0 flex items-center justify-center bg-white/60 backdrop-blur-sm rounded-2xl opacity-0 pointer-events-none transition-opacity">
                            <div class="w-10 h-10 rounded-full border-4 border-emerald-400 border-t-transparent animate-spin shadow-lg shadow-emerald-500/30"></div>
                        </div>
                        <canvas id="barChart"></canvas>
                    </div>
                </div>

                <!-- Popular Items -->
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-xl p-8">
                    <h3 class="text-xl font-black outfit text-gray-900 mb-6 flex items-center gap-2">
                        <span class="w-2 h-8 bg-emerald-500 rounded-full"></span>
                        Top Selling Items
                    </h3>
                    <div class="space-y-4">
                        @forelse($stats['top_items'] ?? [] as $item)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl border border-transparent hover:border-emerald-200 transition-all group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-emerald-500 font-bold">
                                        {{ $loop->iteration }}
                                    </div>
                                    <p class="font-bold text-gray-900 truncate max-w-[120px]">{{ $item->name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-black text-emerald-600">{{ $item->total_quantity }} sold</p>
                                </div>
                            </div>
                        @empty
                            <div class="py-10 text-center">
                                <p class="text-gray-400 font-medium">No sales data yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="lg:col-span-3 bg-white rounded-[2.5rem] border border-gray-100 shadow-xl p-8">
                    <h3 class="text-xl font-black outfit text-gray-900 mb-6 flex items-center gap-2">
                        <span class="w-2 h-8 bg-pink-500 rounded-full"></span>
                        Order Status Breakdown
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Orders Management Link -->
            <div class="mb-16 bg-white rounded-3xl border border-gray-100 shadow-sm p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-black outfit text-gray-900 flex items-center gap-3">
                        <span class="w-2 h-8 bg-emerald-500 rounded-full"></span>
                        Incoming Orders
                    </h3>
                    <p class="text-gray-500 font-medium mt-1">Manage and track your restaurant's orders in real-time.</p>
                </div>
                <a href="{{ route('owner.orders') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95 w-full sm:w-auto">
                    View Orders
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>

            <!-- Categories & Items List -->
            <div class="space-y-8">
                @foreach($restaurant->menuCategories as $category)
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ open: true }">
                        <div class="w-full flex items-center justify-between p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-4 cursor-pointer" @click="open = !open">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-500/20">
                                    {{ substr($category->name, 0, 1) }}
                                </div>
                                <div class="text-left">
                                    <h3 class="text-xl font-black outfit text-gray-900">{{ $category->name }}</h3>
                                    <p class="text-sm font-bold text-gray-400">{{ $category->menuItems->count() }} items</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <form method="POST" action="{{ route('owner.category.toggle-visibility', $category) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 transition-colors {{ $category->is_visible ? 'text-emerald-500 hover:text-emerald-600 bg-emerald-50 rounded-lg' : 'text-gray-400 hover:text-gray-600' }}" title="{{ $category->is_visible ? 'Visible on menu' : 'Hidden from menu' }}">
                                        @if($category->is_visible)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                        @endif
                                    </button>
                                </form>
                                <button @click="editingCategory = { id: {{ $category->id }}, name: '{{ addslashes($category->name) }}' }; showEditCategoryModal = true" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors" title="Edit Category">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form method="POST" action="{{ route('owner.category.destroy', $category) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this category and all its items?')" class="p-2 text-gray-400 hover:text-red-600 transition-colors" title="Delete Category">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                                <svg @click="open = !open" :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        
                        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4">
                            <div class="p-6 pt-0">
                                @forelse($category->menuItems as $item)
                                    <div class="flex items-center gap-4 py-4 {{ !$loop->last ? 'border-b border-gray-100' : '' }} group">
                                        <div class="w-16 h-16 bg-gray-100 rounded-xl flex-shrink-0 overflow-hidden">
                                            @if($item->image)
                                                <img src="{{ Storage::url($item->image) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-gray-900 truncate">{{ $item->name }}</h4>
                                            <p class="text-sm text-gray-500 font-medium truncate">{{ $item->description ?? 'No description' }}</p>
                                        </div>
                                        
                                        <div class="text-right flex-shrink-0">
                                            <span class="font-black text-emerald-500 text-lg">${{ number_format($item->price, 2) }}</span>
                                        </div>
                                        
                                        <div class="hidden sm:flex items-center gap-2 flex-shrink-0">
                                            <button @click="editingMenuItem = { 
                                                id: {{ $item->id }}, 
                                                name: '{{ addslashes($item->name) }}', 
                                                description: '{{ addslashes($item->description ?? '') }}', 
                                                price: {{ $item->price }}, 
                                                category_id: {{ $item->menu_category_id }},
                                                image_url: '{{ $item->image ? Storage::url($item->image) : '' }}',
                                                variants: {{ Js::from($item->variants) }},
                                                variant_type: {{ Js::from($item->variants['type'] ?? '') }}
                                            }; showEditMenuItemModal = true" title="Edit item" class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-400 hover:bg-indigo-100 hover:text-indigo-600 flex items-center justify-center transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>

                                            <form method="POST" action="{{ route('owner.menu-item.toggle', $item) }}">
                                                @csrf
                                                <button type="submit" title="{{ $item->is_available ? 'Mark unavailable' : 'Mark available' }}" class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors {{ $item->is_available ? 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200' : 'bg-gray-100 text-gray-400 hover:bg-gray-200' }}">
                                                    @if($item->is_available)
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    @else
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                    @endif
                                                </button>
                                            </form>
                                            
                                            <div x-data="{ 
                                                isFeatured: {{ $item->is_featured ? 'true' : 'false' }},
                                                loading: false,
                                                async toggleFeatured() {
                                                    if (this.loading) return;
                                                    this.loading = true;
                                                    try {
                                                        const res = await fetch('{{ route('owner.menu-item.toggle-featured', $item) }}', {
                                                            method: 'POST',
                                                            headers: {
                                                                'X-Requested-With': 'XMLHttpRequest',
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                            }
                                                        });
                                                        const data = await res.json();
                                                        if (data.success) {
                                                            this.isFeatured = data.is_featured;
                                                            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message } }));
                                                        }
                                                    } catch (e) {
                                                        console.error('Featured toggle failed', e);
                                                    } finally {
                                                        this.loading = false;
                                                    }
                                                }
                                            }">
                                                <button type="button" @click="toggleFeatured()" :disabled="loading" :title="isFeatured ? 'Remove featured' : 'Make featured'" class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors disabled:opacity-50" :class="isFeatured ? 'bg-amber-100 text-amber-500 hover:bg-amber-200' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'">
                                                    <svg class="w-5 h-5" :fill="isFeatured ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                                </button>
                                            </div>
                                            
                                            <form method="POST" action="{{ route('owner.menu-item.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Delete this item?')" title="Delete item" class="w-10 h-10 rounded-xl bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Mobile Actions Dropdown -->
                                        <div class="sm:hidden flex items-center flex-shrink-0" x-data="{ open: false }">
                                            <button type="button" @click="open = !open" @click.away="open = false" class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 flex items-center justify-center transition-colors" title="More actions" aria-label="More actions">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                            </button>

                                            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-1" class="absolute right-4 mt-12 w-48 bg-white border border-gray-100 rounded-2xl shadow-2xl overflow-hidden z-20">
                                                <button type="button" @click="editingMenuItem = { 
                                                    id: {{ $item->id }}, 
                                                    name: '{{ addslashes($item->name) }}', 
                                                    description: '{{ addslashes($item->description ?? '') }}', 
                                                    price: {{ $item->price }}, 
                                                    category_id: {{ $item->menu_category_id }},
                                                    image_url: '{{ $item->image ? Storage::url($item->image) : '' }}',
                                                    variants: {{ Js::from($item->variants) }},
                                                    variant_type: {{ Js::from($item->variants['type'] ?? '') }}
                                                }; showEditMenuItemModal = true; open = false" class="w-full px-4 py-3 text-left text-sm font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    Edit Item
                                                </button>

                                                <form method="POST" action="{{ route('owner.menu-item.toggle', $item) }}" class="w-full">
                                                    @csrf
                                                    <button type="submit" @click="open = false" class="w-full px-4 py-3 text-left text-sm font-bold text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center gap-2">
                                                        @if($item->is_available)
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                            Mark Unavailable
                                                        @else
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                            Mark Available
                                                        @endif
                                                    </button>
                                                </form>

                                                <div class="px-4 py-3 text-left text-sm font-bold text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors flex items-center gap-2 cursor-pointer"
                                                     x-data="{ 
                                                        isFeatured: {{ $item->is_featured ? 'true' : 'false' }},
                                                        loading: false,
                                                        async toggleFeatured() {
                                                            if (this.loading) return;
                                                            this.loading = true;
                                                            try {
                                                                const res = await fetch('{{ route('owner.menu-item.toggle-featured', $item) }}', {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'X-Requested-With': 'XMLHttpRequest',
                                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                                    }
                                                                });
                                                                const data = await res.json();
                                                                if (data.success) {
                                                                    this.isFeatured = data.is_featured;
                                                                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message } }));
                                                                    open = false;
                                                                }
                                                            } catch (e) {
                                                                console.error('Featured toggle failed', e);
                                                            } finally {
                                                                this.loading = false;
                                                            }
                                                        }
                                                     }"
                                                     @click="toggleFeatured()">
                                                    <svg class="w-4 h-4" :fill="isFeatured ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                                    <span x-text="isFeatured ? 'Remove Featured' : 'Mark Featured'"></span>
                                                </div>

                                                <form method="POST" action="{{ route('owner.menu-item.destroy', $item) }}" class="w-full">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Delete this item?')" @click="open = false" class="w-full px-4 py-3 text-left text-sm font-bold text-red-500 hover:bg-red-50 transition-colors flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        Delete Item
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-10 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200 mt-2">
                                        <p class="text-gray-500 font-medium mb-3">No items in this category yet.</p>
                                        <button @click="selectedCategoryId = {{ $category->id }}; showMenuItemModal = true" class="text-sm font-bold text-indigo-600 hover:text-indigo-500 transition-colors">
                                            + Add the first item
                                        </button>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Promotions Section -->
            <div class="mt-12 bg-white rounded-[2.5rem] border border-gray-100 shadow-xl p-8 mb-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-black outfit text-gray-900 flex items-center gap-3">
                        <span class="w-2 h-10 bg-amber-500 rounded-full"></span>
                        Promotions & Discounts
                    </h3>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1">
                        <div class="bg-amber-50 rounded-3xl p-6 border border-amber-100">
                            <h4 class="text-lg font-black text-amber-900 mb-4">Create Promo Code</h4>
                            <form method="POST" action="{{ route('owner.promotion.store') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-amber-800 uppercase tracking-widest mb-1">Code</label>
                                    <input type="text" name="code" placeholder="e.g. SUMMER10" class="w-full bg-white border-0 rounded-xl focus:ring-amber-500 font-bold uppercase" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-amber-800 uppercase tracking-widest mb-1">Discount %</label>
                                    <input type="number" name="discount_percentage" min="1" max="100" placeholder="10" class="w-full bg-white border-0 rounded-xl focus:ring-amber-500 font-bold" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-amber-800 uppercase tracking-widest mb-1">Valid Until (Optional)</label>
                                    <input type="date" name="valid_until" class="w-full bg-white border-0 rounded-xl focus:ring-amber-500 font-bold text-gray-600">
                                </div>
                                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-white font-black py-3 rounded-xl shadow-lg shadow-amber-500/20 transition-all active:scale-95">Add Promo</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-4">
                        <h4 class="text-lg font-black text-gray-900 mb-4">Active Promotions</h4>
                        @forelse($restaurant->promotions as $promo)
                            <div class="bg-white border border-gray-100 rounded-2xl p-5 flex items-center justify-between group hover:shadow-lg transition-shadow">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 bg-emerald-50 text-emerald-500 font-black text-xl rounded-2xl flex items-center justify-center border border-emerald-100 shadow-inner">
                                        {{ $promo->discount_percentage }}%
                                    </div>
                                    <div>
                                        <h5 class="font-black text-lg text-gray-900 font-mono tracking-wider">{{ $promo->code }}</h5>
                                        <p class="text-sm font-bold text-gray-400">
                                            @if($promo->valid_until)
                                                Valid until {{ $promo->valid_until->format('M d, Y') }}
                                            @else
                                                Never expires
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('owner.promotion.destroy', $promo) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition-colors" title="Delete Promo">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-10 bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                                <p class="text-gray-500 font-bold mb-1">No active promotions</p>
                                <p class="text-sm text-gray-400 font-medium">Create one to offer discounts to your customers.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ============================= --}}
    {{-- MODAL: Create / Edit Restaurant --}}
    {{-- ============================= --}}
    <div x-show="showRestaurantModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[12000] flex items-start md:items-center justify-center px-4 pt-[calc(env(safe-area-inset-top)+5rem)] md:pt-8 pb-4 bg-black/50 backdrop-blur-sm overflow-y-auto" x-cloak>
        <div @click.outside="showRestaurantModal = false" x-show="showRestaurantModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[calc(100dvh-6rem)] md:max-h-[calc(100dvh-3rem)] overflow-y-auto">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-black outfit">{{ $restaurant ? 'Edit Restaurant' : 'Create Restaurant' }}</h3>
                    <button @click="showRestaurantModal = false" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            
            <form method="POST" action="{{ route('owner.restaurant.store') }}" enctype="multipart/form-data" class="p-6 space-y-4" x-data="{
                logoPreview: '{{ $restaurant && $restaurant->logo ? Storage::url($restaurant->logo) : '' }}',
                coverPreview: '{{ $restaurant && $restaurant->cover_image ? Storage::url($restaurant->cover_image) : '' }}',
                handleLogoSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => this.logoPreview = e.target.result;
                        reader.readAsDataURL(file);
                    }
                },
                handleCoverSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => this.coverPreview = e.target.result;
                        reader.readAsDataURL(file);
                    }
                }
            }">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Restaurant Logo</label>
                        <div @click="$refs.logoInput.click()" class="relative h-32 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center cursor-pointer overflow-hidden group hover:border-indigo-500 transition-all">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoPreview">
                                <div class="text-center">
                                    <svg class="w-8 h-8 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-wider">Logo</p>
                                </div>
                            </template>
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <p class="text-white text-xs font-bold">Change</p>
                            </div>
                        </div>
                        <input type="file" name="logo" x-ref="logoInput" @change="handleLogoSelect($event)" accept="image/*" class="hidden">
                    </div>

                    <!-- Cover Upload -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Banner Image</label>
                        <div @click="$refs.coverInput.click()" class="relative h-32 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center cursor-pointer overflow-hidden group hover:border-indigo-500 transition-all">
                            <template x-if="coverPreview">
                                <img :src="coverPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!coverPreview">
                                <div class="text-center">
                                    <svg class="w-8 h-8 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-wider">Banner</p>
                                </div>
                            </template>
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <p class="text-white text-xs font-bold">Change</p>
                            </div>
                        </div>
                        <input type="file" name="cover_image" x-ref="coverInput" @change="handleCoverSelect($event)" accept="image/*" class="hidden">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Restaurant Name</label>
                    <input type="text" name="name" value="{{ $restaurant->name ?? old('name') }}" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="My Amazing Restaurant">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none" placeholder="Tell customers about your cuisine...">{{ $restaurant->description ?? old('description') }}</textarea>
                </div>
                <div x-data="{ 
                    gettingLocation: false, 
                    async useCurrentLocation() {
                        if (!navigator.geolocation) {
                            alert('Geolocation is not supported by your browser');
                            return;
                        }

                        this.gettingLocation = true;
                        navigator.geolocation.getCurrentPosition(async (position) => {
                            const lat = position.coords.latitude;
                            const lon = position.coords.longitude;
                            
                            $refs.latInput.value = lat;
                            $refs.lonInput.value = lon;

                            try {
                                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`);
                                const data = await response.json();
                                if (data && data.display_name) {
                                    $refs.addressInput.value = data.display_name;
                                }
                            } catch (error) {
                                console.error('Error fetching address:', error);
                            } finally {
                                this.gettingLocation = false;
                            }
                        }, (error) => {
                            console.error('Error getting location:', error);
                            alert('Could not get your location. Please ensure location permissions are granted.');
                            this.gettingLocation = false;
                        });
                    }
                }">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-sm font-bold text-gray-700">Address</label>
                        <button type="button" @click="useCurrentLocation()" :disabled="gettingLocation" class="text-xs font-black text-indigo-600 hover:text-indigo-500 flex items-center gap-1 transition-all">
                            <svg x-show="!gettingLocation" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <svg x-show="gettingLocation" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span x-text="gettingLocation ? 'Locating...' : 'Use Current Location'"></span>
                        </button>
                    </div>
                    <input type="text" name="address" x-ref="addressInput" value="{{ $restaurant->address ?? old('address') }}" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="Hamra Street, Beirut">
                    <input type="hidden" name="latitude" x-ref="latInput" value="{{ $restaurant->latitude ?? old('latitude') }}">
                    <input type="hidden" name="longitude" x-ref="lonInput" value="{{ $restaurant->longitude ?? old('longitude') }}">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Phone</label>
                    <input type="text" name="phone" value="{{ $restaurant->phone ?? old('phone') }}" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="+961 1 234 567">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Operating Hours</label>
                    <div class="space-y-3 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        @php
                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            $hours = $restaurant->operating_hours ?? [];
                        @endphp
                        @foreach($days as $day)
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" x-data="{ closed: {{ ($hours[$day]['closed'] ?? false) ? 'true' : 'false' }} }">
                                <span class="w-full sm:w-20 text-xs font-black uppercase tracking-widest text-gray-500">{{ $day }}</span>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:flex-1 min-w-0">
                                    <input type="time" name="operating_hours[{{ $day }}][open]" value="{{ $hours[$day]['open'] ?? '08:00' }}" :disabled="closed" class="w-full sm:flex-1 text-xs border-0 bg-white rounded-lg focus:ring-indigo-500 disabled:opacity-40 font-bold">
                                    <span class="text-gray-300 font-black hidden sm:inline">/</span>
                                    <input type="time" name="operating_hours[{{ $day }}][close]" value="{{ $hours[$day]['close'] ?? '22:00' }}" :disabled="closed" class="w-full sm:flex-1 text-xs border-0 bg-white rounded-lg focus:ring-indigo-500 disabled:opacity-40 font-bold">
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer group self-start sm:self-auto">
                                    <input type="hidden" name="operating_hours[{{ $day }}][closed]" value="0">
                                    <input type="checkbox" name="operating_hours[{{ $day }}][closed]" value="1" x-model="closed" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-[10px] font-black uppercase tracking-tighter text-gray-400 group-hover:text-gray-600">Closed</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_open" value="1" {{ ($restaurant->is_open ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                    <span class="text-sm font-bold text-gray-700">Open for business</span>
                </div>
                
                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-[0.98] mt-4">
                    {{ $restaurant ? 'Update Restaurant' : 'Create Restaurant' }}
                </button>
            </form>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- MODAL: Add Category --}}
    {{-- ============================= --}}
    <div x-show="showCategoryModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[12000] flex items-start md:items-center justify-center px-4 pt-[calc(env(safe-area-inset-top)+5rem)] md:pt-8 pb-4 bg-black/50 backdrop-blur-sm overflow-y-auto" x-cloak>
        <div @click.outside="showCategoryModal = false" x-show="showCategoryModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-white rounded-3xl shadow-2xl w-full max-w-md max-h-[calc(100dvh-6rem)] md:max-h-[calc(100dvh-3rem)] overflow-y-auto">
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-black outfit">Add Category</h3>
                    <button @click="showCategoryModal = false" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            
            <form method="POST" action="{{ route('owner.category.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Category Name</label>
                    <input type="text" name="name" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="e.g. Main Dishes, Drinks, Desserts">
                </div>
                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-[0.98]">
                    Add Category
                </button>
            </form>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- MODAL: Add Menu Item --}}
    {{-- ============================= --}}
    <div x-show="showMenuItemModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[12000] flex items-start md:items-center justify-center px-4 pt-[calc(env(safe-area-inset-top)+5rem)] md:pt-8 pb-4 bg-black/50 backdrop-blur-sm overflow-y-auto" x-cloak>
        <div @click.outside="showMenuItemModal = false" x-show="showMenuItemModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[calc(100dvh-6rem)] md:max-h-[calc(100dvh-3rem)] overflow-y-auto">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-black outfit">Add Menu Item</h3>
                    <button @click="showMenuItemModal = false" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            
            <form method="POST" action="{{ route('owner.menu-item.store') }}" enctype="multipart/form-data" class="p-6 space-y-4" x-data="{
                imagePreview: null,
                hasVariants: false,
                variants: [{ name: '', price: '' }],
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => this.imagePreview = e.target.result;
                        reader.readAsDataURL(file);
                    }
                },
                handleDrop(event) {
                    event.preventDefault();
                    const file = event.dataTransfer.files[0];
                    if (file && file.type.startsWith('image/')) {
                        this.$refs.imageInput.files = event.dataTransfer.files;
                        const reader = new FileReader();
                        reader.onload = (e) => this.imagePreview = e.target.result;
                        reader.readAsDataURL(file);
                    }
                },
                removeImage() {
                    this.imagePreview = null;
                    this.$refs.imageInput.value = '';
                },
                addVariantRow() {
                    this.variants.push({ name: '', price: '' });
                },
                removeVariantRow(index) {
                    if (this.variants.length > 1) {
                        this.variants.splice(index, 1);
                    } else {
                        this.variants[0] = { name: '', price: '' };
                    }
                }
            }">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Category</label>
                    <select name="menu_category_id" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium focus:outline-none focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-500/10 transition-all appearance-none cursor-pointer">
                        @if($restaurant)
                            @foreach($restaurant->menuCategories as $cat)
                                <option value="{{ $cat->id }}" {{ $cat->id == old('menu_category_id') ? 'selected' : '' }} :selected="selectedCategoryId == {{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Item Name</label>
                    <input type="text" name="name" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-500/10 transition-all" placeholder="e.g. Shawarma Plate">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="2" class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-500/10 transition-all resize-none" placeholder="Describe the dish..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Price ($)</label>
                    <input type="number" step="0.01" min="0" name="price" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-500/10 transition-all" placeholder="12.99">
                </div>

                <!-- Variants Toggle -->
                <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-gray-700">Variants</p>
                        <p class="text-xs font-medium text-gray-400">Let customers choose options like size, each with its own price.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="has_variants" value="1" x-model="hasVariants" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 peer-checked:bg-emerald-500 transition-colors relative">
                            <div class="absolute top-[2px] left-[2px] w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200"
                                 :class="hasVariants ? 'translate-x-5' : 'translate-x-0'"></div>
                        </div>
                    </label>
                </div>

                <!-- Variant Configuration -->
                <div x-show="hasVariants" x-transition class="space-y-3 rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4">
                    <div>
                        <label class="block text-xs font-black text-emerald-700 mb-1.5 uppercase tracking-widest">Variant Type</label>
                        <input type="text" name="variant_type" class="block w-full px-3 py-2.5 bg-white border border-emerald-200 rounded-xl text-sm font-medium placeholder-emerald-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" placeholder="e.g. Size, Portion, Bread Type">
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-black text-emerald-700 uppercase tracking-widest">Variant Options</label>
                            <button type="button"
                                    @click="addVariantRow()"
                                    class="text-[11px] font-bold text-emerald-600 hover:text-emerald-500">
                                + Add option
                            </button>
                        </div>
                        <template x-for="(variant, index) in variants" :key="index">
                            <div class="flex items-center gap-2">
                                <input type="text"
                                       class="flex-1 px-3 py-2 bg-white border border-emerald-200 rounded-xl text-sm font-medium placeholder-emerald-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                                       placeholder="e.g. Small"
                                       x-model="variant.name"
                                       :name="`variant_names[${index}]`">
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       class="w-28 px-3 py-2 bg-white border border-emerald-200 rounded-xl text-sm font-medium placeholder-emerald-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                                       placeholder="$9.99"
                                       x-model="variant.price"
                                       :name="`variant_prices[${index}]`">
                                <button type="button"
                                        @click="removeVariantRow(index)"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <p class="mt-1 text-[11px] text-emerald-600 font-medium">
                            Each option can have its own price. Customers will pick one when ordering.
                        </p>
                    </div>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Item Image</label>
                    
                    <!-- Drop Zone -->
                    <div x-show="!imagePreview"
                         @dragover.prevent="$el.classList.add('border-amber-500', 'bg-amber-50')"
                         @dragleave.prevent="$el.classList.remove('border-amber-500', 'bg-amber-50')"
                         @drop="handleDrop($event); $el.classList.remove('border-amber-500', 'bg-amber-50')"
                         @click="$refs.imageInput.click()"
                         class="relative border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center cursor-pointer hover:border-amber-500 hover:bg-amber-50/50 transition-all group">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-700">Drop an image here or <span class="text-amber-500">browse</span></p>
                                <p class="text-xs text-gray-400 font-medium mt-1">PNG, JPG, WEBP up to 2MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Image Preview -->
                    <div x-show="imagePreview" x-cloak class="relative rounded-2xl overflow-hidden border-2 border-gray-200 bg-gray-50">
                        <img :src="imagePreview" alt="Preview" class="w-full h-48 object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                        <button type="button" @click="removeImage()" class="absolute top-3 right-3 w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                        <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full">
                            <p class="text-xs font-bold text-gray-700">✓ Image ready</p>
                        </div>
                    </div>

                    <input type="file" name="image" accept="image/*" x-ref="imageInput" @change="handleFileSelect($event)" class="hidden">
                </div>

                <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 text-white font-bold rounded-2xl shadow-lg shadow-amber-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-[0.98]">
                    Add Menu Item
                </button>
            </form>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- MODAL: Edit Category --}}
    {{-- ============================= --}}
    <div x-show="showEditCategoryModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[12000] flex items-start md:items-center justify-center px-4 pt-[calc(env(safe-area-inset-top)+5rem)] md:pt-8 pb-4 bg-black/50 backdrop-blur-sm overflow-y-auto" x-cloak>
        <div @click.outside="showEditCategoryModal = false" x-show="showEditCategoryModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-white rounded-3xl shadow-2xl w-full max-w-md max-h-[calc(100dvh-6rem)] md:max-h-[calc(100dvh-3rem)] overflow-y-auto">
            <div class="bg-gradient-to-r from-indigo-700 to-blue-700 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-black outfit">Edit Category</h3>
                    <button @click="showEditCategoryModal = false" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            
            <form method="POST" :action="`{{ route('owner.category.update', 'ID') }}`.replace('ID', editingCategory.id)" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Category Name</label>
                    <input type="text" name="name" x-model="editingCategory.name" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="e.g. Main Dishes, Drinks, Desserts">
                </div>
                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-[0.98]">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- MODAL: Edit Menu Item --}}
    {{-- ============================= --}}
    <div x-show="showEditMenuItemModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[12000] flex items-start md:items-center justify-center px-4 pt-[calc(env(safe-area-inset-top)+5rem)] md:pt-8 pb-4 bg-black/50 backdrop-blur-sm overflow-y-auto" x-cloak>
        <div @click.outside="showEditMenuItemModal = false" x-show="showEditMenuItemModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[calc(100dvh-6rem)] md:max-h-[calc(100dvh-3rem)] overflow-y-auto">
            <div class="bg-gradient-to-r from-indigo-500 to-blue-500 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-black outfit">Edit Menu Item</h3>
                    <button @click="showEditMenuItemModal = false" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            
            <form method="POST" :action="`{{ route('owner.menu-item.update', 'ID') }}`.replace('ID', editingMenuItem.id)" enctype="multipart/form-data" class="p-6 space-y-4" x-data="{
                newImagePreview: null,
                editHasVariants: false,
                editVariantType: '',
                editVariants: [{ name: '', price: '' }],
                
                init() {
                    this.$watch('editingMenuItem', (val) => {
                        if (val && val.id) {
                            const v = val.variants;
                            if (v && v.options && v.options.length > 0) {
                                this.editHasVariants = true;
                                this.editVariantType = v.type || '';
                                this.editVariants = v.options.map(o => ({ name: o.label || '', price: o.price || '' }));
                            } else {
                                this.editHasVariants = false;
                                this.editVariantType = '';
                                this.editVariants = [{ name: '', price: '' }];
                            }
                        }
                    }, { immediate: true });
                },
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => this.newImagePreview = e.target.result;
                        reader.readAsDataURL(file);
                    }
                },
                removeImage() {
                    this.newImagePreview = null;
                    this.$refs.editImageInput.value = '';
                },
                addEditVariantRow() {
                    this.editVariants.push({ name: '', price: '' });
                },
                removeEditVariantRow(index) {
                    if (this.editVariants.length > 1) {
                        this.editVariants.splice(index, 1);
                    } else {
                        this.editVariants = [{ name: '', price: '' }];
                    }
                }
            }">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Item Name</label>
                    <input type="text" name="name" x-model="editingMenuItem.name" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="e.g. Shawarma Plate">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" x-model="editingMenuItem.description" rows="2" class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none" placeholder="Describe the dish..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Price ($)</label>
                    <input type="number" step="0.01" min="0" name="price" x-model="editingMenuItem.price" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="12.99">
                </div>

                <!-- Variants Toggle -->
                <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-gray-700">Variants</p>
                        <p class="text-xs font-medium text-gray-400">Edit or add variant options like size, each with its own price.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="has_variants" value="1" x-model="editHasVariants" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 peer-checked:bg-emerald-500 transition-colors relative">
                            <div class="absolute top-[2px] left-[2px] w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200"
                                 :class="editHasVariants ? 'translate-x-5' : 'translate-x-0'"></div>
                        </div>
                    </label>
                </div>

                <!-- Variant Configuration -->
                <div x-show="editHasVariants" x-transition class="space-y-3 rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4">
                    <div>
                        <label class="block text-xs font-black text-emerald-700 mb-1.5 uppercase tracking-widest">Variant Type</label>
                        <input type="text" name="variant_type" x-model="editVariantType" class="block w-full px-3 py-2.5 bg-white border border-emerald-200 rounded-xl text-sm font-medium placeholder-emerald-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" placeholder="e.g. Size, Portion, Bread Type">
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-black text-emerald-700 uppercase tracking-widest">Variant Options</label>
                            <button type="button"
                                    @click="addEditVariantRow()"
                                    class="text-[11px] font-bold text-emerald-600 hover:text-emerald-500">
                                + Add option
                            </button>
                        </div>
                        <template x-for="(variant, index) in editVariants" :key="index">
                            <div class="flex items-center gap-2">
                                <input type="text"
                                       class="flex-1 px-3 py-2 bg-white border border-emerald-200 rounded-xl text-sm font-medium placeholder-emerald-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                                       placeholder="e.g. Small"
                                       x-model="variant.name"
                                       :name="`variant_names[${index}]`">
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       class="w-28 px-3 py-2 bg-white border border-emerald-200 rounded-xl text-sm font-medium placeholder-emerald-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                                       placeholder="$9.99"
                                       x-model="variant.price"
                                       :name="`variant_prices[${index}]`">
                                <button type="button"
                                        @click="removeEditVariantRow(index)"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <p class="mt-1 text-[11px] text-emerald-600 font-medium">
                            Each option can have its own price. Customers will pick one when ordering.
                        </p>
                    </div>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Change Image (Optional)</label>
                    
                    <div class="flex gap-4 items-start">
                        <!-- Current Image -->
                        <div class="relative w-32 h-32 rounded-2xl overflow-hidden border-2 border-gray-200 bg-gray-50 flex-shrink-0" x-show="editingMenuItem.image_url && !newImagePreview">
                            <img :src="editingMenuItem.image_url" class="w-full h-full object-cover">
                            <div class="absolute inset-x-0 bottom-0 bg-black/50 text-[10px] text-white text-center py-1 font-bold">CURRENT</div>
                        </div>

                        <!-- New Preview / Drop Zone -->
                        <div class="flex-1">
                            <div x-show="!newImagePreview"
                                 @click="$refs.editImageInput.click()"
                                 class="relative border-2 border-dashed border-gray-300 rounded-2xl p-4 text-center cursor-pointer hover:border-indigo-500 hover:bg-indigo-50/50 transition-all group h-32 flex flex-col items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400 mb-1 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p class="text-[10px] font-bold text-gray-500">New Image</p>
                            </div>

                            <div x-show="newImagePreview" x-cloak class="relative rounded-2xl overflow-hidden border-2 border-indigo-500 bg-gray-50 h-32">
                                <img :src="newImagePreview" alt="Preview" class="w-full h-full object-cover">
                                <button type="button" @click="removeImage()" class="absolute top-1 right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <div class="absolute inset-x-0 bottom-0 bg-indigo-500 text-[10px] text-white text-center py-1 font-bold">NEW READY</div>
                            </div>
                        </div>
                    </div>

                    <input type="file" name="image" accept="image/*" x-ref="editImageInput" @change="handleFileSelect($event)" class="hidden">
                </div>

                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-[0.98]">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
    <script>
        (function() {
            const stats = @json($stats);

            function initDashboard() {
                // Sparklines for metric cards
                const sparkCanvases = Array.from(document.querySelectorAll('.sparkline'));
                sparkCanvases.forEach((canvas) => {
                    const raw = canvas.getAttribute('data-sparkline');
                    const color = canvas.getAttribute('data-color') || '#10b981';
                    let series = [];
                    try {
                        series = raw ? JSON.parse(raw) : [];
                    } catch (e) {
                        series = [];
                    }

                    if (!Array.isArray(series) || series.length === 0) return;

                    const ctx = canvas.getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: series.map((_, i) => i + 1),
                            datasets: [{
                                data: series,
                                borderColor: color,
                                backgroundColor: color + '22',
                                borderWidth: 2,
                                pointRadius: 0,
                                tension: 0.35,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { enabled: false }
                            },
                            scales: {
                                x: { display: false },
                                y: { display: false }
                            },
                            elements: {
                                line: { capBezierPoints: true }
                            }
                        }
                    });
                });

                // Bar Chart
                const barCanvas = document.getElementById('barChart');
                if (barCanvas) {
                    const barCtx = barCanvas.getContext('2d');
                    const barChart = new Chart(barCtx, {
                        type: 'bar',
                        data: {
                            labels: stats.chart_data.bar.labels,
                            datasets: [{
                                label: 'Orders',
                                data: stats.chart_data.bar.data,
                                backgroundColor: 'rgba(79, 70, 229, 0.2)',
                                borderColor: 'rgb(79, 70, 229)',
                                borderWidth: 2,
                                borderRadius: 8,
                                barThickness: 20
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 }
                                }
                            }
                        }
                    });

                    // Dynamic period filters
                    const chartWrap = barCanvas.closest('[data-chart-url]');
                    const chartUrl = chartWrap?.getAttribute('data-chart-url');
                    const titleEl = document.getElementById('barChartTitle');
                    const loaderEl = document.getElementById('barChartLoader');
                    const buttons = Array.from(document.querySelectorAll('.chart-period-btn'));

                    const setActiveBtn = (period) => {
                        buttons.forEach((b) => {
                            const isActive = b.getAttribute('data-period') === period;
                            b.classList.toggle('bg-white', isActive);
                            b.classList.toggle('text-emerald-600', isActive);
                            b.classList.toggle('shadow-sm', isActive);
                            b.classList.toggle('text-gray-500', !isActive);
                        });
                    };

                    const setLoading = (isLoading) => {
                        if (!loaderEl) return;
                        loaderEl.classList.toggle('opacity-0', !isLoading);
                        loaderEl.classList.toggle('pointer-events-none', !isLoading);
                    };

                    const updateBarChart = async (period) => {
                        if (!chartUrl) return;
                        setActiveBtn(period);
                        setLoading(true);
                        try {
                            const res = await fetch(`${chartUrl}?period=${encodeURIComponent(period)}`, {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await res.json();
                            if (!res.ok) throw new Error(data?.message || 'Failed to load chart data');

                            if (titleEl && data.title) titleEl.textContent = data.title;
                            barChart.data.labels = data.labels || [];
                            barChart.data.datasets[0].data = data.data || [];
                            barChart.update();
                        } catch (e) {
                            console.error('Chart update failed', e);
                        } finally {
                            setLoading(false);
                        }
                    };

                    buttons.forEach((btn) => {
                        btn.addEventListener('click', () => {
                            updateBarChart(btn.getAttribute('data-period') || 'week');
                        });
                    });
                }

                // Pie Chart
                const pieCanvas = document.getElementById('pieChart');
                if (pieCanvas) {
                    const pieCtx = pieCanvas.getContext('2d');
                    new Chart(pieCtx, {
                        type: 'doughnut',
                        data: {
                            labels: stats.chart_data.pie.labels,
                            datasets: [{
                                data: stats.chart_data.pie.data,
                                backgroundColor: [
                                    '#fbbf24', // amber
                                    '#10b981', // emerald
                                    '#ef4444', // red
                                    '#3b82f6', // blue
                                    '#8b5cf6'  // violet
                                ],
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: { family: 'Outfit', weight: 'bold' }
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // Run immediately for Swup transitions
            initDashboard();
        })();
    </script>
@endsection
