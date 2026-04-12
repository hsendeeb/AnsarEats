@extends('layouts.app')

@section('content')
<style>
    .scroll-reveal {
        opacity: 0;
        transform: translate3d(0, var(--reveal-distance, 36px), 0) scale(0.985);
        filter: blur(8px);
        transition-property: opacity, transform, filter;
        transition-duration: var(--reveal-duration, 700ms);
        transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
        transition-delay: var(--reveal-delay, 0ms);
        will-change: opacity, transform, filter;
    }

    .scroll-reveal.is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0) scale(1);
        filter: blur(0);
    }

    @media (prefers-reduced-motion: reduce) {
        .scroll-reveal,
        .scroll-reveal.is-visible {
            opacity: 1;
            transform: none;
            filter: none;
            transition: none;
        }
    }
</style>
<div class="relative bg-white dark:bg-gray-900 transition-colors pt-6 md:pt-8 pb-0 overflow-x-clip z-[30]">
    <div class="container mx-auto px-4 relative z-10">
        <div class="flex flex-col items-center">
            <!-- Left Content -->
            <div class="w-full text-center lg:text-left scroll-reveal"
                 x-data="scrollReveal(0, 26)"
                 x-intersect.once.margin.-100px.0.0.0="reveal()"
                 :class="{ 'is-visible': shown }">
                
                
                <div class="w-full" 
                     x-data="{ 
                        query: '', 
                        results: { restaurants: [], meals: [] }, 
                        show: false,
                        loading: false,
                        search() {
                            const normalized = this.query ? this.query.trim() : '';
                            if (normalized.length) {
                                window.location.href = '{{ route('restaurants.index') }}?q=' + encodeURIComponent(normalized);
                            }
                        },
                        async fetchSuggestions() {
                            if (this.query.length < 2) {
                                this.results = { restaurants: [], meals: [] };
                                this.show = false;
                                return;
                            }
                            this.loading = true;
                            try {
                                const response = await fetch(`/search/suggestions?q=${encodeURIComponent(this.query)}`);
                                this.results = await response.json();
                                this.show = true;
                            } catch (e) {
                                console.error('Search error:', e);
                            } finally {
                                this.loading = false;
                            }
                        }
                     }"
                     @click.away="show = false">
                    
                    <div class="relative w-full group">
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" 
                                   x-model="query"
                                   name="q"
                                   @input.debounce.300ms="fetchSuggestions()"
                                   @focus="if(query.length >= 2) show = true"
                                   @keydown.enter.prevent="search()"
                                   placeholder="What are you eating today?" 
                                   class="w-full pl-12 pr-12 py-5 bg-gray-100 border-none focus:ring-4 focus:ring-emerald-500/20 rounded-3xl font-bold text-gray-900 placeholder-gray-400 shadow-inner transition-all">
                            
                            <button type="button"
                                    @click="search()"
                                    aria-label="Search restaurants"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-2xl text-gray-400 hover:text-emerald-500 transition-colors duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!query.trim().length">
                                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                            
                            <!-- Loading Spinner -->
                            <div x-show="loading" class="absolute right-4 top-1/2 -translate-y-1/2">
                                <svg class="animate-spin h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Suggestions Dropdown -->
                        <div x-show="show && (results.restaurants.length > 0 || results.meals.length > 0)" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             class="absolute left-0 right-0 mt-3 bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden z-[100]"
                             x-cloak>
                            
                            <div class="h-80 md:h-96 overflow-y-scroll overscroll-contain no-scrollbar pb-4" style="-webkit-overflow-scrolling: touch; touch-action: pan-y;">
                                <!-- Restaurants Section -->
                            <template x-if="results.restaurants.length > 0">
                                <div>
                                    <div class="px-5 py-3 bg-gray-50/50 text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-50">Restaurants</div>
                                    <div class="py-2">
                                        <template x-for="r in results.restaurants" :key="r.id">
                                            <a :href="r.url" class="flex items-center gap-4 px-5 py-3 hover:bg-emerald-50 transition-colors group">
                                                <div class="w-12 h-12 rounded-xl bg-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                                    <template x-if="r.logo">
                                                        <img :src="r.logo" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!r.logo">
                                                        <span class="text-lg font-black text-gray-400" x-text="r.name.charAt(0)"></span>
                                                    </template>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900 group-hover:text-emerald-600 transition-colors" x-text="r.name"></div>
                                                    <div class="text-xs text-gray-500 font-medium">Restaurant</div>
                                                </div>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Meals Section -->
                            <template x-if="results.meals.length > 0">
                                <div class="border-t border-gray-50">
                                    <div class="px-5 py-3 bg-gray-50/50 text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-50">Popular Meals</div>
                                    <div class="py-2">
                                        <template x-for="m in results.meals" :key="m.id">
                                            <a :href="m.url" class="flex items-center gap-4 px-5 py-3 hover:bg-emerald-50 transition-colors group">
                                                <div class="w-12 h-12 rounded-xl bg-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                                    <template x-if="m.image">
                                                        <img :src="m.image" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!m.image">
                                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                    </template>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-bold text-gray-900 group-hover:text-emerald-600 transition-colors" x-text="m.name"></div>
                                                    <div class="text-xs text-gray-500 font-medium" x-text="m.restaurant_name"></div>
                                                </div>
                                                <div class="font-black text-emerald-500 text-sm" x-text="'$' + m.price"></div>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            </div>
                        </div>
                    </div>
                </div>
                
               
            </div>

        </div>
    </div>
    
</div>

<section class="mt-4 md:mt-8 pt-4 md:pt-8 pb-4 bg-white relative z-20">
    <div class="container mx-auto px-4">
        <!-- Browse By Category Section -->
        <div class="mb-16 md:mb-20 scroll-reveal"
             x-data="scrollReveal(0, 30)"
             x-intersect.once.margin.-80px.0.0.0="reveal()"
             :class="{ 'is-visible': shown }">
            @php
                $homeCategories = \App\Http\Controllers\BrowseController::categories();
                $homeCategories = array_filter($homeCategories, fn($c) => $c['slug'] !== 'all');
            @endphp

            <!-- 3D Category Slider -->
            <div class="relative px-4 overflow-x-hidden" x-data="{ initSwiper() {
                new Swiper('.category-swiper', {
                    effect: 'coverflow',
                    grabCursor: true,
                    centeredSlides: true,
                    slidesPerView: 'auto',
                    initialSlide: 2,
                    coverflowEffect: {
                        rotate: 5,
                        stretch: 0,
                        depth: 100,
                        modifier: 2.5,
                        slideShadows: false,
                    },
                    loop: true,
                    autoplay: {
                        delay: 3000,
                        disableOnInteraction: false,
                    },
                    breakpoints: {
                        320: { slidesPerView: 2.4, spaceBetween: 8 },
                        640: { slidesPerView: 3.4, spaceBetween: 12 },
                        1024: { slidesPerView: 5, spaceBetween: 18 }
                    }
                });
            }}" x-init="initSwiper()">
                <div class="swiper category-swiper !overflow-hidden md:!overflow-visible">
                    <div class="swiper-wrapper">
                        @foreach($homeCategories as $cat)
                            <div class="swiper-slide !w-28 sm:!w-32 lg:!w-36">
                                <a href="{{ route('browse.index', ['category' => $cat['slug']]) }}"
                                   class="group flex flex-col items-center justify-center gap-1.5 w-full aspect-square rounded-full transition-all duration-300 cursor-pointer text-center">
                                    <span class="text-2xl sm:text-[1.7rem] leading-none transition-transform duration-300 group-hover:scale-110">{{ $cat['emoji'] }}</span>
                                    <span class="block font-black text-gray-900 group-hover:text-emerald-600 text-[11px] sm:text-xs leading-tight transition-colors">{{ $cat['label'] }}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                
                
            </div>
        </div>

        <div class="flex flex-wrap justify-between items-end mb-12 px-4 scroll-reveal"
             x-data="scrollReveal(0, 24)"
             x-intersect.once.margin.-80px.0.0.0="reveal()"
             :class="{ 'is-visible': shown }">
            <div>
                <h2 class="text-2xl md:text-3xl lg:text-4xl outfit font-black text-gray-900 tracking-tight">Trending Spots</h2>
            </div>
            <a href="{{ route('restaurants.index') }}" class="hidden sm:inline-block font-bold text-emerald-600 hover:text-emerald-500 flex items-center gap-2 group transition-all">
                <span>See all</span>
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>

        @if(($restaurants ?? collect())->isNotEmpty())
            <div class="md:hidden relative left-1/2 w-screen -translate-x-1/2 overflow-hidden scroll-reveal"
                 x-data="{
                    shown: false,
                    isInteracting: false,
                    animationFrame: null,
                    reveal() { this.shown = true; },
                    initTrendingSpotsMarquee() {
                        const viewport = this.$refs.trendingViewport;
                        const track = this.$refs.trendingTrack;

                        if (!viewport || !track) {
                            return;
                        }

                        const getLoopWidth = () => track.scrollWidth / 2;
                        let lastTimestamp = null;

                        const tick = (timestamp) => {
                            if (lastTimestamp === null) {
                                lastTimestamp = timestamp;
                            }

                            const delta = timestamp - lastTimestamp;
                            lastTimestamp = timestamp;

                            if (!this.isInteracting) {
                                viewport.scrollLeft += delta * 0.03;
                                const loopWidth = getLoopWidth();

                                if (loopWidth > 0 && viewport.scrollLeft >= loopWidth) {
                                    viewport.scrollLeft -= loopWidth;
                                }
                            }

                            this.animationFrame = requestAnimationFrame(tick);
                        };

                        const pause = () => {
                            this.isInteracting = true;
                        };

                        const resume = () => {
                            this.isInteracting = false;
                            const loopWidth = getLoopWidth();

                            if (loopWidth > 0 && viewport.scrollLeft >= loopWidth) {
                                viewport.scrollLeft -= loopWidth;
                            }
                        };

                        viewport.addEventListener('pointerdown', pause);
                        viewport.addEventListener('pointerup', resume);
                        viewport.addEventListener('pointercancel', resume);
                        viewport.addEventListener('touchstart', pause, { passive: true });
                        viewport.addEventListener('touchend', resume);
                        viewport.addEventListener('scroll', () => {
                            const loopWidth = getLoopWidth();

                            if (loopWidth > 0 && viewport.scrollLeft >= loopWidth) {
                                viewport.scrollLeft -= loopWidth;
                            }
                        }, { passive: true });

                        this.animationFrame = requestAnimationFrame(tick);
                    }
                 }"
                 x-init="initTrendingSpotsMarquee()"
                 x-intersect.once.margin.-60px.0.0.0="reveal()"
                 :class="{ 'is-visible': shown }">
                <div x-ref="trendingViewport"
                     class="overflow-x-auto no-scrollbar"
                     style="-webkit-overflow-scrolling: touch; scroll-behavior: auto; touch-action: pan-x; cursor: grab;">
                    <div x-ref="trendingTrack"
                         class="flex items-stretch gap-4"
                         style="width: max-content; flex-wrap: nowrap;">
                        @for($duplicate = 0; $duplicate < 2; $duplicate++)
                            @foreach($restaurants ?? [] as $restaurant)
                                <div class="shrink-0 pb-2" style="width: min(84vw, 24rem);">
                                    <a href="{{ route('restaurant.show', $restaurant) }}" class="block h-full relative">
                                        <div class="relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-md transition-all duration-300">
                                            <div class="relative flex h-48 items-center justify-center overflow-hidden bg-gray-100">
                                                @if($restaurant->logo)
                                                    <img alt="{{ $restaurant->name }}" src="{{ Storage::url($restaurant->logo) }}" class="h-full w-full object-cover transition-transform duration-700 ease-in-out"/>
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-400 via-indigo-500 to-purple-600 text-6xl font-black text-white outfit opacity-80">
                                                        {{ substr($restaurant->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/20 to-transparent"></div>

                                                <div class="absolute right-4 top-4">
                                                    <div class="flex items-center gap-1.5 rounded-full bg-white/90 px-4 py-1.5 text-xs font-bold text-gray-900 shadow-lg backdrop-blur-md">
                                                        <div class="h-2 w-2 rounded-full {{ $restaurant->isOpenNow() ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                                                        {{ $restaurant->isOpenNow() ? 'Open Now' : 'Closed' }}
                                                    </div>
                                                </div>

                                                <div class="absolute bottom-4 left-4 flex items-center gap-2">
                                                    <div class="flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-bold text-gray-900 shadow-lg">
                                                        <svg class="h-3 w-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                        @if(($restaurant->ratings_count ?? 0) > 0)
                                                            {{ number_format($restaurant->ratings_avg_rating ?? 0, 1) }}
                                                        @else
                                                            New
                                                        @endif
                                                    </div>
                                                    <div class="rounded-full bg-white/20 px-3 py-1 text-xs font-bold text-white shadow-lg backdrop-blur-md">
                                                        {{ $restaurant->menu_categories_count }} Categories
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex-auto p-6">
                                                <h6 class="mt-2 text-xl md:text-2xl font-black text-gray-900 outfit transition-colors">{{ $restaurant->name }}</h6>
                                                <p class="mt-2 mb-4 line-clamp-2 font-medium text-gray-500">
                                                    {{ $restaurant->description ?? 'Amazing food, cooked with perfection and delivered straight to you.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>

            <div class="hidden md:flex md:flex-wrap">
                @foreach($restaurants ?? [] as $restaurant)
                    <div class="w-full md:w-1/2 lg:w-1/3 px-4 mb-10 group scroll-reveal"
                         x-data="scrollReveal({{ ($loop->index % 3) * 90 }}, 34)"
                         x-intersect.once.margin.-60px.0.0.0="reveal()"
                         :class="{ 'is-visible': shown }">
                        <a href="{{ route('restaurant.show', $restaurant) }}" class="block h-full relative">
                            <div class="relative flex flex-col min-w-0 break-words bg-white w-full h-full shadow-md hover:shadow-2xl rounded-3xl transition-all duration-300 transform group-hover:-translate-y-2 border border-gray-100 overflow-hidden">
                                <div class="h-48 relative overflow-hidden bg-gray-100 flex items-center justify-center">
                                    @if($restaurant->logo)
                                        <img alt="{{ $restaurant->name }}" src="{{ Storage::url($restaurant->logo) }}" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-in-out"/>
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-emerald-400 via-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-6xl outfit opacity-80 group-hover:scale-110 transition-transform duration-700 ease-in-out">
                                            {{ substr($restaurant->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/20 to-transparent"></div>

                                    <div class="absolute top-4 right-4">
                                        <div class="bg-white/90 backdrop-blur-md text-gray-900 font-bold px-4 py-1.5 rounded-full text-xs shadow-lg flex items-center gap-1.5">
                                            <div class="w-2 h-2 rounded-full {{ $restaurant->isOpenNow() ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                                            {{ $restaurant->isOpenNow() ? 'Open Now' : 'Closed' }}
                                        </div>
                                    </div>

                                    <div class="absolute bottom-4 left-4 flex items-center gap-2">
                                        <div class="bg-white text-gray-900 font-bold px-3 py-1 rounded-full text-xs flex items-center gap-1 shadow-lg">
                                            <svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            @if(($restaurant->ratings_count ?? 0) > 0)
                                                {{ number_format($restaurant->ratings_avg_rating ?? 0, 1) }}
                                            @else
                                                New
                                            @endif
                                        </div>
                                        <div class="bg-white/20 backdrop-blur-md text-white font-bold px-3 py-1 rounded-full text-xs shadow-lg">
                                            {{ $restaurant->menu_categories_count }} Categories
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-auto p-6 relative">
                                    <h6 class="text-xl md:text-2xl font-black outfit text-gray-900 mt-2 group-hover:text-emerald-500 transition-colors">{{ $restaurant->name }}</h6>
                                    <p class="mt-2 mb-4 text-gray-500 font-medium line-clamp-2">
                                        {{ $restaurant->description ?? 'Amazing food, cooked with perfection and delivered straight to you.' }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="w-full py-20 text-center scroll-reveal"
                 x-data="scrollReveal(80, 30)"
                 x-intersect.once.margin.-60px.0.0.0="reveal()"
                 :class="{ 'is-visible': shown }">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-emerald-100 text-emerald-500 mb-6 group hover:rotate-12 transition-transform">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-2xl md:text-3xl font-black outfit text-gray-900 mb-2">No restaurants around yet!</h3>
                <p class="text-gray-500 text-lg font-medium">Be the first to partner with us or come back later.</p>

                <a href="{{ route('register') }}" class="inline-block mt-8 font-bold px-8 py-4 rounded-full bg-gray-900 text-white hover:bg-emerald-500 hover:shadow-xl hover:shadow-emerald-500/40 transition-all transform hover:-translate-y-1">Open Your Store</a>
            </div>
        @endif

        <div class="mt-8 flex justify-center lg:hidden scroll-reveal"
             x-data="scrollReveal(120, 22)"
             x-intersect.once.margin.-60px.0.0.0="reveal()"
             :class="{ 'is-visible': shown }">
            <a href="{{ route('restaurants.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white border border-gray-200 text-gray-900 font-black rounded-2xl shadow-sm hover:bg-gray-50 transition-all active:scale-95 group">
                <span>View All Spots</span>
                <svg class="w-5 h-5 text-emerald-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>

        @include('partials.restaurant-globe', ['restaurants' => $globeRestaurants ?? $restaurants ?? collect()])
        
        <!-- Trending Meals Section -->
        @if(isset($trendingMeals) && $trendingMeals->count() > 0)
        <div class="mt-32">
            <div class="flex flex-wrap justify-between items-end mb-12 px-4 scroll-reveal"
                 x-data="scrollReveal(0, 24)"
                 x-intersect.once.margin.-80px.0.0.0="reveal()"
                 :class="{ 'is-visible': shown }">
                <div>
                    <h2 class="text-2xl md:text-3xl lg:text-4xl outfit font-black text-gray-900 tracking-tight">Most Loved Meals</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($trendingMeals as $meal)
                    <div class="group scroll-reveal"
                         id="browse-card-[homemeal]-{{ $meal->id }}"
                         x-data="scrollReveal({{ ($loop->index % 3) * 100 }}, 30)"
                         x-intersect.once.margin.-60px.0.0.0="reveal()"
                         :class="{ 'is-visible': shown }">
                        <div class="bg-white border border-gray-100 rounded-2xl p-4 flex gap-4 hover:shadow-xl transition-shadow relative overflow-hidden h-full"
                             x-data="{
                                adding: false,
                                basePrice: parseFloat('{{ $meal->price }}'),
                                isOnSale: {{ $meal->is_on_sale ? 'true' : 'false' }},
                                salePrice: {{ Js::from($meal->sale_price) }},
                                discountPercentage: {{ Js::from($meal->saleDiscountPercentage()) }},
                                variants: @js(data_get($meal->variants, 'options', [])),
                                variantType: @js(data_get($meal->variants, 'type')),
                                selectedIndex: 0,
                                get hasVariants() {
                                    return this.variants && this.variants.length > 0;
                                },
                                formatCurrency(value) {
                                    const numericValue = parseFloat(value);
                                    return '$' + (Number.isNaN(numericValue) ? 0 : numericValue).toFixed(2);
                                },
                                calculateDiscountedPrice(price) {
                                    const numericPrice = parseFloat(price);
                                    if (Number.isNaN(numericPrice)) {
                                        return this.basePrice;
                                    }

                                    if (this.discountPercentage !== null && this.discountPercentage !== '' && !Number.isNaN(parseFloat(this.discountPercentage))) {
                                        return Math.max(numericPrice * (1 - (parseFloat(this.discountPercentage) / 100)), 0);
                                    }

                                    if (!this.hasVariants && this.salePrice !== null && this.salePrice !== '' && !Number.isNaN(parseFloat(this.salePrice)) && parseFloat(this.salePrice) < this.basePrice) {
                                        return parseFloat(this.salePrice);
                                    }

                                    return numericPrice;
                                },
                                get hasActiveSale() {
                                    return this.isOnSale && (
                                        (this.discountPercentage !== null && this.discountPercentage !== '' && !Number.isNaN(parseFloat(this.discountPercentage)) && parseFloat(this.discountPercentage) > 0)
                                        || (!this.hasVariants && this.salePrice !== null && this.salePrice !== '' && !Number.isNaN(parseFloat(this.salePrice)) && parseFloat(this.salePrice) < this.basePrice)
                                    );
                                },
                                get currentOption() {
                                    if (!this.hasVariants) return null;
                                    return this.variants[this.selectedIndex] || this.variants[0];
                                },
                                get currentOriginalPrice() {
                                    if (!this.currentOption) return this.basePrice;
                                    const value = parseFloat(this.currentOption.price);
                                    return Number.isNaN(value) ? this.basePrice : value;
                                },
                                get currentPrice() {
                                    return this.hasActiveSale
                                        ? this.calculateDiscountedPrice(this.currentOriginalPrice)
                                        : this.currentOriginalPrice;
                                },
                                get originalPrice() {
                                    return this.hasActiveSale ? this.currentOriginalPrice : null;
                                },
                                get currentLabel() {
                                    return this.currentOption ? this.currentOption.label : null;
                                },
                                get formattedPrice() {
                                    return this.formatCurrency(this.currentPrice);
                                },
                                get formattedOriginalPrice() {
                                    return this.originalPrice !== null ? this.formatCurrency(this.originalPrice) : '';
                                },
                                optionOriginalPrice(option) {
                                    const numericPrice = parseFloat(option?.price);
                                    return Number.isNaN(numericPrice) ? this.basePrice : numericPrice;
                                },
                                optionSalePrice(option) {
                                    return this.calculateDiscountedPrice(this.optionOriginalPrice(option));
                                },
                                formattedOptionOriginalPrice(option) {
                                    return this.formatCurrency(this.optionOriginalPrice(option));
                                },
                                formattedOptionSalePrice(option) {
                                    return this.formatCurrency(this.optionSalePrice(option));
                                },
                                async addToCart(itemId) {
                                    if (this.adding) return;
                                    this.adding = true;
                                    const token = document.querySelector('meta[name=csrf-token]')?.content;
                                    try {
                                        const payload = { menu_item_id: itemId, quantity: 1 };
                                        if (this.currentLabel) {
                                            payload.variant_label = this.currentLabel;
                                            payload.variant_price = this.currentPrice;
                                        }
                                        const res = await fetch('{{ route('cart.add') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': token,
                                                'X-Requested-With': 'XMLHttpRequest'
                                            },
                                            body: JSON.stringify(payload)
                                        });
                                        const data = await res.json();
                                        if (res.ok) {
                                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
                                            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Added to cart!', type: 'success' } }));
                                        } else {
                                            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: data.message || 'Could not add item.', type: 'error' } }));
                                        }
                                    } catch (e) {
                                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Network error. Please try again.', type: 'error' } }));
                                    } finally {
                                        this.adding = false;
                                    }
                                }
                             }">
                            <!-- Meal Image -->
                            <a href="{{ route('restaurant.show', $meal->menuCategory->restaurant) }}#meal-{{ $meal->id }}" class="w-24 h-24 flex-shrink-0">
                                <div class="w-24 h-24 flex-shrink-0 bg-gray-100 rounded-xl overflow-hidden relative" id="browse-img-[homemeal]-{{ $meal->id }}">
                                    @if($meal->image)
                                        <img alt="{{ $meal->name }}" src="{{ Storage::url($meal->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                    @if(!$meal->is_available)
                                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center backdrop-blur-[1px] z-10">
                                            <span class="text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 bg-gray-900/80 rounded border border-gray-700">Sold out</span>
                                        </div>
                                    @elseif(!$meal->menuCategory->restaurant->isOpenNow())
                                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center backdrop-blur-[1px] z-10">
                                            <span class="text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 bg-red-900/80 rounded border border-red-700">Closed</span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                            
                            <!-- Details -->
                            <div class="flex-1 min-w-0 flex flex-col justify-between">
                                <div>
                                    <div class="flex flex-wrap items-start justify-between gap-x-2 gap-y-1">
                                        <a href="{{ route('restaurant.show', $meal->menuCategory->restaurant) }}#meal-{{ $meal->id }}" class="min-w-0 flex-1">
                                            <h4 class="font-bold text-lg text-gray-900 group-hover:text-emerald-600 transition-colors leading-tight break-words flex items-center gap-2">
                                                {{ $meal->name }}
                                                @if($meal->is_featured)
                                                    <span class="inline-flex items-center text-amber-500 bg-amber-50 px-1.5 py-0.5 rounded text-[9px] uppercase font-black tracking-widest border border-amber-100 whitespace-nowrap">Featured</span>
                                                @endif
                                            </h4>
                                            <div class="hidden sm:flex items-center gap-1.5 mt-1">
                                                <div class="w-4 h-4 rounded-full overflow-hidden bg-gray-100 flex-shrink-0 border border-gray-200">
                                                    @if($meal->menuCategory->restaurant->logo)
                                                        <img src="{{ Storage::url($meal->menuCategory->restaurant->logo) }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center bg-emerald-100 text-emerald-600 text-[8px] font-bold">{{ substr($meal->menuCategory->restaurant->name,0,1) }}</div>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] font-bold text-gray-500 truncate">{{ $meal->menuCategory->restaurant->name }}</span>
                                                <span class="text-gray-300 mx-1">&bull;</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $meal->menuCategory->name }}</span>
                                            </div>
                                        </a>
                                        <div class="shrink-0 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <span x-show="hasActiveSale" x-cloak class="text-sm font-bold text-gray-400 line-through" x-text="formattedOriginalPrice"></span>
                                                <span class="font-black text-emerald-500 whitespace-nowrap" x-text="formattedPrice">${{ number_format($meal->price, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if($meal->description)
                                        <p class="text-sm text-gray-500 font-medium line-clamp-2 mt-2 break-words">{{ $meal->description }}</p>
                                    @endif
                                </div>

                                <div x-show="hasVariants" x-cloak class="mt-3">
                                    <p class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1" x-text="variantType ? variantType : 'Option'"></p>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(opt, idx) in variants" :key="idx">
                                            <button type="button"
                                                    @click="selectedIndex = idx"
                                                    class="px-3 py-1.5 rounded-full text-[11px] font-semibold border transition-all"
                                                    :class="selectedIndex === idx ? 'bg-emerald-500 text-white border-emerald-500 shadow-sm' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'">
                                                <span x-text="opt.label"></span>
                                                <span x-show="hasActiveSale" x-cloak class="ml-1 text-[10px] text-gray-400 line-through" x-text="formattedOptionOriginalPrice(opt)"></span>
                                                <span class="ml-1 opacity-80" :class="hasActiveSale ? 'font-bold text-emerald-500 opacity-100' : ''" x-text="hasActiveSale ? formattedOptionSalePrice(opt) : formattedOptionOriginalPrice(opt)"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between mt-3">
                                    <div class="text-xs font-bold text-gray-400 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                        {{ $meal->order_items_count }} orders
                                    </div>
                                    
                                    @if(!$meal->is_available || !$meal->menuCategory->restaurant->isOpenNow())
                                        <span class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full border border-gray-200">
                                            {{ !$meal->is_available ? 'Sold out' : 'Closed' }}
                                        </span>
                                    @elseif(Auth::id() === ($meal->menuCategory->restaurant->user_id ?? null))
                                        <span class="text-[10px] font-bold text-amber-500 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-100">Own Restaurant</span>
                                    @else
                                        <button
                                            type="button"
                                            @click="addToCart({{ $meal->id }})"
                                            :disabled="adding"
                                            class="bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white w-9 h-9 rounded-full flex items-center justify-center transition-all transform hover:scale-110 active:scale-95 shadow-sm hover:shadow-lg hover:shadow-emerald-500/30 disabled:opacity-60 disabled:cursor-not-allowed">
                                            <svg x-show="!adding" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                            <svg x-show="adding" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mt-28"
             x-data="allStoresFeed({
                items: @js($initialAllStores ?? []),
                nextPage: @js($allStoresNextPage ?? null),
                endpoint: '{{ route('home.stores') }}'
             })">
            <div class="flex flex-wrap justify-between items-end mb-12 px-4 scroll-reveal"
                 x-data="scrollReveal(0, 24)"
                 x-intersect.once.margin.-80px.0.0.0="reveal()"
                 :class="{ 'is-visible': shown }">
                <div>
                    <h2 class="text-2xl md:text-3xl lg:text-4xl outfit font-black text-gray-900 tracking-tight">All Stores</h2>

                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4">
                <template x-for="(store, index) in stores" :key="store.id">
                    <article class="group bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300"
                             x-intersect.margin.250px="handleLastVisible(index)">
                        <a :href="store.url" class="block h-full">
                            <div class="relative h-52 bg-gray-100 overflow-hidden">
                                <template x-if="store.logo_url">
                                    <img :src="store.logo_url" :alt="store.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                </template>
                                <template x-if="!store.logo_url">
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-emerald-400 via-teal-500 to-cyan-500 text-white text-6xl font-black outfit">
                                        <span x-text="store.name.charAt(0)"></span>
                                    </div>
                                </template>

                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/75 via-gray-900/20 to-transparent"></div>

                                <div class="absolute top-4 right-4">
                                    <div class="flex items-center gap-1.5 rounded-full px-4 py-1.5 text-xs font-bold shadow-lg backdrop-blur-md"
                                         :class="store.is_open_now ? 'bg-white/90 text-gray-900' : 'bg-red-50/90 text-red-600'">
                                        <div class="h-2 w-2 rounded-full" :class="store.is_open_now ? 'bg-emerald-500' : 'bg-red-500'"></div>
                                        <span x-text="store.is_open_now ? 'Open Now' : 'Closed'"></span>
                                    </div>
                                </div>

                                <div class="absolute bottom-4 left-4 flex items-center gap-2">
                                    <div class="rounded-full bg-white px-3 py-1 text-xs font-bold text-gray-900 shadow-lg flex items-center gap-1">
                                        <svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        <span x-text="store.rating ?? 'New'"></span>
                                    </div>
                                    <div class="rounded-full bg-white/20 px-3 py-1 text-xs font-bold text-white shadow-lg backdrop-blur-md">
                                        <span x-text="`${store.menu_categories_count} Categories`"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                <h3 class="text-2xl font-black outfit text-gray-900 group-hover:text-emerald-500 transition-colors" x-text="store.name"></h3>
                                <p class="mt-3 text-sm text-gray-500 font-medium line-clamp-2 min-h-[2.75rem]"
                                   x-text="store.description || 'Fresh food, signature flavors, and a storefront ready to explore.'"></p>
                            </div>
                        </a>
                    </article>
                </template>
            </div>

            <!-- Loading Spinner & Intersection Trigger -->
            <div x-show="nextPage" 
                 class="mt-12 flex justify-center py-8">
                <div x-show="loading" class="flex flex-col items-center gap-3">
                    <div class="w-10 h-10 rounded-full border-4 border-emerald-400 border-t-transparent animate-spin"></div>
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">Loading more stores...</span>
                </div>
            </div>

            <div x-show="!stores.length" x-cloak class="px-4">
                <div class="rounded-3xl border border-dashed border-gray-200 bg-white/80 p-8 text-center text-gray-500 font-medium">
                    No stores are available right now.
                </div>
            </div>
        </div>

        @if(!auth()->check() || !auth()->user()->restaurant)
        <div class="mt-28 w-full" x-data="{
            hasCounted: false,
            revealed: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            menuReady: 0,
            reach: 0,
            profileProgress: 0,
            animateCounter(key, target, duration = 1400) {
                const start = performance.now();
                const step = (now) => {
                    const progress = Math.min((now - start) / duration, 1);
                    this[key] = Math.floor(progress * target);
                    if (progress < 1) {
                        requestAnimationFrame(step);
                    } else {
                        this[key] = target;
                    }
                };
                requestAnimationFrame(step);
            },
            startCounters() {
                if (this.hasCounted) return;
                this.hasCounted = true;
                this.animateCounter('menuReady', 48, 1400);
                this.animateCounter('reach', 320, 1600);
                this.animateCounter('profileProgress', 88, 1500);
            },
            revealSection() {
                this.revealed = true;
                this.startCounters();
            }
        }"
        x-intersect.once.margin.-80px.0.0.0="revealSection()"
        class="scroll-reveal"
        style="--reveal-delay: 40ms; --reveal-distance: 34px;"
        :class="{ 'is-visible': revealed }">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center rounded-[2.5rem] border border-emerald-100 bg-white shadow-[0_30px_80px_-30px_rgba(16,185,129,0.25)] overflow-hidden">
                <div class="p-8 md:p-12 lg:p-14">
                    <p class="text-xs font-black uppercase tracking-[0.35em] text-emerald-500 mb-4">Partner With Us</p>
                    <h2 class="text-2xl md:text-4xl lg:text-5xl outfit font-black text-gray-900 tracking-tight leading-tight">
                        Turn your kitchen into the next favorite spot in town.
                    </h2>
                    <p class="mt-5 text-gray-500 font-medium text-lg leading-relaxed max-w-xl">
                        Join AnsarEats to reach more customers, manage orders with ease, and grow your brand with a storefront built for fast discovery, smooth menu updates, and reliable delivery-ready operations.
                    </p>

                    <div class="mt-8">
                        <a href="{{ auth()->check() ? route('partner.with.us') : route('register') }}" class="inline-flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-gray-900 text-white font-black hover:bg-emerald-500 transition-all hover:shadow-xl hover:shadow-emerald-500/20 transform hover:-translate-y-0.5 active:scale-95">
                            <span>Start your restaurant</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                </div>

                <div class="relative min-h-[460px] md:min-h-[420px] bg-gradient-to-br from-emerald-100 via-white to-cyan-100 p-6 sm:p-8 md:p-12 overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-emerald-300/30 blur-3xl"></div>
                    <div class="absolute -bottom-16 -left-10 w-56 h-56 rounded-full bg-cyan-300/30 blur-3xl"></div>

                    <div class="relative h-full flex items-center justify-center">
                        <div class="w-full max-w-2xl">
                            <div class="grid grid-cols-1 gap-6 items-stretch">
                                <div class="rounded-[2rem] bg-white/75 backdrop-blur-md shadow-[0_20px_50px_-25px_rgba(15,23,42,0.35)] p-4 sm:p-5 flex flex-col">
                                    <div class="mb-3">
                                        <p class="text-xs font-black uppercase tracking-[0.3em] text-emerald-600">Watch Your Metrics</p>
                                        <h3 class="mt-2 text-xl font-black outfit text-gray-900">Track growth as your restaurant scales.</h3>
                                    </div>
                                    <dotlottie-player src="https://lottie.host/ac464139-d495-41a2-95b6-62404745f9d8/BwZr3VLDTK.lottie" background="transparent" speed="1" class="w-full h-[260px] sm:h-[300px] md:h-[320px]" loop autoplay></dotlottie-player>
                                </div>

                                <div class="relative">
                                    <div class="absolute -left-4 top-10 w-24 h-24 rounded-[2rem] bg-white/80 backdrop-blur-md shadow-lg rotate-[-8deg] flex items-center justify-center">
                                        <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4"></path></svg>
                                    </div>

                                    <div class="relative rounded-[2.5rem] bg-white shadow-[0_25px_60px_-20px_rgba(15,23,42,0.28)] p-5 sm:p-6">
                                        <div class="flex items-center justify-between mb-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-14 h-14 rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white flex items-center justify-center shadow-lg">
                                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 7h16M7 7V6a3 3 0 013-3h4a3 3 0 013 3v1M7 11h10M8 15h3"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 21h10a2 2 0 002-2V7H5v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                                <div>
                                                    <p class="font-black text-gray-900">Create Restaurant</p>
                                                    <p class="text-sm font-medium text-gray-500">Launch your digital storefront</p>
                                                </div>
                                            </div>
                                            <div class="px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-widest">Simple Setup</div>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="rounded-2xl border border-gray-100 p-4 bg-gray-50">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-xs font-black uppercase tracking-widest text-gray-400">Restaurant Profile</span>
                                                    <span class="text-xs font-bold text-emerald-600">Ready</span>
                                                </div>
                                                <div class="h-3 rounded-full bg-gray-200 overflow-hidden">
                                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-[width] duration-200" :style="`width: ${profileProgress}%`"></div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4">
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Menu Ready</p>
                                                    <p class="mt-3 text-xl md:text-2xl font-black outfit text-gray-900" x-text="menuReady">0</p>
                                                    <p class="text-xs font-medium text-gray-500">items prepared</p>
                                                </div>
                                                <div class="rounded-2xl bg-cyan-50 border border-cyan-100 p-4">
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-cyan-600">Reach</p>
                                                    <p class="mt-3 text-xl md:text-2xl font-black outfit text-gray-900" x-text="'+' + reach + '%'">+0%</p>
                                                    <p class="text-xs font-medium text-gray-500">more discovery</p>
                                                </div>
                                            </div>

                                            <div class="rounded-[2rem] bg-gray-900 p-5 text-white">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-11 h-11 rounded-2xl bg-white/10 flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 17l6-6 4 4 7-7"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 8h6v6"></path></svg>
                                                    </div>
                                                    <div>
                                                        <p class="font-black">Built to help restaurants grow</p>
                                                        <p class="text-sm text-white/65 font-medium">Requests, approvals, menu control, and order tracking in one flow.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        if (window.homeScrollRevealRegistered) {
            return;
        }

        window.homeScrollRevealRegistered = true;

        Alpine.data('scrollReveal', (delay = 0, distance = 36) => ({
            shown: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            init() {
                this.$el.style.setProperty('--reveal-delay', `${delay}ms`);
                this.$el.style.setProperty('--reveal-distance', `${distance}px`);
            },
            reveal() {
                this.shown = true;
            },
        }));

        Alpine.data('allStoresFeed', ({ items = [], nextPage = null, endpoint }) => ({
            stores: items,
            nextPage,
            endpoint,
            loading: false,
            async loadMore() {
                if (this.loading || !this.nextPage) {
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch(`${this.endpoint}?page=${this.nextPage}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to load more stores.');
                    }

                    const payload = await response.json();
                    this.stores = [...this.stores, ...(payload.data || [])];
                    this.nextPage = payload.next_page;
                } catch (error) {
                    console.error(error);
                } finally {
                    this.loading = false;
                }
            },
            handleLastVisible(index) {
                if (index === this.stores.length - 1) {
                    this.loadMore();
                }
            },
        }));
    });
</script>
@endpush
