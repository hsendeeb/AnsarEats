@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4" x-data="ordersFilter()" x-init="init()">
    <div x-show="toast.open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-4 left-4 right-4 z-[90] sm:left-auto sm:right-4 sm:w-[22rem]">
        <div class="flex items-start gap-3 rounded-[1.5rem] border border-emerald-100 bg-white px-4 py-4 shadow-2xl shadow-emerald-500/15">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-500">Success</p>
                <p class="mt-1 text-sm font-semibold text-gray-700" x-text="toast.message"></p>
            </div>
            <button type="button"
                    @click="hideToast()"
                    class="flex h-8 w-8 items-center justify-center rounded-xl text-gray-300 transition-colors hover:bg-gray-50 hover:text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between">
            <a href="{{ route('owner.dashboard') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-emerald-600 font-bold transition-colors bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
            <button type="button"
                                @click="openDeleteDialog({
                                    type: 'clear-all',
                                    title: 'Clear all orders?',
                                    message: 'This will permanently delete only the orders that match the filters you currently selected.',
                                    action: '{{ route('owner.orders.clear') }}',
                                    buttonLabel: 'Clear All Orders'
                                })"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold text-red-500 bg-white border border-red-100 hover:bg-red-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 7h16m-10 4v6m4-6v6M9 7V4h6v3m-7 0h8l-1 13a2 2 0 01-2 2h-2a2 2 0 01-2-2L8 7z"></path></svg>
                            Clear All
                        </button>
        </div>

<!-- Orders Management -->
            <div class="mb-16 relative overflow-visible" id="orders">

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
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 overflow-visible">
            

                    <div class="w-full lg:max-w-4xl" id="orders-filters">
                        <div class="flex flex-col gap-3 lg:items-end">
                        <form method="GET"
                              action="{{ route('owner.orders') }}"
                              @submit.prevent="applySearch(searchTerm)"
                              class="w-full lg:w-[30rem]">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('filter'))
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            @endif
                            @if(request('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif

                            <label for="owner-orders-search" class="mb-2 flex items-center justify-between gap-3 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">
                                <span>Find Orders</span>
                                <span class="rounded-full bg-white px-3 py-1 text-[10px] text-emerald-600 shadow-sm shadow-emerald-100/60">Live search</span>
                            </label>

                            <div class="relative" @click.outside="showSearchSuggestions = false">
                                <div class="rounded-[1.75rem] border border-gray-200 bg-white p-2.5 shadow-[0_18px_45px_-24px_rgba(17,24,39,0.28)] transition-all duration-200 focus-within:border-emerald-300 focus-within:shadow-[0_22px_50px_-24px_rgba(16,185,129,0.3)]">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                        <div class="relative flex-1">
                                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                                <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            </span>
                                            <input id="owner-orders-search"
                                                   type="search"
                                                   name="q"
                                                   autocomplete="off"
                                                   x-model="searchTerm"
                                                   x-ref="orderSearchInput"
                                                   @input="queueSearchSuggestions()"
                                                   @focus="handleSearchFocus()"
                                                   @keydown.escape.prevent="showSearchSuggestions = false"
                                                   @keydown.arrow-down.prevent="highlightNextSearchSuggestion()"
                                                   @keydown.arrow-up.prevent="highlightPreviousSearchSuggestion()"
                                                   @keydown.enter.prevent="selectActiveSearchSuggestion()"
                                                   :aria-expanded="showSearchSuggestions ? 'true' : 'false'"
                                                   placeholder=" #, customer, or phone"
                                                   class="w-full rounded-[1.2rem] border-0 bg-gray-50/80 py-3.5 pl-11 pr-11 text-sm font-semibold text-gray-700 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-0">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg x-show="searchSuggestionsLoading" x-cloak class="h-4 w-4 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 sm:flex-shrink-0">
                                            <button type="button"
                                                    x-show="searchTerm.trim().length"
                                                    x-cloak
                                                    @click="clearOrderSearch()"
                                                    class="inline-flex h-11 flex-1 items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 text-xs font-black uppercase tracking-[0.18em] text-gray-500 transition-all hover:border-red-100 hover:bg-red-50 hover:text-red-500 sm:flex-none">
                                                Clear
                                            </button>
                                            <button type="submit"
                                                    class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-2xl bg-gray-900 px-5 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-500 sm:flex-none">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                                Search
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="showSearchSuggestions && searchTerm.trim().length >= 2"
                                     x-transition:enter="transition ease-out duration-180"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-120"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-1 scale-[0.99]"
                                     x-cloak
                                     class="absolute left-0 right-0 top-[calc(100%-1px)] z-50 overflow-hidden rounded-b-[1.5rem] rounded-t-[1rem] border border-t-0 border-gray-200 bg-white shadow-[0_24px_50px_-24px_rgba(17,24,39,0.28)]">
                                    <div class="max-h-[min(22rem,60vh)] overflow-y-auto overscroll-contain py-2"
                                         style="-webkit-overflow-scrolling: touch; touch-action: pan-y;">
                                        <div x-show="searchSuggestionsLoading" x-cloak class="flex items-center gap-3 px-4 py-4 text-sm font-semibold text-gray-500">
                                            <svg class="h-4 w-4 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                            <span>Searching orders...</span>
                                        </div>

                                        <template x-if="!searchSuggestionsLoading && searchSuggestions.length">
                                            <div>
                                                <template x-for="(suggestion, index) in searchSuggestions" :key="suggestion.id">
                                                    <button type="button"
                                                            @mouseenter="activeSearchSuggestionIndex = index"
                                                            @click="chooseSearchSuggestion(suggestion)"
                                                            :class="activeSearchSuggestionIndex === index ? 'bg-emerald-50/80' : 'bg-white'"
                                                            class="flex w-full items-start gap-3 px-4 py-3 text-left transition-colors hover:bg-emerald-50/80">
                                                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-gray-100 text-gray-600">
                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9m-5-4h5m0 0v5m0-5L10 14"></path></svg>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex flex-wrap items-center gap-2">
                                                                <p class="text-sm font-black text-gray-900" x-text="suggestion.order_number"></p>
                                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.18em]"
                                                                      :class="suggestion.status_tone"
                                                                      x-text="suggestion.status_label"></span>
                                                            </div>
                                                            <p class="mt-1 truncate text-sm font-semibold text-gray-600" x-text="suggestion.customer_name"></p>
                                                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-medium text-gray-400">
                                                                <span x-text="suggestion.phone"></span>
                                                                <span x-text="suggestion.created_at"></span>
                                                                <span class="font-bold text-emerald-600" x-text="suggestion.total"></span>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>

                                        <div x-show="!searchSuggestionsLoading && searchSuggestionsLoaded && !searchSuggestions.length"
                                             x-cloak
                                             class="px-4 py-6 text-center">
                                            <p class="text-sm font-bold text-gray-600">No matching orders found.</p>
                                            <p class="mt-1 text-xs font-medium text-gray-400">Try the order number, customer name, or phone number.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

       
                        </form>

                        <div class="flex flex-wrap items-center gap-2 relative z-30 overflow-visible w-full lg:justify-end">

                        @php
                            $activeStatusLabel = match (request('status')) {
                                'pending' => 'Pending',
                                'accepted' => 'Accepted',
                                'preparing' => 'Preparing',
                                'delivered' => 'Delivered',
                                default => null,
                            };
                        @endphp

                        {{-- Mobile status dropdown --}}
                        <div class="relative z-40 w-full md:hidden" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" type="button"
                                class="w-full inline-flex items-center justify-between gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all {{ $activeStatusLabel ? 'bg-gray-900 text-white shadow-lg shadow-gray-900/30' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100' }}">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    <span>{{ $activeStatusLabel ?? 'Order Status' }}</span>
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    @if($activeStatusLabel)
                                        <span class="inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-black bg-white/20 text-white">
                                            {{ $statusCounts[request('status')] ?? 0 }}
                                        </span>
                                    @endif
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 x-cloak
                                 class="absolute left-0 right-0 mt-2 bg-white rounded-2xl border border-gray-100 shadow-2xl shadow-gray-900/10 py-2 z-50 overflow-hidden">
                                <p class="px-4 pt-2 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-widest">Order Status</p>
                                <a href="{{ route('owner.orders', ['status' => 'pending']) }}" @click.prevent="applyFilter($el.href); open = false"
                                   class="order-filter-link flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors {{ request('status') === 'pending' ? 'bg-amber-50 text-amber-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <span>Pending</span>
                                    <span class="ml-auto inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-black {{ request('status') === 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-amber-50 text-amber-600' }}">{{ $statusCounts['pending'] ?? 0 }}</span>
                                </a>
                                <a href="{{ route('owner.orders', ['status' => 'accepted']) }}" @click.prevent="applyFilter($el.href); open = false"
                                   class="order-filter-link flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors {{ request('status') === 'accepted' ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <span>Accepted</span>
                                    <span class="ml-auto inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-black {{ request('status') === 'accepted' ? 'bg-emerald-100 text-emerald-600' : 'bg-emerald-50 text-emerald-600' }}">{{ $statusCounts['accepted'] ?? 0 }}</span>
                                </a>
                                <a href="{{ route('owner.orders', ['status' => 'preparing']) }}" @click.prevent="applyFilter($el.href); open = false"
                                   class="order-filter-link flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors {{ request('status') === 'preparing' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <span>Preparing</span>
                                    <span class="ml-auto inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-black {{ request('status') === 'preparing' ? 'bg-indigo-100 text-indigo-600' : 'bg-indigo-50 text-indigo-600' }}">{{ $statusCounts['preparing'] ?? 0 }}</span>
                                </a>
                                <a href="{{ route('owner.orders', ['status' => 'delivered']) }}" @click.prevent="applyFilter($el.href); open = false"
                                   class="order-filter-link flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors {{ request('status') === 'delivered' ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <span>Delivered</span>
                                    <span class="ml-auto inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-black {{ request('status') === 'delivered' ? 'bg-blue-100 text-blue-600' : 'bg-blue-50 text-blue-600' }}">{{ $statusCounts['delivered'] ?? 0 }}</span>
                                </a>
                            </div>
                        </div>

                        {{-- Desktop status pills --}}
                        <div class="hidden md:flex md:flex-wrap md:items-center md:gap-2">
                            <a href="{{ route('owner.orders', ['status' => 'pending']) }}" @click.prevent="applyFilter($el.href)" class="order-filter-link inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all {{ request('status') === 'pending' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/30' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100' }}">
                                <span>Pending</span>
                                <span class="inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-black {{ request('status') === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-600' }}">{{ $statusCounts['pending'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('owner.orders', ['status' => 'accepted']) }}" @click.prevent="applyFilter($el.href)" class="order-filter-link inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all {{ request('status') === 'accepted' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100' }}">
                                <span>Accepted</span>
                                <span class="inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-black {{ request('status') === 'accepted' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-600' }}">{{ $statusCounts['accepted'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('owner.orders', ['status' => 'preparing']) }}" @click.prevent="applyFilter($el.href)" class="order-filter-link inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all {{ request('status') === 'preparing' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100' }}">
                                <span>Preparing</span>
                                <span class="inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-black {{ request('status') === 'preparing' ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-600' }}">{{ $statusCounts['preparing'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('owner.orders', ['status' => 'delivered']) }}" @click.prevent="applyFilter($el.href)" class="order-filter-link inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold transition-all {{ request('status') === 'delivered' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100' }}">
                                <span>Delivered</span>
                                <span class="inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-black {{ request('status') === 'delivered' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600' }}">{{ $statusCounts['delivered'] ?? 0 }}</span>
                            </a>
                        </div>

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
                                 class="absolute left-0 md:left-auto md:right-0 mt-2 w-56 max-w-[calc(100vw-2rem)] bg-white rounded-2xl border border-gray-100 shadow-2xl shadow-gray-900/10 py-2 z-50 overflow-hidden">

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
                        @if(request('filter') || request('sort') || request('status') || request('q'))
                            <a href="{{ route('owner.orders') }}" @click.prevent="applyFilter($el.href, true, { preserveSearch: false })" class="order-filter-link px-5 py-2.5 rounded-2xl text-sm font-bold transition-all bg-white text-gray-500 hover:bg-red-50 hover:text-red-500 border border-gray-100 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Clear
                            </a>
                        @endif

                        </div>
                        </div>
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

                    <div class="mb-4 rounded-[2rem] border border-gray-100 bg-white/90 px-4 py-4 shadow-sm backdrop-blur-sm">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex flex-wrap items-center gap-3">
                                <label class="inline-flex items-center gap-3 text-sm font-semibold text-gray-600">
                                    <input type="checkbox"
                                           class="h-4 w-4 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500"
                                           :checked="areAllVisibleSelected()"
                                           :disabled="visibleOrderIds().length === 0"
                                           @change="toggleSelectAllVisible($event.target.checked)">
                                    <span>Select all on this page</span>
                                </label>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-gray-500"
                                      x-text="visibleOrderIds().length ? visibleOrderIds().length + ' visible' : 'No visible orders'"></span>
                            </div>

                            <div x-show="selectedOrderIds.length > 0"
                                 x-cloak
                                 class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-emerald-600"
                                      x-text="selectedOrderIds.length + ' selected'"></span>
                                <button type="button"
                                        @click="clearSelection()"
                                        class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-500 transition-all hover:bg-gray-50 hover:text-gray-800">
                                    Clear Selection
                                </button>
                                <button type="button"
                                        @click="openDeleteDialog({
                                            type: 'selected-orders',
                                            title: 'Delete selected orders?',
                                            message: 'This will permanently delete the checked orders from your restaurant inbox.',
                                            action: '{{ route('owner.orders.destroy-selected') }}',
                                            orderIds: selectedOrderIds,
                                            buttonLabel: 'Delete Selected'
                                        })"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-red-500 px-4 py-2 text-sm font-black text-white shadow-lg shadow-red-500/20 transition-all hover:bg-red-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Delete Selected
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="orders-content" :class="filterLoading ? 'opacity-40 scale-[0.99] pointer-events-none' : 'opacity-100 scale-100'" class="transition-all duration-300 grid grid-cols-1 gap-4">
                        @forelse($orders as $order)
                            <div
                                data-order-id="{{ $order->id }}"
                                :class="[
                                    loadingOrderId === {{ $order->id }} ? 'opacity-70 pointer-events-none scale-[0.99]' : '',
                                    isOrderSelected({{ $order->id }}) ? 'border-emerald-200 ring-2 ring-emerald-100 bg-emerald-50/20' : ''
                                ]"
                                class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-xl hover:shadow-emerald-500/5 transition-all group"
                            >
                                <div class="p-5 md:p-6">
                                    <div class="flex flex-col lg:flex-row justify-between gap-6">
                                        <div class="flex flex-col sm:flex-row gap-5">
                                            <label class="mt-1 inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:border-emerald-200 hover:bg-emerald-50">
                                                <input type="checkbox"
                                                       class="h-4 w-4 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500"
                                                       x-model="selectedOrderIds"
                                                       value="{{ $order->id }}">
                                            </label>

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
                                                    <button type="button"
                                                            @click="openDeleteDialog({
                                                                type: 'single-order',
                                                                title: 'Delete this order?',
                                                                message: 'Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }} will be permanently deleted from your restaurant orders.',
                                                                action: '{{ route('owner.order.destroy', $order) }}',
                                                                orderId: {{ $order->id }},
                                                                buttonLabel: 'Delete Order'
                                                            })"
                                                            class="ml-auto inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-white text-red-500 transition-all hover:bg-red-50"
                                                            title="Delete order">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
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

                                        <div class="flex w-full flex-col gap-4 border-t pt-4 sm:flex-row sm:items-center sm:justify-between lg:w-auto lg:flex-col lg:items-end lg:justify-center lg:border-t-0 lg:pt-0">
                                            <div class="lg:text-right">
                                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</p>
                                                <p class="text-2xl font-black outfit text-emerald-500">${{ number_format($order->total, 2) }}</p>
                                            </div>
                                            
                                            @if($order->status === 'pending')
                                                <div class="flex w-full flex-col gap-2 sm:w-auto">
                                                    <form method="POST" action="{{ route('owner.order.accept', $order) }}" class="order-status-form flex w-full flex-col gap-2 sm:flex-row sm:justify-end">
                                                        @csrf
                                                        <select name="estimated_prep_time" class="w-full text-sm border-0 bg-gray-50 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-gray-700 font-bold py-2 px-3 sm:w-auto">
                                                            <option value="15">15 min prep</option>
                                                            <option value="30">30 min prep</option>
                                                            <option value="45">45 min prep</option>
                                                            <option value="60">60 min prep</option>
                                                        </select>
                                                        <button type="submit" data-loading-key="accept-{{ $order->id }}" :disabled="loadingOrderId === {{ $order->id }}" class="w-full justify-center bg-emerald-500 hover:bg-emerald-400 disabled:hover:bg-emerald-500 text-white font-bold py-2 px-6 rounded-xl shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95 disabled:transform-none disabled:opacity-80 flex items-center gap-2 text-sm whitespace-nowrap sm:w-auto">
                                                            <span>Accept</span>
                                                            <span x-show="loadingButtonKey === 'accept-{{ $order->id }}'" x-cloak class="inline-flex">
                                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                                            </span>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" action="{{ route('owner.order.reject', $order) }}" class="order-status-form flex w-full flex-col gap-2 sm:flex-row sm:justify-end" x-data="{ showReason: false }">
                                                        @csrf
                                                        <input x-show="showReason" x-transition type="text" name="rejection_reason" placeholder="Reason (Optional)" class="w-full text-sm border-0 bg-red-50 text-red-600 placeholder-red-300 rounded-xl focus:ring-red-500 focus:border-red-500 py-2 px-3 sm:w-40">
                                                        <button type="button" x-show="!showReason" @click="showReason = true" class="w-full bg-white hover:bg-red-50 text-red-500 font-bold py-2 px-6 rounded-xl border border-red-100 transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2 text-sm sm:w-auto">
                                                            Reject
                                                        </button>
                                                        <button type="submit" data-loading-key="reject-{{ $order->id }}" :disabled="loadingOrderId === {{ $order->id }}" x-show="showReason" x-cloak class="w-full bg-red-500 hover:bg-red-400 disabled:hover:bg-red-500 text-white font-bold py-2 px-4 rounded-xl shadow-lg shadow-red-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95 disabled:transform-none disabled:opacity-80 flex items-center justify-center gap-2 text-sm whitespace-nowrap sm:w-auto">
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
                                                    <span class="text-[11px] font-bold text-gray-700">
                                                        {{ $item->name }}
                                                        @if($item->variant_label)
                                                            <span class="text-gray-400 font-medium">({{ $item->variant_label }})</span>
                                                        @endif
                                                    </span>
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
                        <div x-show="deleteDialog.open" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-950/50 backdrop-blur-sm">
                <div @click.outside="closeDeleteDialog()"
                     x-show="deleteDialog.open"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center">
                    
                    <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="deleteDialog.title"></h3>
                    <p class="text-gray-500 mb-8 text-sm" x-text="deleteDialog.message"></p>

                    <div class="flex items-center justify-center gap-3">
                        <button @click="closeDeleteDialog()" type="button" :disabled="deleteDialog.submitting" class="flex-1 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all text-sm disabled:opacity-50">
                            Cancel
                        </button>
                        <button @click="confirmDelete()" type="button" :disabled="deleteDialog.submitting" class="flex-1 px-4 py-2.5 bg-red-600 text-white font-bold rounded-xl hover:bg-red-500 transition-all text-sm disabled:opacity-50 flex items-center justify-center gap-2">
                            <span x-text="deleteDialog.buttonLabel"></span>
                            <span x-show="deleteDialog.submitting" x-cloak class="inline-flex">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                            </span>
                        </button>
                    </div>
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
                        searchTerm: @js((string) request('q', '')),
                        searchSuggestions: [],
                        searchSuggestionsLoading: false,
                        searchSuggestionsLoaded: false,
                        showSearchSuggestions: false,
                        activeSearchSuggestionIndex: -1,
                        searchDebounceTimer: null,
                        searchAbortController: null,
                        selectedOrderIds: [],
                        deleteDialog: {
                            open: false,
                            type: null,
                            title: '',
                            message: '',
                            action: '',
                            orderId: null,
                            orderIds: [],
                            buttonLabel: 'Confirm',
                            submitting: false,
                        },
                        toast: {
                            open: false,
                            message: '',
                            timeoutId: null,
                        },
                        latestOrderId: {{ $orders->isNotEmpty() ? $orders->first()->id : 0 }},
                        pollTimer: null,
                        pollInFlight: false,
                        pollingFallbackBound: false,
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
                            this.$nextTick(() => {
                                this.bindStatusForms();
                                this.syncSelectionWithVisibleOrders();
                            });

                            // Try initial subscription
                            this.usingEcho = this.subscribeToRealtime();

                            // IF it failed because Echo wasn't ready, WAIT for connection and retry
                            window.addEventListener('realtime:connected', () => {
                                if (!this.usingEcho) {
                                    this.usingEcho = this.subscribeToRealtime();
                                }
                            });

                            if (this.usingEcho) {
                                window.waitForRealtimeConnection?.(2500).then((connected) => {
                                    if (!connected && !this.usingEcho) {
                                        this.enablePollingFallback();
                                    }
                                });
                                return;
                            }

                            this.enablePollingFallback();
                        },

                        enablePollingFallback() {
                            if (this.pollingFallbackBound) {
                                this.schedulePoll(this.pollConfig.visible);
                                return;
                            }

                            this.pollingFallbackBound = true;

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
                                const channel = window.Echo.private('restaurant.{{ $restaurant->id }}.orders');
                                
                                channel.listen('.order.updated', (payload) => {
                                    this.handleRealtimeUpdate(payload)
                                });

                                channel.subscribed(() => {
                                    this.usingEcho = true;
                                    this.stopPolling();
                                    this.showToast('Real-time updates active');
                                });

                                channel.error((error) => {
                                    this.usingEcho = false;
                                    this.enablePollingFallback();
                                });

                                return true;
                            } catch (error) {
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

                            // Determine if we are actually SUBSCRIBED to the restaurant channel
                            const channel = window.Echo?.connector?.channels['private-restaurant.{{ $restaurant->id }}.orders'];
                            const isSubscribed = channel && channel.subscribed;

                            if (isSubscribed) {
                                this.usingEcho = true;
                            }

                            if (this.usingEcho || document.hidden || !navigator.onLine) {
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
                                const url = '{{ route("owner.dashboard.poll-new-orders") }}?since_id=' + this.latestOrderId + '&_t=' + Date.now();
                                const res = await fetch(url, {
                                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Cache-Control': 'no-cache' }
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
                                        await this.applyFilter(window.location.href, false);
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

                        visibleOrderIds() {
                            return Array.from(document.querySelectorAll('#orders-content [data-order-id]'))
                                .map((card) => String(card.getAttribute('data-order-id')))
                                .filter(Boolean);
                        },

                        isOrderSelected(orderId) {
                            return this.selectedOrderIds.includes(String(orderId));
                        },

                        areAllVisibleSelected() {
                            const visibleIds = this.visibleOrderIds();
                            return visibleIds.length > 0 && visibleIds.every((id) => this.selectedOrderIds.includes(id));
                        },

                        toggleSelectAllVisible(checked) {
                            const visibleIds = this.visibleOrderIds();

                            if (!visibleIds.length) {
                                return;
                            }

                            if (checked) {
                                this.selectedOrderIds = Array.from(new Set([
                                    ...this.selectedOrderIds.map((id) => String(id)),
                                    ...visibleIds,
                                ]));
                                return;
                            }

                            const visibleSet = new Set(visibleIds);
                            this.selectedOrderIds = this.selectedOrderIds.filter((id) => !visibleSet.has(String(id)));
                        },

                        clearSelection() {
                            this.selectedOrderIds = [];
                        },

                        syncSelectionWithVisibleOrders() {
                            const visibleSet = new Set(this.visibleOrderIds());
                            this.selectedOrderIds = this.selectedOrderIds
                                .map((id) => String(id))
                                .filter((id) => visibleSet.has(id));
                        },

                        openDeleteDialog(config) {
                            this.deleteDialog = {
                                open: true,
                                type: config.type ?? 'single-order',
                                title: config.title ?? 'Delete this order?',
                                message: config.message ?? 'This will permanently delete the order from your restaurant orders.',
                                action: config.action ?? '',
                                orderId: config.orderId ?? null,
                                orderIds: Array.isArray(config.orderIds) ? config.orderIds.map((id) => String(id)) : [],
                                buttonLabel: config.buttonLabel ?? 'Confirm',
                                submitting: false,
                            };
                        },

                        closeDeleteDialog(force = false) {
                            if (this.deleteDialog.submitting && !force) {
                                return;
                            }

                            this.deleteDialog = {
                                open: false,
                                type: null,
                                title: '',
                                message: '',
                                action: '',
                                orderId: null,
                                orderIds: [],
                                buttonLabel: 'Confirm',
                                submitting: false,
                            };
                        },

                        showToast(message) {
                            if (!message) {
                                return;
                            }

                            if (this.toast.timeoutId) {
                                clearTimeout(this.toast.timeoutId);
                            }

                            this.toast.open = true;
                            this.toast.message = message;
                            this.toast.timeoutId = setTimeout(() => this.hideToast(), 2600);
                        },

                        hideToast() {
                            if (this.toast.timeoutId) {
                                clearTimeout(this.toast.timeoutId);
                            }

                            this.toast.open = false;
                            this.toast.message = '';
                            this.toast.timeoutId = null;
                        },

                        clearSearchSuggestionState({ hidePanel = true } = {}) {
                            if (this.searchDebounceTimer) {
                                clearTimeout(this.searchDebounceTimer);
                                this.searchDebounceTimer = null;
                            }

                            if (this.searchAbortController) {
                                this.searchAbortController.abort();
                                this.searchAbortController = null;
                            }

                            this.searchSuggestions = [];
                            this.searchSuggestionsLoading = false;
                            this.searchSuggestionsLoaded = false;
                            this.activeSearchSuggestionIndex = -1;

                            if (hidePanel) {
                                this.showSearchSuggestions = false;
                            }
                        },

                        queueSearchSuggestions() {
                            const term = this.searchTerm.trim();
                            const currentUrl = new URL(window.location.href);

                            if (this.searchDebounceTimer) {
                                clearTimeout(this.searchDebounceTimer);
                            }

                            if (term.length === 0) {
                                this.clearSearchSuggestionState();

                                if (currentUrl.searchParams.has('q')) {
                                    this.searchDebounceTimer = setTimeout(() => {
                                        this.applySearch('');
                                    }, 150);
                                }

                                return;
                            }

                            if (term.length < 2) {
                                this.clearSearchSuggestionState();
                                return;
                            }

                            this.showSearchSuggestions = true;
                            this.searchSuggestions = [];
                            this.searchSuggestionsLoading = true;
                            this.searchSuggestionsLoaded = false;
                            this.activeSearchSuggestionIndex = -1;
                            this.searchDebounceTimer = setTimeout(() => {
                                this.fetchSearchSuggestions(term);
                            }, 280);
                        },

                        handleSearchFocus() {
                            if (this.searchTerm.trim().length < 2) {
                                return;
                            }

                            this.showSearchSuggestions = true;

                            if (!this.searchSuggestionsLoaded && !this.searchSuggestionsLoading) {
                                this.queueSearchSuggestions();
                            }
                        },

                        async fetchSearchSuggestions(term) {
                            const search = String(term ?? '').trim();

                            if (search.length < 2) {
                                this.clearSearchSuggestionState();
                                return;
                            }

                            if (this.searchAbortController) {
                                this.searchAbortController.abort();
                            }

                            const controller = new AbortController();
                            this.searchAbortController = controller;
                            this.searchSuggestionsLoading = true;

                            try {
                                const currentUrl = new URL(window.location.href);
                                const url = new URL(@js(route('owner.orders.suggestions')), window.location.origin);
                                url.searchParams.set('q', search);

                                ['status', 'filter'].forEach((key) => {
                                    const value = currentUrl.searchParams.get(key);
                                    if (value) {
                                        url.searchParams.set(key, value);
                                    }
                                });

                                const response = await fetch(url.toString(), {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json',
                                    },
                                    signal: controller.signal,
                                });

                                if (!response.ok) {
                                    throw new Error('Suggestions request failed');
                                }

                                const data = await response.json();

                                if (this.searchTerm.trim() !== search) {
                                    return;
                                }

                                this.searchSuggestions = Array.isArray(data.orders) ? data.orders : [];
                                this.searchSuggestionsLoaded = true;
                                this.activeSearchSuggestionIndex = this.searchSuggestions.length ? 0 : -1;
                                this.showSearchSuggestions = true;
                            } catch (error) {
                                if (error.name !== 'AbortError') {
                                    // Suggestion failure

                                    this.searchSuggestions = [];
                                    this.searchSuggestionsLoaded = true;
                                }
                            } finally {
                                if (this.searchAbortController === controller) {
                                    this.searchAbortController = null;
                                }

                                if (this.searchTerm.trim() === search) {
                                    this.searchSuggestionsLoading = false;
                                }
                            }
                        },

                        highlightNextSearchSuggestion() {
                            if (!this.searchSuggestions.length) {
                                return;
                            }

                            this.showSearchSuggestions = true;
                            this.activeSearchSuggestionIndex = (this.activeSearchSuggestionIndex + 1) % this.searchSuggestions.length;
                        },

                        highlightPreviousSearchSuggestion() {
                            if (!this.searchSuggestions.length) {
                                return;
                            }

                            this.showSearchSuggestions = true;
                            this.activeSearchSuggestionIndex = this.activeSearchSuggestionIndex <= 0
                                ? this.searchSuggestions.length - 1
                                : this.activeSearchSuggestionIndex - 1;
                        },

                        selectActiveSearchSuggestion() {
                            if (this.showSearchSuggestions && this.activeSearchSuggestionIndex >= 0) {
                                const suggestion = this.searchSuggestions[this.activeSearchSuggestionIndex];
                                if (suggestion) {
                                    this.chooseSearchSuggestion(suggestion);
                                    return;
                                }
                            }

                            this.applySearch(this.searchTerm);
                        },

                        chooseSearchSuggestion(suggestion) {
                            const searchValue = suggestion?.search_value ?? '';
                            this.searchTerm = searchValue;
                            this.showSearchSuggestions = false;
                            this.applySearch(searchValue);
                        },

                        clearOrderSearch() {
                            this.searchTerm = '';
                            this.clearSearchSuggestionState();
                            this.applySearch('');
                        },

                        applySearch(term) {
                            const targetUrl = new URL(window.location.href);
                            const search = String(term ?? '').trim();
                            this.searchTerm = search;
                            this.clearSearchSuggestionState();

                            if (search) {
                                targetUrl.searchParams.set('q', search);
                            } else {
                                targetUrl.searchParams.delete('q');
                            }

                            targetUrl.searchParams.delete('page');

                            this.applyFilter(targetUrl.toString(), true, { preserveSearch: false });
                        },

                        normalizeFilterUrl(url, options = {}) {
                            const normalizedUrl = new URL(url, window.location.href);
                            const currentUrl = new URL(window.location.href);
                            const preserveSearch = options.preserveSearch ?? true;

                            if (preserveSearch) {
                                const currentSearch = currentUrl.searchParams.get('q');

                                if (currentSearch && !normalizedUrl.searchParams.has('q')) {
                                    normalizedUrl.searchParams.set('q', currentSearch);
                                }
                            }

                            return normalizedUrl.toString();
                        },

                        buildDeleteActionUrl() {
                            if (!this.deleteDialog.action) {
                                return '';
                            }

                            const actionUrl = new URL(this.deleteDialog.action, window.location.href);

                            if (this.deleteDialog.type !== 'clear-all') {
                                return actionUrl.toString();
                            }

                            const currentUrl = new URL(window.location.href);

                            ['filter', 'status', 'sort', 'q'].forEach((key) => {
                                const value = currentUrl.searchParams.get(key);

                                if (value) {
                                    actionUrl.searchParams.set(key, value);
                                } else {
                                    actionUrl.searchParams.delete(key);
                                }
                            });

                            actionUrl.searchParams.delete('page');

                            return actionUrl.toString();
                        },

                        buildDeleteRequestOptions() {
                            const headers = {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            };

                            if (this.deleteDialog.type === 'selected-orders') {
                                headers['Content-Type'] = 'application/json';

                                return {
                                    method: 'DELETE',
                                    headers,
                                    body: JSON.stringify({
                                        order_ids: this.deleteDialog.orderIds,
                                    }),
                                };
                            }

                            return {
                                method: 'DELETE',
                                headers,
                            };
                        },

                        async confirmDelete() {
                            const deleteAction = this.buildDeleteActionUrl();

                            if (!deleteAction || this.deleteDialog.submitting) {
                                return;
                            }

                            this.deleteDialog.submitting = true;

                            if (this.deleteDialog.type === 'single-order' && this.deleteDialog.orderId) {
                                this.loadingOrderId = String(this.deleteDialog.orderId);
                            } else {
                                this.filterLoading = true;
                            }

                            try {
                                const res = await fetch(deleteAction, this.buildDeleteRequestOptions());

                                if (!res.ok) {
                                    throw new Error('Delete request failed');
                                }

                                const data = await res.json();
                                const currentUrl = new URL(window.location.href);
                                currentUrl.searchParams.delete('page');

                                if (this.deleteDialog.type === 'selected-orders') {
                                    const deletedSet = new Set(this.deleteDialog.orderIds.map((id) => String(id)));
                                    this.selectedOrderIds = this.selectedOrderIds.filter((id) => !deletedSet.has(String(id)));
                                } else if (this.deleteDialog.type === 'single-order' && this.deleteDialog.orderId) {
                                    this.selectedOrderIds = this.selectedOrderIds.filter((id) => String(id) !== String(this.deleteDialog.orderId));
                                }

                                this.closeDeleteDialog(true);
                                this.showToast(data.message ?? 'Orders updated successfully.');
                                await this.applyFilter(currentUrl.toString());
                            } catch (error) {
                                // Action failure

                                this.submitDeleteFallback();
                            } finally {
                                this.loadingOrderId = null;
                                this.filterLoading = false;
                                this.deleteDialog.submitting = false;
                            }
                        },

                        submitDeleteFallback() {
                            const deleteAction = this.buildDeleteActionUrl();

                            if (!deleteAction) {
                                return;
                            }

                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = deleteAction;
                            form.className = 'hidden';

                            const token = document.createElement('input');
                            token.type = 'hidden';
                            token.name = '_token';
                            token.value = '{{ csrf_token() }}';

                            const method = document.createElement('input');
                            method.type = 'hidden';
                            method.name = '_method';
                            method.value = 'DELETE';

                            form.appendChild(token);
                            form.appendChild(method);

                            if (this.deleteDialog.type === 'selected-orders') {
                                this.deleteDialog.orderIds.forEach((orderId) => {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = 'order_ids[]';
                                    input.value = orderId;
                                    form.appendChild(input);
                                });
                            }

                            document.body.appendChild(form);
                            form.submit();
                        },

                        initInteractiveSections(sections = []) {
                            if (!window.Alpine) {
                                return;
                            }

                            sections
                                .filter(Boolean)
                                .forEach((section) => window.Alpine.initTree(section));
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

                        async applyFilter(url, pushState = true, options = {}) {
                            const normalizedUrl = this.normalizeFilterUrl(url, options);
                            const nextUrl = new URL(normalizedUrl, window.location.href);
                            this.searchTerm = nextUrl.searchParams.get('q') ?? '';
                            this.clearSearchSuggestionState();
                            this.filterLoading = true;

                            try {
                                const fetchUrl = new URL(normalizedUrl, window.location.href);
                                fetchUrl.searchParams.set('_t', Date.now());

                                const res = await fetch(fetchUrl.toString(), {
                                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Cache-Control': 'no-cache' }
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
                                        window.history.pushState({}, '', normalizedUrl);
                                    }

                                    // Re-bind Alpine click handlers on new filter links
                                    this.$nextTick(() => {
                                        this.initInteractiveSections([
                                            currentFilters,
                                            currentContent,
                                            container.querySelector('#orders-pagination'),
                                        ]);
                                        this.syncSelectionWithVisibleOrders();

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
                                // Fetch failure

                                window.location.href = normalizedUrl;
                            }

                            this.filterLoading = false;

                            if (!this.usingEcho) {
                                this.schedulePoll(this.currentPollDelay());
                            }
                        },

                        async refreshSingleOrder(orderId) {
                            const container = document.getElementById('orders');
                            if (!container) return;

                            const fetchUrl = new URL(window.location.href);
                            fetchUrl.searchParams.set('_t', Date.now());

                            const res = await fetch(fetchUrl.toString(), {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Cache-Control': 'no-cache' }
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

                            const newFilters = newOrders.querySelector('#orders-filters');
                            const currentFilters = container.querySelector('#orders-filters');
                            if (newFilters && currentFilters) {
                                currentFilters.innerHTML = newFilters.innerHTML;
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
                                this.initInteractiveSections([
                                    currentFilters,
                                    currentContent,
                                    container.querySelector('#orders-pagination'),
                                ]);
                                this.syncSelectionWithVisibleOrders();

                                if (currentFilters) {
                                    container.querySelectorAll('#orders-filters .order-filter-link').forEach(link => {
                                        link.addEventListener('click', (e) => {
                                            e.preventDefault();
                                            this.applyFilter(link.href);
                                        });
                                    });

                                    const dropdownRoot = container.querySelector('#orders-filters [x-data]');
                                    if (dropdownRoot) {
                                        dropdownRoot.querySelectorAll('.order-filter-link').forEach(link => {
                                            link.addEventListener('click', (e) => {
                                                e.preventDefault();
                                                this.applyFilter(link.href);
                                            });
                                        });
                                    }
                                }

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
                                // Update failure

                                form.submit();
                            } finally {
                                this.loadingOrderId = null;
                                this.loadingButtonKey = null;
                            }
                        },

                        destroy() {
                            this.stopPolling();
                            this.clearSearchSuggestionState();
                            window.Echo?.leaveChannel('private-restaurant.{{ $restaurant->id }}.orders');
                        }
                    };
                }
            </script>

            
    </div>
</div>
@endsection
