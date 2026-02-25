@extends('layouts.app')

@section('content')
<div class="bg-white py-12 border-b border-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div class="max-w-2xl">
                <h1 class="text-5xl md:text-6xl font-black outfit text-gray-900 tracking-tighter mb-4">
                    Explore <span class="text-emerald-500">All Restaurants</span>
                </h1>
                <p class="text-lg text-gray-500 font-medium leading-relaxed">
                    Discover the best flavors in town.
                </p>
            </div>
            
            <div class="bg-gray-50 p-2 rounded-[2.5rem] border border-gray-100 shadow-sm flex items-center min-w-[300px]">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-emerald-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div class="flex-1 px-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Status</p>
                    <p class="text-sm font-bold text-gray-900">{{ $restaurants->count() }} places found</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="py-12 bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4">
        <!-- Top Filters Bar -->
        <div class="mb-12 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4 px-6 py-3 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Sort By</span>
                <select class="bg-transparent border-none focus:ring-0 text-sm font-bold text-gray-900 cursor-pointer">
                    <option>Recommended</option>
                    <option>Most Popular</option>
                    <option>Fastest Delivery</option>
                </select>
            </div>

            <div class="flex items-center gap-4" x-data="{ open: false }">
                <div class="relative">
                    <button @click="open = !open" 
                            class="flex items-center gap-3 px-6 py-3 bg-white rounded-2xl border border-gray-100 shadow-sm hover:border-emerald-500 transition-all group">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="text-sm font-bold text-gray-700">{{ request('location') ?: 'Select Location' }}</span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="absolute right-0 mt-3 w-64 bg-white rounded-3xl shadow-2xl border border-gray-100 p-2 z-50"
                         x-cloak>
                        
                        <a href="{{ route('restaurants.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all {{ !request('location') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'text-gray-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                            All Locations
                        </a>
                        @foreach($locations as $location)
                            <a href="{{ route('restaurants.index', ['location' => $location]) }}" 
                               class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all {{ request('location') == $location ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'text-gray-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                                {{ $location }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Listing -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($restaurants as $restaurant)
                <div class="group">
                    <a href="{{ route('restaurant.show', $restaurant) }}" class="block h-full relative">
                        <div class="bg-white rounded-[2.5rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-2xl transition-all duration-300 transform group-hover:-translate-y-2 flex flex-col h-full">
                            <!-- Image / Logo -->
                            <div class="h-48 relative overflow-hidden bg-gray-100">
                                @if($restaurant->logo)
                                    <img alt="{{ $restaurant->name }}" src="{{ Storage::url($restaurant->logo) }}" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-in-out"/>
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-emerald-400 via-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-6xl outfit opacity-80 group-hover:scale-110 transition-transform duration-700 ease-in-out">
                                        {{ substr($restaurant->name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/60 via-gray-900/20 to-transparent"></div>
                                
                                <div class="absolute top-4 right-4">
                                    <div class="bg-white/90 backdrop-blur-md text-gray-900 font-bold px-4 py-1.5 rounded-full text-xs shadow-lg flex items-center gap-1.5">
                                        <div class="w-2 h-2 rounded-full {{ $restaurant->is_open ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                                        {{ $restaurant->is_open ? 'Open Now' : 'Closed' }}
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 flex-1 flex flex-col">
                                <h3 class="text-xl font-black outfit text-gray-900 group-hover:text-emerald-500 transition-colors mb-2">{{ $restaurant->name }}</h3>
                                <p class="text-sm text-gray-500 font-medium mb-6 line-clamp-2">{{ $restaurant->description ?? 'Amazing food, cooked with perfection and delivered straight to you.' }}</p>
                                
                                <div class="mt-auto pt-6 border-t border-gray-50 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-1.5 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            {{ head(explode(',', $restaurant->address)) }}
                                        </div>
                                    </div>
                                    <div class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-3 py-1 rounded-full uppercase tracking-tighter">
                                        {{ $restaurant->menu_categories_count }} menus
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-300">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h3 class="text-3xl font-black outfit text-gray-900 mb-2">No results found</h3>
                    <p class="text-gray-500 text-lg font-medium">Try adjusting your filters or search for something else.</p>
                    <a href="{{ route('restaurants.index') }}" class="inline-block mt-8 font-bold px-8 py-4 bg-gray-900 text-white rounded-2xl hover:bg-emerald-500 transition-all">View All Places</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
