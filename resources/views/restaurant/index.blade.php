@extends('layouts.app')

@section('content')
<div class="bg-white py-12 border-b border-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
             <h1 class="text-4xl font-black outfit text-gray-900">Explore Restaurants</h1>
           
            
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
        @php
            $activeSort = in_array($currentSort ?? request('sort'), ['rating_desc', 'rating_asc'], true)
                ? ($currentSort ?? request('sort'))
                : null;
            $sortLabels = [
                'rating_desc' => 'Highest Rated',
                'rating_asc' => 'Lowest Rated',
            ];
            $sortBaseParams = array_filter([
                'q' => request('q'),
                'location' => request('location'),
            ]);
        @endphp

        <!-- Top Filters Bar -->
        <div class="mb-12 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-6 overflow-visible">
            <div class="flex w-full md:w-auto items-center justify-end gap-4 md:gap-6 overflow-visible relative z-20">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                            type="button"
                            class="inline-flex items-center justify-end gap-2 text-sm font-semibold transition-colors {{ $activeSort ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 6h18M6 12h12M10 18h4"></path></svg>
                        <span>{{ $sortLabels[$activeSort] ?? 'Sort By' }}</span>
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
                         class="absolute left-0 mt-3 w-56 max-w-[calc(100vw-2rem)] bg-white rounded-2xl border border-gray-100 shadow-2xl shadow-gray-900/10 py-2 z-50 overflow-hidden">
                        <p class="px-4 pt-2 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sort By</p>
                        <a href="{{ route('restaurants.index', array_filter($sortBaseParams + ['sort' => 'rating_desc'])) }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors {{ $activeSort === 'rating_desc' ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 17l-5 3 1-5.5L4 10l5.6-.8L12 4l2.4 5.2L20 10l-4 4.5 1 5.5-5-3z"></path></svg>
                            Highest Rated
                            @if($activeSort === 'rating_desc')
                                <svg class="w-4 h-4 ml-auto text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            @endif
                        </a>
                        <a href="{{ route('restaurants.index', array_filter($sortBaseParams + ['sort' => 'rating_asc'])) }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors {{ $activeSort === 'rating_asc' ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7l5 3-1 5.5 4 4.5-5.6.8L12 20l-2.4-5.2L4 14l4-4.5L7 4l5 3z"></path></svg>
                            Lowest Rated
                            @if($activeSort === 'rating_asc')
                                <svg class="w-4 h-4 ml-auto text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            @endif
                        </a>
                        @if($activeSort)
                            <div class="mx-3 my-1.5 border-t border-gray-100"></div>
                            <a href="{{ route('restaurants.index', $sortBaseParams) }}"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Clear Sort
                            </a>
                        @endif
                    </div>
                </div>

                <div class="relative" x-data="restaurantLocationFilter(@js($locations))" @click.outside="close()">
                    <button @click="toggle()"
                            type="button"
                            class="inline-flex items-center justify-end gap-2 text-sm font-semibold transition-colors {{ request('location') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="truncate max-w-[9rem] md:max-w-[10rem]">{{ request('location') ?: 'Location' }}</span>
                        <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="open"
                         x-effect="if (open) { $nextTick(() => $refs.locationSearch?.focus()); }"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         x-cloak
                         class="absolute right-0 mt-3 w-72 max-w-[calc(100vw-2rem)] bg-white rounded-2xl border border-gray-100 shadow-2xl shadow-gray-900/10 py-2 z-50 overflow-hidden">
                        <p class="px-4 pt-2 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-widest">Locations</p>
                        <div class="px-3 pb-3">
                            <label for="location-search" class="sr-only">Search locations</label>
                            <div class="flex items-center gap-2 px-4 py-3 rounded-2xl border border-gray-100 bg-gray-50">
                                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <input id="location-search"
                                       x-ref="locationSearch"
                                       x-model="search"
                                       @input="scheduleFilter()"
                                       @keydown.escape.stop.prevent="close()"
                                       type="text"
                                       placeholder="Search locations..."
                                       class="w-full bg-transparent border-none shadow-none focus:outline-none focus:ring-0 focus:border-transparent focus:shadow-none text-sm font-bold text-gray-900 placeholder:text-gray-400 p-0"
                                       style="outline: none !important; box-shadow: none !important; -webkit-box-shadow: none !important;">
                            </div>
                        </div>

                        <div class="max-h-72 overflow-y-auto overscroll-contain px-2 pb-2">
                            <a href="{{ route('restaurants.index', array_filter(['q' => request('q'), 'sort' => request('sort')])) }}"
                               class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ !request('location') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span>All Locations</span>
                                @if(!request('location'))
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                            </a>

                            <template x-if="filteredLocations.length">
                                <div class="space-y-1">
                                    <template x-for="location in filteredLocations" :key="location">
                                        <a :href="locationUrl(location)"
                                           class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                                           :class="selectedLocation === location ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50'">
                                            <span x-text="location"></span>
                                            <svg x-show="selectedLocation === location"
                                                 x-cloak
                                                 class="w-4 h-4 text-emerald-500"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <p x-show="!filteredLocations.length"
                               class="px-4 py-5 text-sm font-bold text-gray-400 text-center"
                               x-cloak>
                                No locations match your search.
                            </p>
                        </div>
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
                                        <div class="w-2 h-2 rounded-full {{ $restaurant->isOpenNow() ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                                        {{ $restaurant->isOpenNow() ? 'Open Now' : 'Closed' }}
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 flex-1 flex flex-col">
                                <h3 class="text-xl font-black outfit text-gray-900 group-hover:text-emerald-500 transition-colors mb-2">{{ $restaurant->name }}</h3>
                                <p class="text-sm text-gray-500 font-medium mb-4 line-clamp-2">{{ $restaurant->description ?? 'Amazing food, cooked with perfection and delivered straight to you.' }}</p>
                                
                                {{-- Star Rating --}}
                                <div class="mb-4">
                                    @include('layouts.partials.star-rating', ['rating' => round($restaurant->ratings_avg_rating ?? 0, 1), 'count' => $restaurant->ratings_count ?? 0, 'size' => 'sm'])
                                </div>
                                
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

@push('scripts')
<script>
    window.restaurantLocationFilter = function(locations) {
        return {
            open: false,
            search: '',
            filteredLocations: [...locations],
            selectedLocation: @js(request('location')),
            debounceTimer: null,
            queryParams: @js(array_filter(['q' => request('q'), 'sort' => request('sort')])),
            toggle() {
                if (this.open) {
                    this.close();
                    return;
                }

                this.open = true;
                this.filteredLocations = [...locations];
            },
            close() {
                this.open = false;
                this.search = '';
                clearTimeout(this.debounceTimer);
                this.filteredLocations = [...locations];
            },
            scheduleFilter() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    const term = this.search.trim().toLowerCase();

                    this.filteredLocations = !term
                        ? [...locations]
                        : locations.filter((location) => location.toLowerCase().includes(term));
                }, 250);
            },
            locationUrl(location) {
                const url = new URL(@js(route('restaurants.index')), window.location.origin);
                const params = new URLSearchParams(this.queryParams);

                params.set('location', location);
                url.search = params.toString();

                return url.toString();
            },
        };
    };
</script>
@endpush
