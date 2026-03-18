@extends('layouts.app')

@section('content')
<div class="relative bg-white dark:bg-gray-900 transition-colors pt-6 md:pt-8 pb-32 overflow-x-clip z-[30]" x-data="{ 
    activeLottie: 0,
    animations: [
        'https://lottie.host/a52b1c0b-6390-42ee-ad5c-fca5db1b7dfa/hcsHrorguN.lottie',
        'https://lottie.host/af111bea-9c55-43f3-bb88-523d8d8d7155/1zjR1EoVso.lottie',
        'https://lottie.host/c18f0392-ca5f-4ed6-9bde-04d2bec9f2ce/GMwQJXw8QD.lottie',
    ],
    nextAnimation() {
        this.activeLottie = (this.activeLottie + 1) % this.animations.length;
    }
}">
    <!-- Background Accents -->
    <div class="absolute top-0 right-0 -mr-20 -mt-20 w-[600px] h-[600px] bg-emerald-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-[400px] h-[400px] bg-indigo-50 rounded-full blur-3xl opacity-30 pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
            <!-- Left Content -->
            <div class="w-full lg:w-1/2 text-center lg:text-left">
                
                
                <h1 class="text-5xl md:text-7xl lg:text-8xl font-black outfit text-gray-900 leading-[0.9] tracking-tighter mb-8">
                    Craving? <br>
                    <span class="text-emerald-500">Just Tap &</span><br>
                    <span class="relative">
                        Enjoy.
                        <svg class="absolute -bottom-2 left-0 w-full h-3 text-emerald-200" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0 5 Q 25 0, 50 5 T 100 5" stroke="currentColor" stroke-width="4" fill="none"/></svg>
                    </span>
                </h1>
                
                <p class="text-xl text-gray-500 font-medium mb-12 max-w-lg mx-auto lg:mx-0 leading-relaxed">
                    Connecting you with the best restaurants, bakeries, and markets. Fresh food delivered to your doorstep in minutes.
                </p>

                <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start" 
                     x-data="{ 
                        query: '', 
                        results: { restaurants: [], meals: [] }, 
                        show: false,
                        loading: false,
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
                    
                    <div class="relative w-full max-w-md group">
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" 
                                   x-model="query"
                                   name="q"
                                   @input.debounce.300ms="fetchSuggestions()"
                                   @focus="if(query.length >= 2) show = true"
                                   @keydown.enter.prevent="if(query && query.trim().length){ window.location.href='{{ route('restaurants.index') }}?q=' + encodeURIComponent(query.trim()); }"
                                   placeholder="What are you eating today?" 
                                   class="w-full pl-12 pr-12 py-5 bg-gray-100 border-none focus:ring-4 focus:ring-emerald-500/20 rounded-3xl font-bold text-gray-900 placeholder-gray-400 shadow-inner transition-all">
                            
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            
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
                            
                            <div class="max-h-[60vh] md:max-h-[450px] overflow-y-auto overscroll-contain pb-4">
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

                    <button type="button"
                            @click="if(query && query.trim().length){ window.location.href='{{ route('restaurants.index') }}?q=' + encodeURIComponent(query.trim()); }"
                            class="w-full sm:w-auto px-10 py-5 bg-gray-900 text-white font-black rounded-3xl hover:bg-emerald-500 transition-all hover:shadow-2xl hover:shadow-emerald-500/30 transform hover:-translate-y-1 active:scale-95">
                        Find Food
                    </button>
                </div>
                
               
            </div>

            <!-- Right Visual (Lottie Slideshow) -->
            <div class="w-full lg:w-1/2 relative flex items-center justify-center">
                <div class="relative w-[350px] md:w-[500px] h-[350px] md:h-[500px]">
                    <!-- Main Animation Wrapper -->
                    <div class="w-full h-full p-8 md:p-12 bg-white rounded-[4rem] shadow-[0_40px_100px_-20px_rgba(0,0,0,0.1)] border border-gray-50 relative z-10 flex items-center justify-center group">
                        <template x-for="(ani, index) in animations" :key="index">
                            <template x-if="activeLottie === index">
                                <div x-transition:enter="transition ease-out duration-700 delay-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-10"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-500"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-110 -translate-y-10"
                                     class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <dotlottie-player 
                                        :src="ani" 
                                        background="transparent" 
                                        speed="1" 
                                        autoplay
                                        class="w-[80%] h-[80%]"
                                        @complete="nextAnimation()"></dotlottie-player>
                                </div>
                            </template>
                        </template>
                    </div>
                    
                    <!-- Back blobs -->
                    <div class="absolute inset-0 bg-emerald-500/10 rounded-[4rem] rotate-3 scale-105"></div>
                    <div class="absolute inset-0 bg-indigo-500/5 rounded-[4rem] -rotate-3 scale-105"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Wave Bottom -->
    <div class="absolute bottom-0 left-0 right-0 h-24 pointer-events-none">
        <svg class="w-full h-full preserve-3d" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <path class="wave-fill transition-colors" fill="#f9fafb" fill-opacity="1" d="M0,192L48,197.3C96,203,192,213,288,192C384,171,480,117,576,112C672,107,768,149,864,154.7C960,160,1056,128,1152,112C1248,96,1344,96,1392,96L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</div>

<section class="pt-8 pb-20 bg-gray-50 relative z-20">
    <div class="container mx-auto px-4">

        <!-- Browse By Category Section -->
        <div class="mb-20">
            <div class="flex flex-wrap justify-between items-end mb-10 px-4">
                <div>
                    <h2 class="text-4xl outfit font-black text-gray-900 tracking-tight">Browse by Category</h2>
                    <div class="w-24 h-2 bg-emerald-500 rounded-full mt-2"></div>
                    <p class="mt-3 text-gray-500 font-medium">Find what you're craving, fast.</p>
                </div>
            </div>

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
                        320: { slidesPerView: 2, spaceBetween: 10 },
                        640: { slidesPerView: 3, spaceBetween: 20 },
                        1024: { slidesPerView: 5, spaceBetween: 30 }
                    }
                });
            }}" x-init="initSwiper()">
                <div class="swiper category-swiper !overflow-hidden md:!overflow-visible">
                    <div class="swiper-wrapper">
                        @foreach($homeCategories as $cat)
                            <div class="swiper-slide !w-40 sm:!w-48 lg:!w-56">
                                <a href="{{ route('browse.index', ['category' => $cat['slug']]) }}"
                                   class="group flex flex-col items-center justify-center gap-4 p-8 rounded-[2.5rem] bg-white border border-gray-100 shadow-sm hover:shadow-2xl hover:border-emerald-200 transition-all duration-500 cursor-pointer block text-center">
                                    <div class="w-20 h-20 rounded-3xl bg-gray-50 group-hover:bg-emerald-500 flex items-center justify-center text-4xl transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 transform shadow-inner group-hover:shadow-emerald-200">
                                        {{ $cat['emoji'] }}
                                    </div>
                                    <div class="space-y-1">
                                        <span class="block font-black text-gray-900 group-hover:text-emerald-600 text-base transition-colors">{{ $cat['label'] }}</span>
                        
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Custom Navigation (Optional but adds premium feel) -->
                <div class="hidden lg:flex justify-center gap-4 mt-12">
                    <div class="p-3 rounded-full bg-white shadow-md border border-gray-100 text-gray-400 hover:text-emerald-500 hover:border-emerald-200 transition-all cursor-pointer transform active:scale-95" onclick="document.querySelector('.category-swiper').swiper.slidePrev()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                    </div>
                    <div class="p-3 rounded-full bg-white shadow-md border border-gray-100 text-gray-400 hover:text-emerald-500 hover:border-emerald-200 transition-all cursor-pointer transform active:scale-95" onclick="document.querySelector('.category-swiper').swiper.slideNext()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-between items-end mb-12 px-4">
            <div>
                <h2 class="text-4xl outfit font-black text-gray-900 tracking-tight">Trending Spots</h2>
                <div class="w-24 h-2 bg-emerald-500 rounded-full mt-2"></div>
            </div>
            <a href="{{ route('restaurants.index') }}" class="hidden sm:inline-block font-bold text-emerald-600 hover:text-emerald-500 flex items-center gap-2 group transition-all">
                <span>See all</span>
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>

        <div class="flex flex-wrap">
            @forelse($restaurants ?? [] as $restaurant)
                <div class="w-full md:w-1/2 lg:w-1/3 px-4 mb-10 group">
                    <a href="{{ route('restaurant.show', $restaurant) }}" class="block h-full relative">
                        <div class="relative flex flex-col min-w-0 break-words bg-white w-full h-full shadow-md hover:shadow-2xl rounded-3xl transition-all duration-300 transform group-hover:-translate-y-2 border border-gray-100 overflow-hidden">
                            <!-- Image -->
                            <div class="h-48 relative overflow-hidden bg-gray-100 flex items-center justify-center">
                        
                               @if($restaurant->logo)
                                    <img alt="{{ $restaurant->name }}" src="{{ Storage::url($restaurant->logo) }}" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-in-out"/>
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-emerald-400 via-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-6xl outfit opacity-80 group-hover:scale-110 transition-transform duration-700 ease-in-out">
                                        {{ substr($restaurant->name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/20 to-transparent"></div>
                                
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
                                <h6 class="text-2xl font-black outfit text-gray-900 mt-2 group-hover:text-emerald-500 transition-colors">{{ $restaurant->name }}</h6>
                                <p class="mt-2 mb-4 text-gray-500 font-medium line-clamp-2">
                                    {{ $restaurant->description ?? 'Amazing food, cooked with perfection and delivered straight to you.' }}
                                </p>
                                
                                
                            
                            </div>
                            
                        </div>
                    </a>
                </div>
            @empty
                <div class="w-full py-20 text-center">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-emerald-100 text-emerald-500 mb-6 group hover:rotate-12 transition-transform">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-3xl font-black outfit text-gray-900 mb-2">No restaurants around yet!</h3>
                    <p class="text-gray-500 text-lg font-medium">Be the first to partner with us or come back later.</p>
                    
                    <a href="{{ route('register') }}" class="inline-block mt-8 font-bold px-8 py-4 rounded-full bg-gray-900 text-white hover:bg-emerald-500 hover:shadow-xl hover:shadow-emerald-500/40 transition-all transform hover:-translate-y-1">Open Your Store</a>
                </div>
            @endforelse
        </div>

        <div class="mt-8 flex justify-center lg:hidden">
            <a href="{{ route('restaurants.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white border border-gray-200 text-gray-900 font-black rounded-2xl shadow-sm hover:bg-gray-50 transition-all active:scale-95 group">
                <span>View All Spots</span>
                <svg class="w-5 h-5 text-emerald-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>
        
        <!-- Trending Meals Section -->
        @if(isset($trendingMeals) && $trendingMeals->count() > 0)
        <div class="mt-32">
            <div class="flex flex-wrap justify-between items-end mb-12 px-4">
                <div>
                    <h2 class="text-4xl outfit font-black text-gray-900 tracking-tight">Most Loved Meals</h2>
                    <div class="w-24 h-2 bg-emerald-500 rounded-full mt-2"></div>
                </div>
            </div>

            <div class="flex flex-wrap">
                @foreach($trendingMeals as $meal)
                    <div class="group w-full md:w-1/2 lg:w-1/3 px-4 mb-10" id="browse-card-[homemeal]-{{ $meal->id }}">
                        <div class="bg-white border border-gray-100 rounded-2xl p-4 flex flex-col sm:flex-row gap-4 hover:shadow-xl transition-shadow relative overflow-hidden h-full">
                            <!-- Meal Image -->
                            <a href="{{ route('restaurant.show', $meal->menuCategory->restaurant) }}#meal-{{ $meal->id }}" class="shrink-0 flex sm:block items-center justify-center">
                                <div class="w-full sm:w-28 h-40 sm:h-28 flex-shrink-0 bg-gray-100 rounded-xl overflow-hidden relative" id="browse-img-[homemeal]-{{ $meal->id }}">
                                    @if($meal->image)
                                        <img alt="{{ $meal->name }}" src="{{ Storage::url($meal->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/60 via-transparent to-transparent sm:hidden"></div>
                                    <div class="absolute bottom-2 left-2 flex items-center gap-1 bg-white/90 backdrop-blur-md pl-1 pr-2 py-1 rounded-full shadow-lg sm:hidden">
                                        <div class="w-4 h-4 rounded-full overflow-hidden bg-gray-100 flex-shrink-0 border border-white shadow-sm">
                                            @if($meal->menuCategory->restaurant->logo)
                                                <img src="{{ Storage::url($meal->menuCategory->restaurant->logo) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-emerald-100 text-emerald-600 text-[8px] font-bold">{{ substr($meal->menuCategory->restaurant->name,0,1) }}</div>
                                            @endif
                                        </div>
                                        <span class="text-[9px] font-bold text-gray-900 truncate max-w-[80px]">{{ $meal->menuCategory->restaurant->name }}</span>
                                    </div>
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
                                                <span class="text-gray-300 mx-1">•</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $meal->menuCategory->name }}</span>
                                            </div>
                                        </a>
                                        <span class="shrink-0 font-black text-emerald-500 whitespace-nowrap">
                                            ${{ number_format($meal->price, 2) }}
                                        </span>
                                    </div>
                                    @if($meal->description)
                                        <p class="text-sm text-gray-500 font-medium line-clamp-2 mt-2 break-words">{{ $meal->description }}</p>
                                    @endif
                                </div>

                                <!-- Custom Add to Cart Button (Only difference from browse is no active add function since it requires GSAP) -->
                                <div class="flex items-center justify-between mt-3">
                                    <div class="text-xs font-bold text-gray-400 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                        {{ $meal->order_items_count }} orders
                                    </div>
                                    
                                    @if(Auth::id() === ($meal->menuCategory->restaurant->user_id ?? null))
                                        <span class="text-[10px] font-bold text-amber-500 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">Own Restaurant</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
