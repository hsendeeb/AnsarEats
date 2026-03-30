<!DOCTYPE html>
<html lang="en" class="bg-gray-50 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Alpine.js Plugins (MUST be before core) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
   
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Lottie Player -->
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

    <!-- Swiper.js (Modern Slider) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Animation & Alpine Plugins -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/MotionPathPlugin.min.js"></script>
    <script>
        // Ensure GSAP plugins are ready
        if (window.gsap && window.MotionPathPlugin) {
            gsap.registerPlugin(MotionPathPlugin);
        }

        // Dark Mode Logic (Flash Prevention)
        const isSelectedDark = localStorage.getItem('dark-mode') === 'true';
        const hasNoSelection = !('dark-mode' in localStorage);
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (isSelectedDark || (hasNoSelection && prefersDark)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('darkMode', {
                on: localStorage.getItem('dark-mode') === 'true' || (!('dark-mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toggle() {
                    this.on = !this.on;
                    localStorage.setItem('dark-mode', this.on);
                    if (this.on) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });
        });
    </script>
   

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col text-gray-800 bg-gray-50 dark:bg-gray-900 overflow-x-hidden relative page-loading transition-theme">
    @php
        $hasActiveOrders = auth()->check()
            ? \App\Models\Order::where('user_id', auth()->id())
                ->whereIn('status', ['pending', 'accepted', 'preparing'])
                ->exists()
            : false;
        $hasRestaurant = auth()->check() && auth()->user()->restaurant;
    @endphp

    <!-- Decorative background blobs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-0 -left-4 w-72 h-72 bg-emerald-300 rounded-full mix-blend-multiply filter blur-2xl opacity-10 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 bg-teal-300 rounded-full mix-blend-multiply filter blur-2xl opacity-10 animate-blob animation-delay-2000"></div>
    </div>

    <!-- Global Page Loader -->
    <div id="page-loader" class="fixed inset-0 flex items-center justify-center bg-emerald-50/80 backdrop-blur-sm z-[120]">
        <div class="w-14 h-14 rounded-full border-4 border-emerald-400 border-t-transparent animate-spin"></div>
    </div>
    
    <!-- Navigation -->
    <nav x-data="{ mobileMenuOpen: false }" x-effect="document.documentElement.classList.toggle('overflow-hidden', mobileMenuOpen); document.body.classList.toggle('overflow-hidden', mobileMenuOpen);" class="bg-white/80 backdrop-blur-xl sticky top-0 z-50 shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center  group">
                        <div class="w-24 h-24 flex items-center justify-center transform group-hover:scale-110 transition-all duration-300">
                            <dotlottie-player src="https://lottie.host/87132d2c-ba34-4710-a301-28e49f292ac0/zItpws4UYi.lottie" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
                        </div>
                        <span class="font-extrabold text-2xl outfit text-gray-900 group-hover:text-emerald-500 tracking-tight transition-colors">AnsarEats</span>
                    </a>

                    <div class="hidden lg:flex items-center ml-10 space-x-8">
                        <a href="{{ route('restaurants.index') }}" class="text-sm font-bold text-gray-600 hover:text-emerald-500 transition-colors uppercase tracking-widest">Explore</a>
                        @if(! $hasRestaurant)
                            <a href="{{ route('partner.with.us') }}" class="font-bold px-6 py-2.5 rounded-full text-black transition-all">Partner with us</a>
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center space-x-2 md:space-x-4">
                    <!-- Global Search (Stretching) -->
                    <div x-data="{ 
                            expanded: false, 
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
                         @click.away="expanded = false; show = false"
                         class="relative hidden md:flex items-center">
                        
                        <div class="flex items-center bg-gray-100 rounded-2xl transition-all duration-500 ease-in-out overflow-hidden h-11"
                             :class="expanded ? 'w-40 sm:w-64 px-4' : 'w-11 justify-center cursor-pointer hover:bg-emerald-50 hover:text-emerald-500'"
                             @click="if(!expanded) { expanded = true; $nextTick(() => $refs.navSearchInput.focus()); }">
                            
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="expanded ? 'text-emerald-500' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            
                            <input x-ref="navSearchInput"
                                   x-model="query"
                                   x-show="expanded"
                                   x-transition:enter="transition ease-out duration-300 delay-100"
                                   x-transition:enter-start="opacity-0"
                                   x-transition:enter-end="opacity-100"
                                   @input.debounce.300ms="fetchSuggestions()"
                                   @focus="if(query.length >= 2) show = true"
                                   type="text"
                                   placeholder="Search..."
                                   class="bg-transparent border-none shadow-none focus:outline-none focus:ring-0 focus:border-transparent focus:shadow-none text-sm font-bold text-gray-900 w-full placeholder-gray-400 ml-2 py-0"
                                   style="outline: none !important; box-shadow: none !important; -webkit-box-shadow: none !important;">

                            <div x-show="loading && expanded" class="ml-2">
                                <svg class="animate-spin h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Dropdown Results -->
                        <div x-show="show && expanded && (results.restaurants.length > 0 || results.meals.length > 0)"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute top-full right-0 mt-3 w-72 sm:w-80 bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden z-[110]"
                             x-cloak>
                            
                            <div class="h-80 md:h-96 overflow-y-auto overscroll-contain no-scrollbar pb-4" style="-webkit-overflow-scrolling: touch;">
                            <template x-if="results.restaurants.length > 0">
                                <div class="p-2">
                                    <div class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400">Restaurants</div>
                                    <template x-for="r in results.restaurants" :key="r.id">
                                        <a :href="r.url" class="flex items-center gap-3 p-2 rounded-2xl hover:bg-emerald-50 transition-colors group">
                                            <div class="w-10 h-10 rounded-xl bg-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                                <template x-if="r.logo">
                                                    <img :src="r.logo" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!r.logo">
                                                    <span class="text-sm font-black text-gray-400" x-text="r.name.charAt(0)"></span>
                                                </template>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-bold text-sm text-gray-900 truncate group-hover:text-emerald-600 transition-colors" x-text="r.name"></div>
                                                <div class="text-[10px] text-gray-500 font-bold uppercase">Restaurant</div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <template x-if="results.meals.length > 0">
                                <div class="p-2 border-t border-gray-50">
                                    <div class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400">Popular Meals</div>
                                    <template x-for="m in results.meals" :key="m.id">
                                        <a :href="m.url" class="flex items-center gap-3 p-2 rounded-2xl hover:bg-emerald-50 transition-colors group">
                                            <div class="w-10 h-10 rounded-xl bg-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                                <template x-if="m.image">
                                                    <img :src="m.image" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!m.image">
                                                    <div class="bg-emerald-100 w-full h-full flex items-center justify-center text-emerald-500">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-bold text-sm text-gray-900 truncate group-hover:text-emerald-600 transition-colors" x-text="m.name"></div>
                                                <div class="text-[10px] text-gray-500 font-bold uppercase" x-text="m.restaurant_name"></div>
                                            </div>
                                            <div class="font-black text-emerald-500 text-xs" x-text="'$' + m.price"></div>
                                        </a>
                                    </template>
                                </div>
                            </template>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Button -->
                    <div x-data="navCart" @cart-updated.window="updateFromEvent($event)">
                        <button @click="$dispatch('toggle-cart')" class="relative font-semibold text-gray-600 hover:text-emerald-500 transition-colors p-2 rounded-xl hover:bg-emerald-50 h-11 w-11 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span x-show="count > 0" x-transition
                                class="absolute -top-1 -right-1 bg-emerald-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center shadow-lg border-2 border-white"
                                x-text="count"></span>
                        </button>
                    </div>

                    <!-- Desktop Links -->
                    <div class="hidden md:flex items-center space-x-4">
                        @guest
                            <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-emerald-500 transition-colors">Log In</a>
                            <a href="{{ route('register') }}" class="bg-emerald-500 px-4 py-2 font-semibold text-white rounded-md transition-colors">Register</a>
                          
                        @else
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" 
                                        class="flex items-center gap-2 px-5 py-2.5 rounded-full bg-white border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-100 transition-all group">
                                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-black">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                    <span class="font-bold text-gray-700 group-hover:text-emerald-600 transition-colors">{{ auth()->user()->name }}</span>
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-500 transition-all" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                     class="absolute right-0 mt-3 w-56 bg-white rounded-[2rem] shadow-2xl border border-gray-100 p-2 z-[100]"
                                     x-cloak>
                                    
                                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                        <div class="w-8 h-8 rounded-xl bg-gray-50 group-hover:bg-emerald-100 flex items-center justify-center text-gray-400 group-hover:text-emerald-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        My Profile
                                    </a>

                                    <a href="{{ route('profile.orders') }}" class="flex items-center justify-between gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-xl bg-gray-50 group-hover:bg-emerald-100 flex items-center justify-center text-gray-400 group-hover:text-emerald-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                            </div>
                                            Orders
                                        </div>
                                        @if($hasActiveOrders)
                                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        @endif
                                    </a>

                                    @if(auth()->user()->role !== 'super_admin' && auth()->user()->restaurant)
                                    <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all group">
                                        <div class="w-8 h-8 rounded-xl bg-gray-50 group-hover:bg-indigo-100 flex items-center justify-center text-gray-400 group-hover:text-indigo-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                        </div>
                                        Dashboard
                                    </a>
                                    @elseif(auth()->user()->role !== 'super_admin')
                                    <a href="{{ route('partner.with.us') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                        <div class="w-8 h-8 rounded-xl bg-gray-50 group-hover:bg-emerald-100 flex items-center justify-center text-gray-400 group-hover:text-emerald-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        </div>
                                        Partner with us
                                    </a>
                                    @endif

                                    <hr class="my-2 border-gray-50">

                                    <!-- Dark Mode Toggle -->
                                    <div class="px-4 py-3 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">
                                                <svg x-show="!$store.darkMode.on" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                <svg x-show="$store.darkMode.on" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                                            </div>
                                            <span class="text-sm font-bold text-gray-600">Dark Mode</span>
                                        </div>
                                        <button @click.stop="$store.darkMode.toggle()" 
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                                :class="$store.darkMode.on ? 'bg-emerald-500' : 'bg-gray-200'">
                                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" 
                                                  :class="$store.darkMode.on ? 'translate-x-5' : 'translate-x-0'"></span>
                                        </button>
                                    </div>

                                    <hr class="my-2 border-gray-50">

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-red-500 hover:bg-red-50 transition-all group">
                                            <div class="w-8 h-8 rounded-xl bg-red-100/50 group-hover:bg-red-100 flex items-center justify-center text-red-400 group-hover:text-red-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                            </div>
                                            Log Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endguest
                    </div>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = true" class="md:hidden p-2 rounded-xl text-gray-600 hover:bg-gray-100 hover:text-emerald-500 transition-all active:scale-90">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer Sidebar -->
        <template x-teleport="body">
            <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-[100] overflow-hidden">
                <!-- Overlay -->
                <div x-show="mobileMenuOpen" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="mobileMenuOpen = false" 
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-md"></div>
                
                <!-- Sidebar -->
                <div x-show="mobileMenuOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="fixed left-0 top-0 bottom-0 h-full w-4/5 max-w-sm bg-white shadow-2xl flex flex-col overflow-hidden">
                    
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-br from-gray-50 to-white">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 flex items-center justify-center">
                                <dotlottie-player src="https://lottie.host/87132d2c-ba34-4710-a301-28e49f292ac0/zItpws4UYi.lottie" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
                            </div>
                            <span class="font-black text-2xl outfit text-gray-900">AnsarEats</span>
                        </div>
                        <button @click="mobileMenuOpen = false" class="p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-900 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Mobile Search -->
                    <div class="px-6 py-4 border-b border-gray-100" 
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
                        
                        <div class="relative">
                            <div class="flex items-center bg-gray-100 rounded-2xl px-4 h-12 border border-transparent transition-all">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <input x-model="query"
                                       @input.debounce.300ms="fetchSuggestions()"
                                       @focus="if(query.length >= 2) show = true"
                                       type="text"
                                       placeholder="Search for food..."
                                       class="bg-transparent border-none shadow-none focus:outline-none focus:ring-0 focus:border-transparent focus:shadow-none text-sm font-bold text-gray-900 w-full placeholder-gray-400 ml-2 py-0"
                                       style="outline: none !important; box-shadow: none !important; -webkit-box-shadow: none !important;">
                                
                                <div x-show="loading">
                                    <svg class="animate-spin h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Mobile Dropdown Results -->
                            <div x-show="show && (results.restaurants.length > 0 || results.meals.length > 0)"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-[110]"
                                 x-cloak>
                                
                                <div class="h-72 overflow-y-auto overscroll-contain no-scrollbar pb-4" style="-webkit-overflow-scrolling: touch;">
                                <template x-if="results.restaurants.length > 0">
                                    <div class="p-2">
                                        <div class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400">Restaurants</div>
                                        <template x-for="r in results.restaurants" :key="r.id">
                                            <a :href="r.url" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-2 rounded-xl hover:bg-emerald-50 transition-colors group">
                                                <div class="w-8 h-8 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                                    <template x-if="r.logo">
                                                        <img :src="r.logo" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!r.logo">
                                                        <span class="text-xs font-black text-gray-400" x-text="r.name.charAt(0)"></span>
                                                    </template>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-bold text-xs text-gray-900 truncate" x-text="r.name"></div>
                                                </div>
                                            </a>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="results.meals.length > 0">
                                    <div class="p-2 border-t border-gray-50">
                                        <div class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400">Meals</div>
                                        <template x-for="m in results.meals" :key="m.id">
                                            <a :href="m.url" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-2 rounded-xl hover:bg-emerald-50 transition-colors group">
                                                <div class="w-8 h-8 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                                    <template x-if="m.image">
                                                        <img :src="m.image" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!m.image">
                                                        <div class="bg-emerald-100 w-full h-full flex items-center justify-center text-emerald-500">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-bold text-xs text-gray-900 truncate" x-text="m.name"></div>
                                                    <div class="text-[10px] text-gray-500 font-bold uppercase truncate" x-text="m.restaurant_name"></div>
                                                </div>
                                            </a>
                                        </template>
                                    </div>
                                </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="px-6 py-4 border-b border-gray-100">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Quick Actions</p>
                        <button @click="$dispatch('toggle-cart'); mobileMenuOpen = false" class="w-full py-4 bg-emerald-500 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            View Cart
                        </button>
                    </div>

                    <div class="overflow-y-auto max-h-[calc(100vh-320px)] p-6 space-y-2">
                        <a href="{{ url('/') }}" @click="mobileMenuOpen = false" class="flex items-center gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            </div>
                            Home
                        </a>

                        <a href="{{ route('restaurants.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            Explore
                        </a>
                        
                        @guest
                            <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="flex items-center gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                </div>
                                Log In
                            </a>
                            <a href="{{ route('register') }}" @click="mobileMenuOpen = false" class="flex items-center gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                </div>
                                Register
                            </a>
                            <a href="{{ route('partner.with.us') }}" @click="mobileMenuOpen = false" class="flex items-center gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </div>
                                Partner with us
                            </a>
                        @else
                            <a href="{{ route('profile.show') }}" @click="mobileMenuOpen = false" class="flex items-center gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                Profile
                            </a>

                            <a href="{{ route('profile.orders') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    </div>
                                    Orders
                                </div>
                                @if($hasActiveOrders)
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                @endif
                            </a>

                            @if(auth()->user()->role !== 'super_admin' && auth()->user()->restaurant)
                                <a href="{{ route('owner.dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center gap-4 p-4 rounded-2xl font-bold  hover:bg-indigo-100 transition-all group">
                                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-indigo-600 shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                    </div>
                                    Dashboard
                                </a>
                            @elseif(auth()->user()->role !== 'super_admin')
                                <a href="{{ route('partner.with.us') }}" @click="mobileMenuOpen = false" class="flex items-center gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                    Partner with us
                                </a>
                            @endif
                            
                            <hr class="my-4 border-gray-100">
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-4 p-4 rounded-2xl font-bold text-red-500 hover:bg-red-50 transition-all group text-left">
                                    <div class="w-10 h-10 rounded-xl bg-red-100 group-hover:bg-red-200 flex items-center justify-center text-red-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                    </div>
                                    Log Out
                                </button>
                            </form>
                        @endguest

                        <hr class="my-4 border-gray-100 dark:border-gray-800">

                        <!-- Mobile Dark Mode Toggle (Global) -->
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/50">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-700 flex items-center justify-center text-gray-400">
                                    <svg x-show="!$store.darkMode.on" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <svg x-show="$store.darkMode.on" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                                </div>
                                <span class="font-bold text-gray-900 dark:text-gray-100">Dark Mode</span>
                            </div>
                            <button @click="$store.darkMode.toggle()" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                    :class="$store.darkMode.on ? 'bg-emerald-500' : 'bg-gray-200'">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" 
                                      :class="$store.darkMode.on ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </template>
    </nav>

    @include('layouts.partials.cart-drawer')
    @include('layouts.partials.toast-notification')


    <!-- Main Content -->
    <main class="flex-grow relative z-10">
        @if(session('success'))
            <div class="bg-emerald-100 border-b border-emerald-200">
                <div class="max-w-7xl mx-auto px-4 py-3 sm:px-6 lg:px-8 flex items-center justify-center">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border-b border-red-200">
                <div class="max-w-7xl mx-auto px-4 py-3 sm:px-6 lg:px-8 flex items-center justify-center">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm font-bold text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white overflow-hidden py-16 mt-auto relative z-10 w-full ">
        <!-- White Radial Circles Background Effect -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center z-0 opacity-10">
            <div class="w-[1200px] h-[1200px] border border-white rounded-full absolute"></div>
            <div class="w-[1000px] h-[1000px] border border-white rounded-full absolute"></div>
            <div class="w-[800px] h-[800px] border border-white rounded-full absolute"></div>
            <div class="w-[600px] h-[600px] border border-white rounded-full absolute"></div>
            <div class="w-[400px] h-[400px] border border-white rounded-full absolute"></div>
            <div class="w-[200px] h-[200px] border border-white rounded-full absolute"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12 border-b border-gray-800/80 pb-12">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center gap-2 mb-6">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span class="font-extrabold text-3xl outfit text-white tracking-tight">AnsarEats</span>
                    </div>
                    <p class="text-gray-400 font-medium text-sm mb-6">Connecting you with the best restaurants, bakeries, and markets. Fresh food delivered to your doorstep in minutes.</p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800/80 border border-gray-700/50 flex items-center justify-center text-gray-400 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800/80 border border-gray-700/50 flex items-center justify-center text-gray-400 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800/80 border border-gray-700/50 flex items-center justify-center text-gray-400 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                        </a>
                    </div>
                </div>
                
                <div class="col-span-1">
                    <h3 class="text-white font-bold text-lg mb-6 outfit tracking-wide">Quick Links</h3>
                    <ul class="space-y-3 text-sm font-medium text-gray-400">
                        <li><a href="{{ url('/') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all inline-block">&rarr; Home</a></li>
                        <li><a href="{{ route('restaurants.index') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all inline-block">&rarr; Explore</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all inline-block">&rarr; Log In</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all inline-block">&rarr; Partner With Us</a></li>
                    </ul>
                </div>
                
                <div class="col-span-1">
                    <h3 class="text-white font-bold text-lg mb-6 outfit tracking-wide">Legal</h3>
                    <ul class="space-y-3 text-sm font-medium text-gray-400">
                        <li><a href="{{ route('legal.terms') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all inline-block">&rarr; Terms of Service</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all inline-block">&rarr; Privacy Policy</a></li>
                        <li><a href="{{ route('legal.cookies') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all inline-block">&rarr; Cookie Policy</a></li>
                        <li><a href="{{ route('help.center') }}" class="hover:text-emerald-400 hover:translate-x-1 transition-all inline-block">&rarr; Help Center</a></li>
                    </ul>
                </div>
                
                <div class="col-span-1">
                    <h3 class="text-white font-bold text-lg mb-6 outfit tracking-wide">Stay Updated</h3>
                    <p class="text-sm text-gray-400 mb-4">Subscribe to our newsletter for the latest deals.</p>
                    <form class="flex" @submit.prevent>
                        <input type="email" placeholder="Your email..." class="bg-gray-800/50 border border-gray-700 text-white px-4 py-3 rounded-l-2xl w-full text-sm font-medium focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                        <button type="button" class="bg-emerald-500 hover:bg-emerald-400 text-white px-5 py-3 rounded-r-2xl font-bold text-sm transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 text-xs font-medium">
                <p>&copy; {{ date('Y') }} AnsarEats. All rights reserved.</p>
                <div class="flex items-center gap-2">
                    <span>Crafted with</span>
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                    <span>by AnsarEats Team</span>
                </div>
            </div>
        </div>
    </footer>
    
    <script>
        // Global helper for Alpine + shared components
        window.initAlpineComponents = function() {
            if (!window.Alpine) return;

            // Only register if not already done (avoids console warnings)
            // But actually Alpine.data doesn't mind re-registration as much as it minds missing them
            
            if (!Alpine.data('navCart')) {
                Alpine.data('navCart', () => ({
                    count: 0,
                    async init() {
                        try {
                            const res = await fetch('/cart');
                            const data = await res.json();
                            this.count = data.count || 0;
                        } catch(e) {}
                    },
                    updateFromEvent(event) {
                        this.count = event.detail.count || 0;
                    }
                }));
            }

            if (!Alpine.data('cartDrawer')) {
                Alpine.data('cartDrawer', () => ({
                    open: false,
                    cart: { items: {}, count: 0, total: 0, restaurant_id: null, restaurant_name: null },
                    
                    async init() {
                        try {
                            const res = await fetch('/cart');
                            this.cart = await res.json();
                            this.cart.count = this.cart.count || 0;
                            this.cart.total = this.cart.total || 0;
                        } catch(e) {}
                    },

                    updateFromEvent(event) {
                        this.cart = event.detail || { items: {}, count: 0, total: 0 };
                        this.cart.count = this.cart.count || 0;
                        this.cart.total = this.cart.total || 0;
                    },

                    async updateQuantity(itemKey, qty) {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content;
                        try {
                            const res = await fetch('/cart/update', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                                body: JSON.stringify({ item_key: itemKey, quantity: qty })
                            });
                            const data = await res.json();
                            if (res.ok) {
                                this.cart = data.cart;
                                window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
                            }
                        } catch(e) {}
                    },

                    async clearCart() {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content;
                        try {
                            const res = await fetch('/cart/clear', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            });
                            const data = await res.json();
                            if (res.ok) {
                                this.cart = data.cart;
                                window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
                            }
                        } catch(e) {}
                    }
                }));
            }

            if (!Alpine.data('toastManager')) {
                Alpine.data('toastManager', () => ({
                    visible: false,
                    message: '',
                    timeout: null,
                    showToast(event) {
                        this.message = event.detail.message || 'Cart updated!';
                        this.visible = true;
                        if (this.timeout) clearTimeout(this.timeout);
                        this.timeout = setTimeout(() => { this.visible = false; }, 3000);
                    },
                }));
            }
        };

        // Initialize on start
        if (window.Alpine) {
            window.initAlpineComponents();
        } else {
            document.addEventListener('alpine:init', window.initAlpineComponents);
        }
        
        // Simple global page loading spinner
        window.showPageLoader = function() {
            document.body.classList.add('page-loading');
        };

        window.hidePageLoader = function() {
            document.body.classList.remove('page-loading');
        };

        document.addEventListener('DOMContentLoaded', () => {
            if (window.gsap && window.MotionPathPlugin) {
                gsap.registerPlugin(MotionPathPlugin);
            }

            if (typeof window.initDashboard === 'function') {
                window.initDashboard();
            }

            // Hide initial loader shortly after content is ready
            setTimeout(() => {
                window.hidePageLoader();
            }, 150);

            // Show loader when navigating away
            window.addEventListener('beforeunload', () => {
                window.showPageLoader();
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
