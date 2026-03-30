@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('owner.dashboard') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-emerald-600 font-bold transition-colors bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>

<!-- Orders Management -->
            <div class="mb-16 relative overflow-visible" id="orders" x-data="ordersFilter()" x-init="init()">

                <!-- New Order Notification Banner -->
                <div
                    x-show="newOrderCount > 0"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mb-6 flex items-center justify-between gap-4 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-2xl px-6 py-4 shadow-xl shadow-emerald-500/30"
                    x-cloak
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0 animate-bounce" style="animation-duration:1.5s">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        </div>
                        <div>
                            <p class="font-black text-sm" x-text="newOrderCount + ' new order' + (newOrderCount > 1 ? 's' : '') + ' just arrived!'">New orders just arrived!</p>
                            <p class="text-white/70 text-xs font-medium">Click refresh to view them immediately</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="refreshOrders()"
                            class="bg-white text-emerald-600 font-black text-xs px-4 py-2 rounded-xl hover:bg-emerald-50 transition-all active:scale-95 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Show New Orders
                        </button>
                        <button @click="newOrderCount = 0" class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white/30 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h3 class="text-3xl font-black outfit text-gray-900 flex items-center gap-3">
                            <span class="w-2 h-10 bg-emerald-500 rounded-full"></span>
                            Incoming Orders
                        </h3>
                        <p class="text-gray-500 font-medium mt-1">Manage and track your restaurant's orders in real-time.</p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2 relative z-30 overflow-visible w-full md:w-auto" id="orders-filters">
                        {{-- Status pills --}}
                        <a href="{{ route('owner.orders', ['status' => 'pending']) }}" @click.prevent="applyFilter($el.href)" class="order-filter-link px-5 py-2.5 rounded-2xl text-sm font-bold transition-all {{ request('status') === 'pending' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/30' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100' }}">Pending</a>
                        <a href="{{ route('owner.orders', ['status' => 'accepted']) }}" @click.prevent="applyFilter($el.href)" class="order-filter-link px-5 py-2.5 rounded-2xl text-sm font-bold transition-all {{ request('status') === 'accepted' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100' }}">Accepted</a>
                        <a href="{{ route('owner.orders', ['status' => 'delivered']) }}" @click.prevent="applyFilter($el.href)" class="order-filter-link px-5 py-2.5 rounded-2xl text-sm font-bold transition-all {{ request('status') === 'delivered' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100' }}">Delivered</a>

                        <div class="w-px h-10 bg-gray-200 mx-1 hidden md:block"></div>

                        {{-- Date / Sort dropdown --}}
                        @php
                            $activeFilterLabel = null;
                            if (request('filter') === 'day') $activeFilterLabel = 'Today';
                            elseif (request('filter') === 'week') $activeFilterLabel = 'This Week';
                            elseif (request('sort') === 'total') $activeFilterLabel = 'Highest Amount';
                        @endphp

                        <div class="relative z-40" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" type="button"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all
                                {{ $activeFilterLabel ? 'bg-gray-900 text-white shadow-lg shadow-gray-900/30' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100' }}">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                <span>{{ $activeFilterLabel ?? 'Filter & Sort' }}</span>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 x-cloak
                                 class="absolute left-0 sm:left-auto sm:right-0 mt-2 w-56 max-w-[90vw] bg-white rounded-2xl border border-gray-100 shadow-2xl shadow-gray-900/10 py-2 z-50 overflow-hidden">

                                <p class="px-4 pt-2 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-widest">Date Range</p>
                                <a href="{{ route('owner.orders', ['filter' => 'day']) }}" @click.prevent="applyFilter($el.href); open = false"
                                   class="order-filter-link flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors {{ request('filter') === 'day' ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Today
                                    @if(request('filter') === 'day')
                                        <svg class="w-4 h-4 ml-auto text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </a>
                                <a href="{{ route('owner.orders', ['filter' => 'week']) }}" @click.prevent="applyFilter($el.href); open = false"
                                   class="order-filter-link flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors {{ request('filter') === 'week' ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    This Week
                                    @if(request('filter') === 'week')
                                        <svg class="w-4 h-4 ml-auto text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </a>

                                <div class="my-1.5 mx-3 border-t border-gray-100"></div>

                                <p class="px-4 pt-1 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sort By</p>
                                <a href="{{ route('owner.orders', ['sort' => 'total']) }}" @click.prevent="applyFilter($el.href); open = false"
                                   class="order-filter-link flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors {{ request('sort') === 'total' ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                                    Highest Amount
                                    @if(request('sort') === 'total')
                                        <svg class="w-4 h-4 ml-auto text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </a>
                            </div>
                        </div>

                        {{-- Clear Filters (only visible when any filter is active) --}}
                        @if(request('filter') || request('sort') || request('status'))
                            <a href="{{ route('owner.orders') }}" @click.prevent="applyFilter($el.href)" class="order-filter-link px-5 py-2.5 rounded-2xl text-sm font-bold transition-all bg-white text-gray-500 hover:bg-red-50 hover:text-red-500 border border-gray-100 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Clear
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Orders list container --}}
                <div id="orders-list" class="relative">
                    {{-- Loading overlay --}}
                    <div x-show="filterLoading" x-transition.opacity.duration.200ms
                         class="absolute inset-0 bg-white/70 backdrop-blur-[2px] z-10 flex items-center justify-center rounded-3xl"
                         style="min-height: 120px;" x-cloak>
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-10 h-10 rounded-full border-4 border-emerald-400 border-t-transparent animate-spin shadow-lg shadow-emerald-500/30"></div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Loading orders…</span>
                        </div>
                    </div>

                    <div id="orders-content" :class="filterLoading ? 'opacity-40 scale-[0.99] pointer-events-none' : 'opacity-100 scale-100'" class="transition-all duration-300 grid grid-cols-1 gap-4">
                        @forelse($orders as $order)
                            <div
                                data-order-id="{{ $order->id }}"
                                :class="loadingOrderId === {{ $order->id }} ? 'opacity-70 pointer-events-none scale-[0.99]' : ''"
                                class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-xl hover:shadow-emerald-500/5 transition-all group"
                            >
                                <div class="p-5 md:p-6">
                                    <div class="flex flex-col lg:flex-row justify-between gap-6">
                                        <div class="flex flex-col sm:flex-row gap-5">
                                            <!-- Order Status Icon -->
                                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 {{ $order->status === 'pending' ? 'bg-amber-100 text-amber-500' : ($order->status === 'accepted' ? 'bg-emerald-100 text-emerald-500' : (in_array($order->status, ['preparing', 'out_for_delivery']) ? 'bg-indigo-100 text-indigo-500' : ($order->status === 'delivered' ? 'bg-blue-100 text-blue-500' : 'bg-gray-100 text-gray-400'))) }}">
                                                @if($order->status === 'pending')
                                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                @elseif($order->status === 'accepted')
                                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                @elseif(in_array($order->status, ['preparing', 'out_for_delivery']))
                                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                                @elseif($order->status === 'delivered')
                                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                @else
                                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                @endif
                                            </div>

                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-1">
                                                    @php
                                                        $statusLabel = $order->status === 'out_for_delivery' ? 'preparing' : $order->status;
                                                    @endphp
                                                    <h4 class="text-xl font-black outfit text-gray-900">Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h4>
                                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $order->status === 'pending' ? 'bg-amber-100 text-amber-600' : ($order->status === 'accepted' ? 'bg-emerald-100 text-emerald-600' : (in_array($order->status, ['preparing', 'out_for_delivery']) ? 'bg-indigo-100 text-indigo-600' : ($order->status === 'delivered' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'))) }}">
                                                        {{ $statusLabel }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-4 mb-4">
                                                    <p class="text-xs font-bold text-gray-400">{{ $order->created_at->format('M d • h:i A') }}</p>
                                                    @if($order->estimated_prep_time)
                                                        <span class="flex items-center gap-1 text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                            {{ $order->estimated_prep_time }} MIN PREP
                                                        </span>
                                                    @endif
                                                    @if($order->status === 'cancelled' && $order->rejection_reason)
                                                        <span class="flex items-center gap-1 text-[10px] font-black text-red-600 bg-red-50 px-2 py-0.5 rounded-lg border border-red-100">
                                                            Reason: {{ $order->rejection_reason }}
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <!-- Customer Details -->
                                                <div class="flex flex-wrap gap-x-6 gap-y-2">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                        <span class="text-xs font-bold text-gray-600">{{ $order->user->name }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                        <span class="text-xs font-bold text-gray-600">{{ $order->phone }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($order->delivery_address) }}" target="_blank" rel="noopener"
                                                           class="text-xs font-bold text-emerald-600 hover:text-emerald-500 underline decoration-emerald-200 underline-offset-2 truncate max-w-[150px]">
                                                            {{ $order->delivery_address }}
                                                        </a>
                                                    </div>
                                                </div>
                                                @if($order->notes)
                                                    <div class="mt-3 flex items-start gap-3 rounded-2xl bg-amber-50 border border-amber-100 px-3 py-2">
                                                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        </div>
                                                        <div>
                                                            <p class="text-[10px] font-black uppercase tracking-widest text-amber-600">Special Instructions</p>
                                                            <p class="text-xs font-medium text-gray-700">{{ $order->notes }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-center gap-4 border-t lg:border-t-0 pt-4 lg:pt-0">
                                            <div class="lg:text-right">
                                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</p>
                                                <p class="text-2xl font-black outfit text-emerald-500">${{ number_format($order->total, 2) }}</p>
                                            </div>
                                            
                                            @if($order->status === 'pending')
                                                <div class="flex flex-col gap-2">
                                                    <form method="POST" action="{{ route('owner.order.accept', $order) }}" class="order-status-form flex gap-2 w-full justify-end">
                                                        @csrf
                                                        <select name="estimated_prep_time" class="text-sm border-0 bg-gray-50 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-gray-700 font-bold py-2 px-3">
                                                            <option value="15">15 min prep</option>
                                                            <option value="30">30 min prep</option>
                                                            <option value="45">45 min prep</option>
                                                            <option value="60">60 min prep</option>
                                                        </select>
                                                        <button type="submit" data-loading-key="accept-{{ $order->id }}" :disabled="loadingOrderId === {{ $order->id }}" class="bg-emerald-500 hover:bg-emerald-400 disabled:hover:bg-emerald-500 text-white font-bold py-2 px-6 rounded-xl shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95 disabled:transform-none disabled:opacity-80 flex items-center gap-2 text-sm whitespace-nowrap">
                                                            <span>Accept</span>
                                                            <span x-show="loadingButtonKey === 'accept-{{ $order->id }}'" x-cloak class="inline-flex">
                                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                                            </span>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" action="{{ route('owner.order.reject', $order) }}" class="order-status-form flex gap-2 w-full justify-end" x-data="{ showReason: false }">
                                                        @csrf
                                                        <input x-show="showReason" x-transition type="text" name="rejection_reason" placeholder="Reason (Optional)" class="text-sm border-0 bg-red-50 text-red-600 placeholder-red-300 rounded-xl focus:ring-red-500 focus:border-red-500 py-2 px-3 w-32">
                                                        <button type="button" x-show="!showReason" @click="showReason = true" class="bg-white hover:bg-red-50 text-red-500 font-bold py-2 px-6 rounded-xl border border-red-100 transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2 text-sm">
                                                            Reject
                                                        </button>
                                                        <button type="submit" data-loading-key="reject-{{ $order->id }}" :disabled="loadingOrderId === {{ $order->id }}" x-show="showReason" x-cloak class="bg-red-500 hover:bg-red-400 disabled:hover:bg-red-500 text-white font-bold py-2 px-4 rounded-xl shadow-lg shadow-red-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95 disabled:transform-none disabled:opacity-80 flex items-center justify-center gap-2 text-sm whitespace-nowrap">
                                                            <span>Confirm Reject</span>
                                                            <span x-show="loadingButtonKey === 'reject-{{ $order->id }}'" x-cloak class="inline-flex">
                                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                                            </span>
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif($order->status === 'accepted')
                                                <div class="flex items-center gap-2">
                                                    <form method="POST" action="{{ route('owner.order.prepare', $order) }}" class="order-status-form">
                                                        @csrf
                                                        <button type="submit" data-loading-key="prepare-{{ $order->id }}" :disabled="loadingOrderId === {{ $order->id }}" class="bg-indigo-500 hover:bg-indigo-400 disabled:hover:bg-indigo-500 text-white font-bold py-2 px-6 rounded-xl shadow-lg shadow-indigo-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95 disabled:transform-none disabled:opacity-80 flex items-center gap-2 text-sm">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                                            <span>Start Preparing</span>
                                                            <span x-show="loadingButtonKey === 'prepare-{{ $order->id }}'" x-cloak class="inline-flex">
                                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                                            </span>
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif(in_array($order->status, ['preparing', 'out_for_delivery']))
                                                <div class="flex items-center gap-2">
                                                    <form method="POST" action="{{ route('owner.order.deliver', $order) }}" class="order-status-form">
                                                        @csrf
                                                        <button type="submit" data-loading-key="deliver-{{ $order->id }}" :disabled="loadingOrderId === {{ $order->id }}" class="bg-emerald-500 hover:bg-emerald-600 disabled:hover:bg-emerald-500 text-white font-black py-2.5 px-6 rounded-xl shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95 disabled:transform-none disabled:opacity-80 flex items-center gap-2 text-sm">
                                                            <div class="w-5 h-5 bg-white/20 rounded-lg flex items-center justify-center">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                            </div>
                                                            <span>Mark Delivered</span>
                                                            <span x-show="loadingButtonKey === 'deliver-{{ $order->id }}'" x-cloak class="inline-flex">
                                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                                            </span>
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif($order->status === 'delivered')
                                                <div class="flex items-center gap-3">
                                                    <a href="{{ route('owner.order.print', $order) }}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-600 p-2 rounded-xl transition-all" title="Print Ticket">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                    </a>
                                                    <span class="inline-flex items-center gap-1.5 text-xs font-black text-white bg-indigo-500 px-4 py-2 rounded-xl shadow-lg shadow-indigo-500/20">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                        Delivered
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mt-5 pt-5 border-t border-gray-50 bg-gray-50/30 -mx-5 -mb-5 px-5 pb-5">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($order->orderItems as $item)
                                                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white rounded-xl border border-gray-100 shadow-sm">
                                                    <span class="text-[10px] font-black text-emerald-500">{{ $item->quantity }}x</span>
                                                    <span class="text-[11px] font-bold text-gray-700">{{ $item->name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white rounded-[2.5rem] border-2 border-dashed border-gray-100 p-20 text-center">
                                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-200">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                </div>
                                <h4 class="text-2xl font-black outfit text-gray-900 mb-3">No orders found</h4>
                                <p class="text-gray-500 font-medium max-w-sm mx-auto">Try changing your filters or check back later when new orders arrive.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    @if($orders->hasPages())
                        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4" id="orders-pagination">
                            <p class="text-xs font-bold text-gray-400">
                                Showing <span class="text-gray-700">{{ $orders->firstItem() }}–{{ $orders->lastItem() }}</span> of <span class="text-gray-700">{{ $orders->total() }}</span> orders
                            </p>
                            <div class="flex items-center gap-1.5">
                                {{-- Previous --}}
                                @if($orders->onFirstPage())
                                    <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-gray-300 cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                    </span>
                                @else
                                    <a href="{{ $orders->previousPageUrl() }}" @click.prevent="applyFilter($el.href)" class="order-filter-link w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                    </a>
                                @endif

                                {{-- Page Numbers --}}
                                @php
                                    $currentPage = $orders->currentPage();
                                    $lastPage = $orders->lastPage();
                                    $pages = [];

                                    if ($lastPage <= 7) {
                                        $pages = range(1, $lastPage);
                                    } else {
                                        $pages = [1];
                                        if ($currentPage > 3) $pages[] = '...';
                                        for ($i = max(2, $currentPage - 1); $i <= min($lastPage - 1, $currentPage + 1); $i++) {
                                            $pages[] = $i;
                                        }
                                        if ($currentPage < $lastPage - 2) $pages[] = '...';
                                        $pages[] = $lastPage;
                                    }
                                @endphp

                                @foreach($pages as $page)
                                    @if($page === '...')
                                        <span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm font-bold">…</span>
                                    @elseif($page == $currentPage)
                                        <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-emerald-500 text-white text-sm font-black shadow-lg shadow-emerald-500/30">{{ $page }}</span>
                                    @else
                                        <a href="{{ $orders->url($page) }}" @click.prevent="applyFilter($el.href)" class="order-filter-link w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-bold transition-all shadow-sm">{{ $page }}</a>
                                    @endif
                                @endforeach

                                {{-- Next --}}
                                @if($orders->hasMorePages())
                                    <a href="{{ $orders->nextPageUrl() }}" @click.prevent="applyFilter($el.href)" class="order-filter-link w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                @else
                                    <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-gray-300 cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <script>
                function ordersFilter() {
                    return {
                        filterLoading: false,
                        loadingOrderId: null,
                        loadingButtonKey: null,
                        newOrderCount: 0,
                        usingEcho: false,
                        latestOrderId: {{ $orders->isNotEmpty() ? $orders->first()->id : 0 }},
                        pollTimer: null,
                        pollInFlight: false,
                        pollConfig: {
                            visible: {{ (int) config('performance.polling.owner_visible_ms') }},
                            hidden: {{ (int) config('performance.polling.owner_hidden_ms') }},
                            retry: {{ (int) config('performance.polling.owner_retry_ms') }},
                            focus: {{ (int) config('performance.polling.owner_focus_ms') }},
                        },

                        init() {
                            // Handle browser back/forward buttons
                            window.addEventListener('popstate', () => {
                                this.applyFilter(window.location.href, false);
                            });

                            // Intercept status forms
                            this.$nextTick(() => this.bindStatusForms());

                            this.usingEcho = this.subscribeToRealtime();

                            if (this.usingEcho) {
                                return;
                            }

                            document.addEventListener('visibilitychange', () => {
                                if (document.hidden) {
                                    this.stopPolling();
                                } else {
                                    this.schedulePoll(this.pollConfig.focus);
                                }
                            });

                            window.addEventListener('online', () => this.schedulePoll(this.pollConfig.focus));

                            this.schedulePoll(this.pollConfig.visible);
                        },

                        subscribeToRealtime() {
                            if (!window.Echo) {
                                return false;
                            }

                            try {
                                window.Echo.private('restaurant.{{ $restaurant->id }}.orders')
                                    .listen('.order.updated', (payload) => this.handleRealtimeUpdate(payload));

                                return true;
                            } catch (error) {
                                console.warn('Realtime owner updates unavailable, falling back to polling.', error);
                                return false;
                            }
                        },

                        async handleRealtimeUpdate(payload) {
                            const order = payload?.order;
                            if (!order?.id) {
                                return;
                            }

                            this.latestOrderId = Math.max(this.latestOrderId, Number(order.id));

                            if (payload.type === 'created') {
                                this.playNotificationSound();

                                const currentUrl = new URL(window.location.href);
                                const status = currentUrl.searchParams.get('status');

                                if (!status || status === 'pending') {
                                    await this.applyFilter(window.location.href, false);
                                } else {
                                    this.newOrderCount += 1;
                                }

                                return;
                            }

                            await this.applyFilter(window.location.href, false);
                        },

                        playNotificationSound() {
                            try {
                                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                                const osc = ctx.createOscillator();
                                const gain = ctx.createGain();
                                osc.connect(gain);
                                gain.connect(ctx.destination);
                                osc.frequency.setValueAtTime(880, ctx.currentTime);
                                osc.frequency.setValueAtTime(660, ctx.currentTime + 0.15);
                                gain.gain.setValueAtTime(0.1, ctx.currentTime);
                                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                                osc.start();
                                osc.stop(ctx.currentTime + 0.4);
                            } catch(e) {}
                        },

                        currentPollDelay() {
                            return document.hidden ? this.pollConfig.hidden : this.pollConfig.visible;
                        },

                        stopPolling() {
                            if (this.pollTimer) {
                                clearTimeout(this.pollTimer);
                                this.pollTimer = null;
                            }
                        },

                        schedulePoll(delay = null) {
                            this.stopPolling();

                            if (document.hidden || !navigator.onLine) {
                                return;
                            }

                            this.pollTimer = setTimeout(() => this.checkNewOrders(), delay ?? this.currentPollDelay());
                        },

                        async checkNewOrders() {
                            if (this.pollInFlight || this.filterLoading || this.loadingOrderId) {
                                this.schedulePoll(this.currentPollDelay());
                                return;
                            }

                            this.pollInFlight = true;
                            try {
                                const url = '{{ route("owner.dashboard.poll-new-orders") }}?since_id=' + this.latestOrderId;
                                const res = await fetch(url, {
                                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                                });
                                if (!res.ok) {
                                    this.schedulePoll(this.pollConfig.retry);
                                    return;
                                }
                                const data = await res.json();

                                if (data.count > 0) {
                                    this.latestOrderId = data.latest_id;

                                    this.playNotificationSound();

                                    // Fully real-time: auto-refresh if looking at Pending tab
                                    const currentUrl = new URL(window.location.href);
                                    const status = currentUrl.searchParams.get('status');
                                    
                                    if (!status || status === 'pending') {
                                        // Fetch and inject new HTML immediately without reloading page
                                        this.refreshOrders();
                                    } else {
                                        // Show the banner so they can switch tabs when they are ready
                                        this.newOrderCount += data.count;
                                        this.schedulePoll(this.pollConfig.focus);
                                    }
                                } else {
                                    this.schedulePoll(this.currentPollDelay());
                                }
                            } catch(e) {
                                this.schedulePoll(this.pollConfig.retry);
                            } finally {
                                this.pollInFlight = false;
                            }
                        },

                        async refreshOrders() {
                            if (this.filterLoading) return;
                            this.newOrderCount = 0;
                            // Switch to pending view to show new orders
                            const url = '{{ route("owner.orders", ["status" => "pending"]) }}';
                            await this.applyFilter(url);
                        },

                        bindStatusForms() {
                            const container = document.getElementById('orders');
                            if (!container) return;
                            container.querySelectorAll('.order-status-form').forEach(form => {
                                if (form.dataset.bound) return;
                                form.addEventListener('submit', (e) => {
                                    e.preventDefault();
                                    this.submitStatusUpdate(form, e.submitter);
                                });
                                form.dataset.bound = "true";
                            });
                        },

                        async applyFilter(url, pushState = true) {
                            this.filterLoading = true;

                            try {
                                const res = await fetch(url, {
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                });
                                const html = await res.text();

                                // Parse the response and extract just the #orders section
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const newOrders = doc.getElementById('orders');

                                if (newOrders) {
                                    const container = document.getElementById('orders');

                                    // Replace the filters bar
                                    const newFilters = newOrders.querySelector('#orders-filters');
                                    const currentFilters = container.querySelector('#orders-filters');
                                    if (newFilters && currentFilters) {
                                        currentFilters.innerHTML = newFilters.innerHTML;
                                    }

                                    // Replace the orders content with a smooth swap
                                    const newContent = newOrders.querySelector('#orders-content');
                                    const currentContent = container.querySelector('#orders-content');
                                    if (newContent && currentContent) {
                                        currentContent.innerHTML = newContent.innerHTML;
                                    }

                                    // Replace the pagination
                                    const newPagination = newOrders.querySelector('#orders-pagination');
                                    const currentPagination = container.querySelector('#orders-pagination');
                                    if (newPagination && currentPagination) {
                                        currentPagination.innerHTML = newPagination.innerHTML;
                                    } else if (newPagination && !currentPagination) {
                                        // Pagination appeared (new results have pages)
                                        const listContainer = container.querySelector('#orders-list');
                                        if (listContainer) {
                                            const paginationDiv = document.createElement('div');
                                            paginationDiv.id = 'orders-pagination';
                                            paginationDiv.className = newPagination.className;
                                            paginationDiv.innerHTML = newPagination.innerHTML;
                                            listContainer.appendChild(paginationDiv);
                                        }
                                    } else if (!newPagination && currentPagination) {
                                        // Pagination disappeared (filtered results fit on one page)
                                        currentPagination.remove();
                                    }

                                    // Update browser URL
                                    if (pushState) {
                                        window.history.pushState({}, '', url);
                                    }

                                    // Re-bind Alpine click handlers on new filter links
                                    this.$nextTick(() => {
                                        container.querySelectorAll('#orders-filters .order-filter-link').forEach(link => {
                                            link.addEventListener('click', (e) => {
                                                e.preventDefault();
                                                this.applyFilter(link.href);
                                            });
                                        });

                                        // Re-init nested Alpine dropdown on new filters
                                        const dropdownRoot = container.querySelector('#orders-filters [x-data]');
                                        if (dropdownRoot) {
                                            dropdownRoot.querySelectorAll('.order-filter-link').forEach(link => {
                                                link.addEventListener('click', (e) => {
                                                    e.preventDefault();
                                                    this.applyFilter(link.href);
                                                });
                                            });
                                        }

                                        // Re-bind pagination links
                                        container.querySelectorAll('#orders-pagination .order-filter-link').forEach(link => {
                                            link.addEventListener('click', (e) => {
                                                e.preventDefault();
                                                this.applyFilter(link.href);
                                            });
                                        });

                                        this.bindStatusForms();
                                    });
                                }
                            } catch (err) {
                                console.error('Filter fetch failed, falling back:', err);
                                window.location.href = url;
                            }

                            this.filterLoading = false;

                            if (!this.usingEcho) {
                                this.schedulePoll(this.currentPollDelay());
                            }
                        },

                        async refreshSingleOrder(orderId) {
                            const container = document.getElementById('orders');
                            if (!container) return;

                            const res = await fetch(window.location.href, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            const html = await res.text();
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newOrders = doc.getElementById('orders');
                            if (!newOrders) return;

                            const currentContent = container.querySelector('#orders-content');
                            const freshContent = newOrders.querySelector('#orders-content');
                            const currentCard = container.querySelector('[data-order-id="' + orderId + '"]');
                            const freshCard = newOrders.querySelector('[data-order-id="' + orderId + '"]');
                            const freshHasCards = freshContent && freshContent.querySelector('[data-order-id]');

                            if (currentContent && freshContent) {
                                if (freshCard && currentCard) {
                                    currentCard.replaceWith(freshCard);
                                } else if (!freshCard) {
                                    if (!freshHasCards) {
                                        currentContent.innerHTML = freshContent.innerHTML;
                                    } else if (currentCard) {
                                        currentCard.remove();
                                    }
                                }
                            }

                            const newPagination = newOrders.querySelector('#orders-pagination');
                            const currentPagination = container.querySelector('#orders-pagination');
                            if (newPagination && currentPagination) {
                                currentPagination.innerHTML = newPagination.innerHTML;
                            } else if (newPagination && !currentPagination) {
                                const listContainer = container.querySelector('#orders-list');
                                if (listContainer) {
                                    const paginationDiv = document.createElement('div');
                                    paginationDiv.id = 'orders-pagination';
                                    paginationDiv.className = newPagination.className;
                                    paginationDiv.innerHTML = newPagination.innerHTML;
                                    listContainer.appendChild(paginationDiv);
                                }
                            } else if (!newPagination && currentPagination) {
                                currentPagination.remove();
                            }

                            this.$nextTick(() => {
                                container.querySelectorAll('#orders-pagination .order-filter-link').forEach(link => {
                                    link.addEventListener('click', (e) => {
                                        e.preventDefault();
                                        this.applyFilter(link.href);
                                    });
                                });
                                this.bindStatusForms();
                            });

                            if (!this.usingEcho) {
                                this.schedulePoll(this.currentPollDelay());
                            }
                        },

                        async submitStatusUpdate(form, submitter = null) {
                            const orderCard = form.closest('[data-order-id]');
                            const orderId = orderCard ? orderCard.getAttribute('data-order-id') : null;
                            const loadingKey = submitter ? submitter.dataset.loadingKey || null : null;

                            this.loadingOrderId = orderId;
                            this.loadingButtonKey = loadingKey;
                            try {
                                const formData = new FormData(form);
                                const res = await fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': formData.get('_token'),
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json',
                                        'X-Socket-ID': window.Echo?.socketId() ?? ''
                                    },
                                    body: formData
                                });

                                if (!res.ok) {
                                    throw new Error('Status update request failed');
                                }

                                if (orderId) {
                                    await this.refreshSingleOrder(orderId);
                                } else {
                                    await this.applyFilter(window.location.href, false);
                                }
                            } catch (err) {
                                console.error('Status update failed:', err);
                                form.submit();
                            } finally {
                                this.loadingOrderId = null;
                                this.loadingButtonKey = null;
                            }
                        },

                        destroy() {
                            this.stopPolling();
                            window.Echo?.leaveChannel('private-restaurant.{{ $restaurant->id }}.orders');
                        }
                    };
                }
            </script>

            
    </div>
</div>
@endsection
