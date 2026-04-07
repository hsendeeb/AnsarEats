@extends('layouts.app')

@section('content')
@php
    $restaurantDraft = $restaurant ?? $latestRequest ?? $pendingRequest ?? null;
@endphp
<div class="min-h-screen bg-gray-50 py-10 px-4 relative" x-data="{ 
    showRestaurantModal: false, 
    showCategoryModal: false,
    showEditCategoryModal: false,
    showMenuItemModal: false,
    showEditMenuItemModal: false,
    selectedCategoryId: null,
    editingCategory: { id: null, name: '' },
    editingMenuItem: { id: null, name: '', description: '', price: '', category_id: null, image_url: '', variants: null, variant_type: '', is_on_sale: false, sale_price: '', discount_percentage: '' },
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
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 rounded-[2rem] p-8 md:p-12 mb-10 text-white relative overflow-hidden">
            <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full"></div>
            <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-white/10 rounded-full"></div>
            <div class="absolute top-8 right-8 w-12 h-12 bg-white/20 rounded-xl rotate-12 animate-bounce" style="animation-duration: 3s;"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-4xl md:text-5xl font-black outfit tracking-tight leading-tight">
                        {{ $restaurant?->name ?? $latestRequest?->restaurant_name ?? Auth::user()->name }}
                    </h1>
                    <p class="mt-3 text-purple-200 font-medium text-lg max-w-lg">
                        {{ $restaurant ? 'Manage your restaurant, categories, and menu items from this dashboard.' : 'Submit your restaurant details for review and come back here to track the decision.' }}
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
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                        {{ $restaurant ? 'Edit Restaurant' : ($pendingRequest ? 'Edit Request' : 'Create Restaurant') }}
                    </button>
                </div>
            </div>
        </div>

        @if(!$restaurant)
            <!-- Empty State -->
            <div class="bg-white rounded-[2rem] border border-gray-100 p-16 text-center">
                @if($latestRequest?->status === 'pending')
                    <div class="max-w-xl mx-auto mb-8 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-800 font-bold text-sm">
                        Your registration request is pending super admin approval.
                    </div>
                @elseif($latestRequest?->status === 'rejected')
                    <div class="max-w-xl mx-auto mb-8 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 text-sm">
                        <div class="font-bold">Your last restaurant request was rejected.</div>
                        <div class="mt-1 font-medium">{{ $latestRequest->rejection_reason ?: 'No rejection reason was provided.' }}</div>
                    </div>
                @endif
                <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-purple-100 text-purple-500 mb-8 animate-bounce" style="animation-duration: 2s;">
                    <x-heroicon-o-building-storefront class="w-14 h-14" />
                </div>
                <h3 class="text-3xl font-black outfit text-gray-900 mb-3">Start Your Journey</h3>
                <p class="text-gray-500 text-lg font-medium max-w-md mx-auto mb-8">
                    {{ $latestRequest?->status === 'pending' ? 'Update your request details while you wait for approval.' : ($latestRequest?->status === 'rejected' ? 'Adjust your restaurant details and submit again for another review.' : 'Create your restaurant profile first. Then you can add categories and menu items.') }}
                </p>
                <button @click="showRestaurantModal = true" class="inline-flex items-center gap-2 px-8 py-4 bg-gray-900 hover:bg-purple-600 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl hover:shadow-purple-500/30 transition-all transform hover:-translate-y-0.5 active:scale-95 text-lg">
                    <x-heroicon-o-plus class="w-6 h-6" />
                    {{ $latestRequest ? 'Update Request' : 'Create Your Restaurant' }}
                </button>
            </div>
        @else
            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 mb-10">
                <button @click="showCategoryModal = true" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-95">
                    <x-heroicon-o-plus class="w-5 h-5" />
                    Add Category
                </button>
                
                @if($restaurant->menuCategories->count() > 0)
                <button @click="showMenuItemModal = true" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-400 text-white font-bold rounded-2xl shadow-lg shadow-amber-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-95">
                    <x-heroicon-o-plus class="w-5 h-5" />
                    Add Menu Item
                </button>
                @endif
                
                <a href="{{ route('restaurant.show', $restaurant) }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white border-2 border-gray-200 hover:border-emerald-500 text-gray-700 hover:text-emerald-600 font-bold rounded-2xl transition-all transform hover:-translate-y-0.5 active:scale-95">
                    <x-heroicon-o-arrow-top-right-on-square class="w-5 h-5" />
                    View Public Page
                </a>
            </div>

            <div id="dashboard-live-sections">
            <!-- Analytics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-10">
                <!-- Total Revenue -->
                <div class="bg-white rounded-[2rem] border border-gray-100 p-6 group transition-all relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                            <x-heroicon-o-banknotes class="w-6 h-6" />
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
                <div class="bg-white rounded-[2rem] border border-gray-100 p-6 group transition-all relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-teal-100 rounded-2xl flex items-center justify-center text-teal-500 group-hover:scale-110 transition-transform">
                            <x-heroicon-o-calculator class="w-6 h-6" />
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
                <a href="{{ route('owner.orders') }}" class="block bg-white rounded-[2rem] border border-gray-100 p-6 group transition-all text-left cursor-pointer relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform">
                            <x-heroicon-o-shopping-bag class="w-6 h-6" />
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
                <a href="{{ route('owner.orders', ['status' => 'pending']) }}" class="block bg-white rounded-[2rem] border border-gray-100 p-6 group transition-all text-left cursor-pointer relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                            <x-heroicon-o-clock class="w-6 h-6" />
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

                <!-- Delivered Orders -->
                <a href="{{ route('owner.orders', ['status' => 'delivered']) }}" class="block bg-white rounded-[2rem] border border-gray-100 p-6 group transition-all text-left cursor-pointer relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                            <x-heroicon-o-check-circle class="w-6 h-6" />
                        </div>
                        <span class="text-xs font-black px-2 py-1 bg-blue-50 text-blue-600 rounded-lg">Delivered</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400">Delivered</p>
                        <p class="text-2xl lg:text-3xl font-black text-gray-900 outfit">{{ $stats['delivered_orders'] }}</p>
                    </div>
                    <div class="absolute bottom-4 right-4 w-24 h-12">
                        <canvas class="sparkline" data-color="#3b82f6" data-sparkline='@json($stats["sparklines"]["delivered_orders"] ?? $stats["chart_data"]["bar"]["data"] ?? [])'></canvas>
                    </div>
                </a>

                <!-- Customers -->
                <a href="{{ route('owner.customers') }}" class="block bg-white rounded-[2rem] border border-gray-100 p-6 group transition-all text-left cursor-pointer relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center text-rose-500 group-hover:scale-110 transition-transform">
                            <x-heroicon-o-user-group class="w-6 h-6" />
                        </div>
                        <span class="text-xs font-black px-2 py-1 bg-rose-50 text-rose-600 rounded-lg">People</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400">Customers</p>
                        <p class="text-2xl lg:text-3xl font-black text-gray-900 outfit">{{ $stats['customers_count'] }}</p>
                    </div>
                    <div class="absolute bottom-4 right-4 w-24 h-12">
                        <canvas class="sparkline" data-color="#f43f5e" data-sparkline='@json($stats["sparklines"]["customers"] ?? $stats["chart_data"]["bar"]["data"] ?? [])'></canvas>
                    </div>
                </a>
            </div>

            <!-- Charts & Popular Items Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                <!-- Bar Chart -->
                <div class="lg:col-span-2 bg-white rounded-[2.5rem] border border-gray-100 p-8">
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
                <div class="bg-white rounded-[2.5rem] border border-gray-100 p-8">
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
                <div class="lg:col-span-3 bg-white rounded-[2.5rem] border border-gray-100 p-8">
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
            <div class="mb-16 bg-white rounded-3xl border border-gray-100 p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-black outfit text-gray-900 flex items-center gap-3">
                        <span class="w-2 h-8 bg-emerald-500 rounded-full"></span>
                        Incoming Orders
                    </h3>
                    <p class="text-gray-500 font-medium mt-1">Manage and track your restaurant's orders in real-time.</p>
                </div>
                <a href="{{ route('owner.orders') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95 w-full sm:w-auto">
                    View Orders
                    <x-heroicon-o-arrow-right class="w-5 h-5" />
                </a>
            </div>
            </div>

            <!-- Categories & Items List -->
            <div class="space-y-8">
                @foreach($restaurant->menuCategories as $category)
                    <div class="relative bg-white rounded-3xl border border-gray-100 overflow-visible" :class="actionsOpen ? 'z-[110]' : 'z-0'" x-data="{ open: true, actionsOpen: false }">
                        <div class="relative z-10 w-full flex items-center justify-between p-6 hover:bg-gray-50 transition-colors">
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
                                <div class="relative" @click.away="actionsOpen = false">
                                    <button
                                        type="button"
                                        @click.stop="actionsOpen = !actionsOpen"
                                        class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 flex items-center justify-center transition-colors"
                                        title="More category actions"
                                        aria-label="More category actions"
                                    >
                                        <x-heroicon-s-ellipsis-horizontal class="w-5 h-5" />
                                    </button>

                                    <div
                                        x-show="actionsOpen"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                                        class="absolute right-0 top-full mt-2 w-52 bg-white border border-gray-100 rounded-2xl shadow-2xl overflow-hidden z-[120]"
                                        x-cloak
                                    >
                                        <form method="POST" action="{{ route('owner.category.toggle-visibility', $category) }}" class="w-full">
                                            @csrf
                                            <button type="submit" @click="actionsOpen = false" class="w-full px-4 py-3 text-left text-sm font-bold text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center gap-2">
                                                @if($category->is_visible)
                                                    <x-heroicon-o-eye-slash class="w-4 h-4" />
                                                    Hide Category
                                                @else
                                                    <x-heroicon-o-eye class="w-4 h-4" />
                                                    Show Category
                                                @endif
                                            </button>
                                        </form>

                                        <button type="button" @click="editingCategory = { id: {{ $category->id }}, name: '{{ addslashes($category->name) }}' }; showEditCategoryModal = true; actionsOpen = false" class="w-full px-4 py-3 text-left text-sm font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors flex items-center gap-2">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                            Edit Category
                                        </button>

                                        <form method="POST" action="{{ route('owner.category.destroy', $category) }}" class="w-full">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Delete this category and all its items?')" @click="actionsOpen = false" class="w-full px-4 py-3 text-left text-sm font-bold text-red-500 hover:bg-red-50 transition-colors flex items-center gap-2">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                Delete Category
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <button type="button" @click="open = !open" class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 flex items-center justify-center transition-colors" title="Toggle category">
                                    <x-heroicon-o-chevron-down :class="open ? 'rotate-180' : ''" class="w-5 h-5 transition-transform" />
                                </button>
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
                                                    <x-heroicon-o-photo class="w-6 h-6" />
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-gray-900 truncate">{{ $item->name }}</h4>
                                            <p class="text-sm text-gray-500 font-medium truncate">{{ $item->description ?? 'No description' }}</p>
                                        </div>
                                        
                                        <div class="text-right flex-shrink-0">
                                            @if($item->isSaleActive())
                                                <div class="flex flex-col items-end">
                                                    <span class="text-sm font-bold text-gray-400 line-through">${{ number_format($item->price, 2) }}</span>
                                                    <span class="font-black text-emerald-500 text-lg">${{ number_format($item->effectivePrice(), 2) }}</span>
                                                    @if(!empty(data_get($item->variants, 'options', [])))
                                                        <span class="text-[10px] font-bold text-emerald-500">
                                                            {{ rtrim(rtrim(number_format($item->saleDiscountPercentage() ?? 0, 2), '0'), '.') }}% off variants
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="font-black text-emerald-500 text-lg">${{ number_format($item->price, 2) }}</span>
                                            @endif
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
                                                variant_type: {{ Js::from($item->variants['type'] ?? '') }},
                                                is_on_sale: {{ $item->is_on_sale ? 'true' : 'false' }},
                                                sale_price: {{ Js::from($item->sale_price) }},
                                                discount_percentage: {{ Js::from($item->saleDiscountPercentage()) }}
                                            }; showEditMenuItemModal = true" title="Edit item" class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-400 hover:bg-indigo-100 hover:text-indigo-600 flex items-center justify-center transition-colors">
                                                <x-heroicon-o-pencil-square class="w-5 h-5" />
                                            </button>

                                            <form method="POST" action="{{ route('owner.menu-item.toggle', $item) }}">
                                                @csrf
                                                <button type="submit" title="{{ $item->is_available ? 'Mark unavailable' : 'Mark available' }}" class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors {{ $item->is_available ? 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200' : 'bg-gray-100 text-gray-400 hover:bg-gray-200' }}">
                                                    @if($item->is_available)
                                                        <x-heroicon-o-check-circle class="w-5 h-5" />
                                                    @else
                                                        <x-heroicon-o-no-symbol class="w-5 h-5" />
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
                                                    <x-heroicon-s-star x-show="isFeatured" x-cloak class="w-5 h-5" />
                                                    <x-heroicon-o-star x-show="!isFeatured" x-cloak class="w-5 h-5" />
                                                </button>
                                            </div>
                                            
                                            <form method="POST" action="{{ route('owner.menu-item.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Delete this item?')" title="Delete item" class="w-10 h-10 rounded-xl bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Mobile Actions Dropdown -->
                                        <div class="sm:hidden flex items-center flex-shrink-0" x-data="{ open: false }">
                                            <button type="button" @click="open = !open" @click.away="open = false" class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 flex items-center justify-center transition-colors" title="More actions" aria-label="More actions">
                                                <x-heroicon-s-ellipsis-horizontal class="w-5 h-5" />
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
                                                    variant_type: {{ Js::from($item->variants['type'] ?? '') }},
                                                    is_on_sale: {{ $item->is_on_sale ? 'true' : 'false' }},
                                                    sale_price: {{ Js::from($item->sale_price) }},
                                                    discount_percentage: {{ Js::from($item->saleDiscountPercentage()) }}
                                                }; showEditMenuItemModal = true; open = false" class="w-full px-4 py-3 text-left text-sm font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors flex items-center gap-2">
                                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                    Edit Item
                                                </button>

                                                <form method="POST" action="{{ route('owner.menu-item.toggle', $item) }}" class="w-full">
                                                    @csrf
                                                    <button type="submit" @click="open = false" class="w-full px-4 py-3 text-left text-sm font-bold text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center gap-2">
                                                        @if($item->is_available)
                                                            <x-heroicon-o-check-circle class="w-4 h-4" />
                                                            Mark Unavailable
                                                        @else
                                                            <x-heroicon-o-no-symbol class="w-4 h-4" />
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
                                                    <x-heroicon-s-star x-show="isFeatured" x-cloak class="w-4 h-4" />
                                                    <x-heroicon-o-star x-show="!isFeatured" x-cloak class="w-4 h-4" />
                                                    <span x-text="isFeatured ? 'Remove Featured' : 'Mark Featured'"></span>
                                                </div>

                                                <form method="POST" action="{{ route('owner.menu-item.destroy', $item) }}" class="w-full">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Delete this item?')" @click="open = false" class="w-full px-4 py-3 text-left text-sm font-bold text-red-500 hover:bg-red-50 transition-colors flex items-center gap-2">
                                                        <x-heroicon-o-trash class="w-4 h-4" />
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
            <div class="mt-12 bg-white rounded-[2.5rem] border border-gray-100 p-8 mb-10">
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
                            <div class="bg-white border border-gray-100 rounded-2xl p-5 flex items-center justify-between group transition-shadow">
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
                                        <x-heroicon-o-trash class="w-5 h-5" />
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
                    <h3 class="text-2xl font-black outfit">{{ $restaurant ? 'Edit Restaurant' : ($latestRequest ? 'Update Registration Request' : 'Create Restaurant') }}</h3>
                    <button @click="showRestaurantModal = false" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
            </div>
            
            <form method="POST" action="{{ route('owner.restaurant.store') }}" enctype="multipart/form-data" class="p-6 space-y-4" x-data="{
                logoPreview: '{{ $restaurantDraft && $restaurantDraft->logo ? Storage::url($restaurantDraft->logo) : '' }}',
                coverPreview: '{{ $restaurantDraft && $restaurantDraft->cover_image ? Storage::url($restaurantDraft->cover_image) : '' }}',
                deliveryFeeEnabled: {{ old('free_delivery', $restaurantDraft ? (((float) (optional($restaurantDraft)->delivery_fee ?? 0) <= 0) ? 1 : 0) : 0) ? 'false' : 'true' }},
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
                                    <x-heroicon-o-photo class="w-8 h-8 text-gray-400 mx-auto" />
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
                                    <x-heroicon-o-photo class="w-8 h-8 text-gray-400 mx-auto" />
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
                    <input type="text" name="name" value="{{ old('name') ?? optional($restaurantDraft)->name ?? optional($pendingRequest)->restaurant_name ?? '' }}" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="My Amazing Restaurant">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none" placeholder="Tell customers about your cuisine...">{{ old('description') ?? optional($restaurantDraft)->description }}</textarea>
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
                            <x-heroicon-o-map-pin x-show="!gettingLocation" class="w-3.5 h-3.5" />
                            <x-heroicon-o-arrow-path x-show="gettingLocation" class="w-3.5 h-3.5 animate-spin" />
                            <span x-text="gettingLocation ? 'Locating...' : 'Use Current Location'"></span>
                        </button>
                    </div>
                    <input type="text" name="address" x-ref="addressInput" value="{{ old('address') ?? optional($restaurantDraft)->address }}" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="Hamra Street, Beirut">
                    <input type="hidden" name="latitude" x-ref="latInput" value="{{ old('latitude') ?? optional($restaurantDraft)->latitude }}">
                    <input type="hidden" name="longitude" x-ref="lonInput" value="{{ old('longitude') ?? optional($restaurantDraft)->longitude }}">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') ?? optional($restaurantDraft)->phone }}" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="+961 1 234 567">
                </div>

                <div class="space-y-3 rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-gray-700">Delivery Fee</p>
                            <p class="text-xs font-medium text-gray-400">Turn this on only if you want to charge customers for delivery.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="free_delivery" :value="deliveryFeeEnabled ? 0 : 1">
                            <input type="checkbox" x-model="deliveryFeeEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 peer-checked:bg-emerald-500 transition-colors relative">
                                <div class="absolute top-[2px] left-[2px] w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200"
                                     :class="deliveryFeeEnabled ? 'translate-x-5' : 'translate-x-0'"></div>
                            </div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-white/80 bg-white/80 px-4 py-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-emerald-700">Delivery Status</p>
                            <p class="text-[11px] font-medium text-emerald-600" x-text="deliveryFeeEnabled ? 'Customers will see this fee during checkout.' : 'Turn the switch on if you want to charge for delivery.'"></p>
                        </div>
                        <span class="text-sm font-black" :class="deliveryFeeEnabled ? 'text-emerald-600' : 'text-gray-400'" x-text="deliveryFeeEnabled ? 'On' : 'Off'"></span>
                    </div>
                    <div x-show="!deliveryFeeEnabled" x-transition class="rounded-xl border border-dashed border-emerald-200 bg-white/70 px-4 py-3">
                        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-widest">Free Delivery</p>
                        <p class="mt-1 text-xs font-medium text-gray-500">No delivery fee will be added at checkout.</p>
                    </div>
                    <div x-show="deliveryFeeEnabled" x-transition>
                        <label class="block text-xs font-black text-emerald-700 mb-1.5 uppercase tracking-widest">Fee Amount ($)</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="delivery_fee"
                               value="{{ old('delivery_fee', optional($restaurantDraft)->delivery_fee) }}"
                               :required="deliveryFeeEnabled"
                               :disabled="!deliveryFeeEnabled"
                               class="block w-full px-4 py-3 bg-white border border-emerald-200 rounded-2xl font-medium placeholder-emerald-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                               placeholder="3.50">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Operating Hours</label>
                    <div class="space-y-3 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        @php
                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            $hours = optional($restaurantDraft)->operating_hours ?? [];
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
                        <input type="checkbox" name="is_open" value="1" {{ (optional($restaurantDraft)->is_open ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                    <span class="text-sm font-bold text-gray-700">Open for business</span>
                </div>
                
                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-[0.98] mt-4">
                    {{ $restaurant ? 'Update Restaurant' : ($latestRequest ? 'Update Request' : 'Submit for Approval') }}
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
                        <x-heroicon-o-x-mark class="w-4 h-4" />
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
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
            </div>
            
            <form method="POST" action="{{ route('owner.menu-item.store') }}" enctype="multipart/form-data" class="p-6 space-y-4" x-data="{
                imagePreview: null,
                basePrice: '',
                hasVariants: false,
                isOnSale: false,
                discountPercentage: '',
                variants: [{ name: '', price: '' }],
                parsedDiscount() {
                    const value = parseFloat(this.discountPercentage);
                    return Number.isNaN(value) ? null : value;
                },
                calculateDiscountedPrice(price) {
                    const numericPrice = parseFloat(price);
                    const percentage = this.parsedDiscount();
                    if (Number.isNaN(numericPrice)) {
                        return null;
                    }
                    if (!this.isOnSale || percentage === null || percentage <= 0) {
                        return numericPrice.toFixed(2);
                    }
                    return Math.max(numericPrice * (1 - (percentage / 100)), 0).toFixed(2);
                },
                validVariants() {
                    return this.variants.filter((variant) => `${variant.name ?? ''}`.trim() !== '' && variant.price !== '' && variant.price !== null);
                },
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
                    <input type="number" step="0.01" min="0" name="price" x-model="basePrice" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-500/10 transition-all" placeholder="12.99">
                </div>

                <div class="pt-2 border-t border-gray-100 space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-gray-700">On Sale</p>
                            <p class="text-xs font-medium text-gray-400">Enter one discount percentage and we'll calculate the sale price for the item and every variant.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_on_sale" value="1" x-model="isOnSale" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 peer-checked:bg-emerald-500 transition-colors relative">
                                <div class="absolute top-[2px] left-[2px] w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200"
                                     :class="isOnSale ? 'translate-x-5' : 'translate-x-0'"></div>
                            </div>
                        </label>
                    </div>

                    <div x-show="isOnSale" x-transition class="space-y-3 rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4">
                        <label class="block text-xs font-black text-emerald-700 mb-1.5 uppercase tracking-widest">Discount Percentage (%)</label>
                        <input type="number"
                               step="0.01"
                               min="0.01"
                               max="100"
                               name="discount_percentage"
                               x-model="discountPercentage"
                               :required="isOnSale"
                               :disabled="!isOnSale"
                               class="block w-full px-3 py-2.5 bg-white border border-emerald-200 rounded-xl text-sm font-medium placeholder-emerald-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                               placeholder="15">
                        <p class="mt-1 text-[11px] text-emerald-600 font-medium">The system will calculate discounted prices automatically, including each variant option.</p>

                        <div x-show="parsedDiscount() !== null" x-cloak class="space-y-2 rounded-xl border border-emerald-200 bg-white/80 p-3">
                            <div class="flex items-center justify-between text-[11px] font-bold text-emerald-700">
                                <span>Live Preview</span>
                                <span x-text="`${parsedDiscount().toFixed(2)}% off`"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-600">Base item</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-400 line-through" x-show="basePrice !== ''" x-text="'$' + parseFloat(basePrice || 0).toFixed(2)"></span>
                                    <span class="font-black text-emerald-600" x-show="basePrice !== ''" x-text="'$' + calculateDiscountedPrice(basePrice)"></span>
                                </div>
                            </div>
                            <div x-show="hasVariants && validVariants().length > 0" x-cloak class="space-y-2 border-t border-emerald-100 pt-2">
                                <template x-for="(variant, index) in validVariants()" :key="`${variant.name}-${index}`">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-medium text-gray-600" x-text="variant.name"></span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-400 line-through" x-text="'$' + parseFloat(variant.price).toFixed(2)"></span>
                                            <span class="font-bold text-emerald-600" x-text="'$' + calculateDiscountedPrice(variant.price)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <p x-show="hasVariants && validVariants().length === 0" x-cloak class="text-[11px] font-medium text-emerald-600">Add variant names and prices to preview their sale prices.</p>
                        </div>
                    </div>
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
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
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
                                <x-heroicon-o-photo class="w-7 h-7" />
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
                            <x-heroicon-o-x-mark class="w-4 h-4" />
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
                        <x-heroicon-o-x-mark class="w-4 h-4" />
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
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
            </div>
            
            <form method="POST" :action="`{{ route('owner.menu-item.update', 'ID') }}`.replace('ID', editingMenuItem.id)" enctype="multipart/form-data" class="p-6 space-y-4" x-data="{
                newImagePreview: null,
                editHasVariants: false,
                editIsOnSale: false,
                editDiscountPercentage: '',
                editVariantType: '',
                editVariants: [{ name: '', price: '' }],
                parsedEditDiscount() {
                    const value = parseFloat(this.editDiscountPercentage);
                    return Number.isNaN(value) ? null : value;
                },
                calculateEditDiscountedPrice(price) {
                    const numericPrice = parseFloat(price);
                    const percentage = this.parsedEditDiscount();
                    if (Number.isNaN(numericPrice)) {
                        return null;
                    }
                    if (!this.editIsOnSale || percentage === null || percentage <= 0) {
                        return numericPrice.toFixed(2);
                    }
                    return Math.max(numericPrice * (1 - (percentage / 100)), 0).toFixed(2);
                },
                validEditVariants() {
                    return this.editVariants.filter((variant) => `${variant.name ?? ''}`.trim() !== '' && variant.price !== '' && variant.price !== null);
                },
                
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

                            this.editIsOnSale = !!val.is_on_sale;
                            this.editDiscountPercentage = val.discount_percentage || '';
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

                <div class="pt-2 border-t border-gray-100 space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-gray-700">On Sale</p>
                            <p class="text-xs font-medium text-gray-400">Enter one discount percentage and we'll apply it to the item and all of its variants.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_on_sale" value="1" x-model="editIsOnSale" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 peer-checked:bg-emerald-500 transition-colors relative">
                                <div class="absolute top-[2px] left-[2px] w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200"
                                     :class="editIsOnSale ? 'translate-x-5' : 'translate-x-0'"></div>
                            </div>
                        </label>
                    </div>

                    <div x-show="editIsOnSale" x-transition class="space-y-3 rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4">
                        <label class="block text-xs font-black text-emerald-700 mb-1.5 uppercase tracking-widest">Discount Percentage (%)</label>
                        <input type="number"
                               step="0.01"
                               min="0.01"
                               max="100"
                               name="discount_percentage"
                               x-model="editDiscountPercentage"
                               :required="editIsOnSale"
                               :disabled="!editIsOnSale"
                               class="block w-full px-3 py-2.5 bg-white border border-emerald-200 rounded-xl text-sm font-medium placeholder-emerald-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                               placeholder="15">
                        <p class="mt-1 text-[11px] text-emerald-600 font-medium">The system calculates the discounted sale price automatically for the item and every variant.</p>

                        <div x-show="parsedEditDiscount() !== null" x-cloak class="space-y-2 rounded-xl border border-emerald-200 bg-white/80 p-3">
                            <div class="flex items-center justify-between text-[11px] font-bold text-emerald-700">
                                <span>Live Preview</span>
                                <span x-text="`${parsedEditDiscount().toFixed(2)}% off`"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-600">Base item</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-400 line-through" x-show="editingMenuItem.price !== ''" x-text="'$' + parseFloat(editingMenuItem.price || 0).toFixed(2)"></span>
                                    <span class="font-black text-emerald-600" x-show="editingMenuItem.price !== ''" x-text="'$' + calculateEditDiscountedPrice(editingMenuItem.price)"></span>
                                </div>
                            </div>
                            <div x-show="editHasVariants && validEditVariants().length > 0" x-cloak class="space-y-2 border-t border-emerald-100 pt-2">
                                <template x-for="(variant, index) in validEditVariants()" :key="`${variant.name}-${index}`">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-medium text-gray-600" x-text="variant.name"></span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-400 line-through" x-text="'$' + parseFloat(variant.price).toFixed(2)"></span>
                                            <span class="font-bold text-emerald-600" x-text="'$' + calculateEditDiscountedPrice(variant.price)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <p x-show="editHasVariants && validEditVariants().length === 0" x-cloak class="text-[11px] font-medium text-emerald-600">Add variant names and prices to preview their sale prices.</p>
                        </div>
                    </div>
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
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
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
                                <x-heroicon-o-photo class="w-6 h-6 text-gray-400 mb-1 group-hover:scale-110 transition-transform" />
                                <p class="text-[10px] font-bold text-gray-500">New Image</p>
                            </div>

                            <div x-show="newImagePreview" x-cloak class="relative rounded-2xl overflow-hidden border-2 border-indigo-500 bg-gray-50 h-32">
                                <img :src="newImagePreview" alt="Preview" class="w-full h-full object-cover">
                                <button type="button" @click="removeImage()" class="absolute top-1 right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg transition-colors">
                                    <x-heroicon-o-x-mark class="w-3 h-3" />
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
            const initialStats = @json($stats);
            const restaurantChannel = 'restaurant.{{ $restaurant->id }}.orders';

            window.dashboardCharts = window.dashboardCharts || {
                sparklines: [],
                bar: null,
                pie: null,
                activePeriod: 'week',
            };

            window.destroyDashboardCharts = function() {
                if (Array.isArray(window.dashboardCharts.sparklines)) {
                    window.dashboardCharts.sparklines.forEach((chart) => chart?.destroy?.());
                }

                window.dashboardCharts.sparklines = [];
                window.dashboardCharts.bar?.destroy?.();
                window.dashboardCharts.pie?.destroy?.();
                window.dashboardCharts.bar = null;
                window.dashboardCharts.pie = null;
            };

            window.initDashboard = function(selectedPeriod = null, statsPayload = null) {
                const stats = statsPayload ?? initialStats;
                const activePeriod = selectedPeriod ?? window.dashboardCharts.activePeriod ?? 'week';

                window.destroyDashboardCharts();
                window.dashboardCharts.activePeriod = activePeriod;

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

                    if (!Array.isArray(series) || series.length === 0) {
                        return;
                    }

                    const chart = new Chart(canvas.getContext('2d'), {
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

                    window.dashboardCharts.sparklines.push(chart);
                });

                const barCanvas = document.getElementById('barChart');
                if (barCanvas) {
                    const chartWrap = barCanvas.closest('[data-chart-url]');
                    const chartUrl = chartWrap?.getAttribute('data-chart-url');
                    const titleEl = document.getElementById('barChartTitle');
                    const loaderEl = document.getElementById('barChartLoader');
                    const buttons = Array.from(document.querySelectorAll('.chart-period-btn'));

                    const setActiveBtn = (period) => {
                        window.dashboardCharts.activePeriod = period;

                        buttons.forEach((button) => {
                            const isActive = button.getAttribute('data-period') === period;
                            button.classList.toggle('bg-white', isActive);
                            button.classList.toggle('text-emerald-600', isActive);
                            button.classList.toggle('shadow-sm', isActive);
                            button.classList.toggle('text-gray-500', !isActive);
                        });
                    };

                    const setLoading = (isLoading) => {
                        if (!loaderEl) {
                            return;
                        }

                        loaderEl.classList.toggle('opacity-0', !isLoading);
                        loaderEl.classList.toggle('pointer-events-none', !isLoading);
                    };

                    window.dashboardCharts.bar = new Chart(barCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: stats.chart_data.bar.labels,
                            datasets: [{
                                label: 'Orders',
                                data: stats.chart_data.bar.data,
                                borderColor: 'rgb(79, 70, 229)',
                                backgroundColor: 'rgba(79, 70, 229, 0.12)',
                                borderWidth: 3,
                                tension: 0.35,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: 'rgb(79, 70, 229)',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2
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

                    const updateBarChart = async (period) => {
                        if (!chartUrl || !window.dashboardCharts.bar) {
                            return;
                        }

                        setActiveBtn(period);
                        setLoading(true);

                        try {
                            const res = await fetch(`${chartUrl}?period=${encodeURIComponent(period)}`, {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await res.json();
                            if (!res.ok) {
                                throw new Error(data?.message || 'Failed to load chart data');
                            }

                            if (titleEl && data.title) {
                                titleEl.textContent = data.title;
                            }

                            window.dashboardCharts.bar.data.labels = data.labels || [];
                            window.dashboardCharts.bar.data.datasets[0].data = data.data || [];
                            window.dashboardCharts.bar.update();
                        } catch (e) {
                            console.error('Chart update failed', e);
                        } finally {
                            setLoading(false);
                        }
                    };

                    buttons.forEach((button) => {
                        button.addEventListener('click', () => {
                            updateBarChart(button.getAttribute('data-period') || 'week');
                        });
                    });

                    setActiveBtn(activePeriod);
                    if (activePeriod !== 'week') {
                        updateBarChart(activePeriod);
                    }
                }

                const pieCanvas = document.getElementById('pieChart');
                if (pieCanvas) {
                    window.dashboardCharts.pie = new Chart(pieCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: stats.chart_data.pie.labels,
                            datasets: [{
                                data: stats.chart_data.pie.data,
                                backgroundColor: [
                                    '#fbbf24',
                                    '#10b981',
                                    '#ef4444',
                                    '#3b82f6',
                                    '#8b5cf6'
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
            };

            if (!window.ownerDashboardRealtimeInitialized) {
                window.ownerDashboardRealtimeInitialized = true;

                let refreshInFlight = false;

                window.refreshOwnerDashboardLiveSections = async function() {
                    if (refreshInFlight) {
                        return;
                    }

                    const wrapper = document.getElementById('dashboard-live-sections');
                    if (!wrapper) {
                        return;
                    }

                    refreshInFlight = true;
                    const activePeriod = window.dashboardCharts.activePeriod ?? 'week';

                    try {
                        const res = await fetch(window.location.href, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const html = await res.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const nextWrapper = doc.getElementById('dashboard-live-sections');

                        if (!nextWrapper) {
                            return;
                        }

                        wrapper.innerHTML = nextWrapper.innerHTML;
                        window.initDashboard(activePeriod);
                    } catch (error) {
                        console.error('Failed to refresh dashboard widgets', error);
                    } finally {
                        refreshInFlight = false;
                    }
                };

                if (window.Echo) {
                    try {
                        window.Echo.private(restaurantChannel)
                            .listen('.order.updated', () => window.refreshOwnerDashboardLiveSections());
                    } catch (error) {
                        console.warn('Realtime owner dashboard updates unavailable.', error);
                    }
                }
            }

            window.initDashboard();
        })();
    </script>
@endsection
