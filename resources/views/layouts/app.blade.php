<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Swup for Page Transitions -->
    <script src="https://unpkg.com/swup@4"></script>
    <script src="https://unpkg.com/@swup/scripts-plugin@2"></script>
    <script src="https://unpkg.com/@swup/a11y-plugin@3"></script>
    
    <!-- Lottie Player -->
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

    <!-- Animation & Alpine Plugins -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/MotionPathPlugin.min.js"></script>
    <script>
        // Ensure GSAP plugins are ready
        if (window.gsap && window.MotionPathPlugin) {
            gsap.registerPlugin(MotionPathPlugin);
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6, .outfit { font-family: 'Outfit', sans-serif; }
        
        /* Playful Swup Transitions */
        html { scroll-behavior: smooth; }
        
        /* Swup default classes fade & zoom */
        .transition-fade {
            transition: 400ms opacity cubic-bezier(0.4, 0, 0.2, 1), 
                        400ms transform cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        
        html.is-animating .transition-fade {
            opacity: 0;
            transform: translateY(15px) scale(0.98);
        }
        
        /* Blob animations for background */
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex flex-col text-gray-800 bg-gray-50 overflow-x-hidden relative">

    <!-- Decorative background blobs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-0 -left-4 w-72 h-72 bg-emerald-300 rounded-full mix-blend-multiply filter blur-2xl opacity-20 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 bg-teal-300 rounded-full mix-blend-multiply filter blur-2xl opacity-20 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-emerald-400 rounded-full mix-blend-multiply filter blur-2xl opacity-20 animate-blob animation-delay-4000"></div>
    </div>
    
    <!-- Navigation -->
    <nav x-data="{ mobileMenuOpen: false }" class="bg-white/80 backdrop-blur-xl sticky top-0 z-50 shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-400 shadow-lg shadow-emerald-500/40 flex items-center justify-center transform group-hover:scale-105 group-hover:-rotate-6 transition-all duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <span class="font-extrabold text-3xl outfit text-gray-900 group-hover:text-emerald-500 tracking-tight transition-colors">TotersLeb</span>
                    </a>
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
                             :class="expanded ? 'w-40 sm:w-64 px-4 ring-2 ring-emerald-500/20' : 'w-11 justify-center cursor-pointer hover:bg-emerald-50 hover:text-emerald-500'"
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
                                   class="bg-transparent border-none focus:ring-0 text-sm font-bold text-gray-900 w-full placeholder-gray-400 ml-2 py-0">

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
                             class="absolute top-full right-0 mt-3 w-72 sm:w-80 bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden z-[110] max-h-[400px] overflow-y-auto"
                             x-cloak>
                            
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

                    <!-- Cart Button -->
                    <div x-data="navCart()" x-init="init()" @cart-updated.window="updateFromEvent($event)">
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
                            <a href="{{ route('register') }}" class="font-bold px-6 py-2.5 rounded-full bg-emerald-500 text-white hover:bg-emerald-400 hover:shadow-xl hover:shadow-emerald-500/40 transition-all transform hover:-translate-y-0.5 active:scale-95">Partner with us</a>
                        @else
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('owner.dashboard') }}" class="font-bold px-5 py-2.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-100 transition-all shadow-sm">Dashboard</a>
                                
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="font-bold px-4 py-2 text-gray-500 hover:text-red-500 hover:bg-red-50 rounded-full transition-all">Log Out</button>
                                </form>
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
            <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-[100]">
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
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <span class="font-black text-2xl outfit text-gray-900">TotersLeb</span>
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
                            <div class="flex items-center bg-gray-100 rounded-2xl px-4 h-12 border border-transparent focus-within:border-emerald-500/20 focus-within:ring-4 focus-within:ring-emerald-500/10 transition-all">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <input x-model="query"
                                       @input.debounce.300ms="fetchSuggestions()"
                                       @focus="if(query.length >= 2) show = true"
                                       type="text"
                                       placeholder="Search for food..."
                                       class="bg-transparent border-none focus:ring-0 text-sm font-bold text-gray-900 w-full placeholder-gray-400 ml-2 py-0">
                                
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
                                 class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-[110] max-h-[300px] overflow-y-auto"
                                 x-cloak>
                                
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
                    <div class="flex-1 overflow-y-auto p-6 space-y-2">
                        <a href="{{ url('/') }}" class="flex items-center gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            </div>
                            Home
                        </a>
                        
                        @guest
                            <a href="{{ route('login') }}" class="flex items-center gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                </div>
                                Log In
                            </a>
                            <a href="{{ route('register') }}" class="flex items-center gap-4 p-4 rounded-2xl font-bold text-gray-900 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center text-gray-500 group-hover:text-emerald-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                </div>
                                Partner with us
                            </a>
                        @else
                            <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-4 p-4 rounded-2xl font-bold  hover:bg-indigo-100 transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-indigo-600 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                </div>
                                Dashboard
                            </a>
                            
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
                    </div>

                    <!-- Footer -->
                    <div class="p-6 bg-gray-50 text-center">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Quick Actions</p>
                        <button @click="$dispatch('toggle-cart'); mobileMenuOpen = false" class="w-full py-4 bg-emerald-500 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            View Cart
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </nav>

    <!-- Cart Sidebar Drawer -->
    <div x-data="cartDrawer()" x-init="init()" @toggle-cart.window="open = !open" @cart-updated.window="updateFromEvent($event)" x-cloak>
        <!-- Overlay -->
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="open = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60]"></div>
        
        <!-- Drawer -->
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl z-[70] flex flex-col">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-6 text-white flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black outfit">Your Cart</h3>
                            <p class="text-emerald-100 text-sm font-medium" x-text="cart.restaurant_name ? 'From ' + cart.restaurant_name : 'Empty cart'"></p>
                        </div>
                    </div>
                    <button @click="open = false" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-6">
                <template x-if="Object.keys(cart.items || {}).length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center py-16">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h4 class="text-xl font-black outfit text-gray-900 mb-2">Cart is empty</h4>
                        <p class="text-gray-500 font-medium">Add items from a restaurant to get started!</p>
                    </div>
                </template>

                <template x-if="Object.keys(cart.items || {}).length > 0">
                    <div class="space-y-4">
                        <template x-for="(item, key) in cart.items" :key="key">
                            <div class="flex items-center gap-4 bg-gray-50 rounded-2xl p-4 border border-gray-100 group hover:shadow-sm transition-shadow">
                                <!-- Image -->
                                <div class="w-16 h-16 bg-gray-200 rounded-xl flex-shrink-0 overflow-hidden">
                                    <template x-if="item.image">
                                        <img :src="'/storage/' + item.image" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.image">
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Details -->
                                <div class="flex-1 min-w-0">
                                    <h5 class="font-bold text-gray-900 truncate" x-text="item.name"></h5>
                                    <p class="text-sm font-black text-emerald-500">$<span x-text="(item.price * item.quantity).toFixed(2)"></span></p>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button @click="updateQuantity(item.id, item.quantity - 1)" class="w-7 h-7 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-red-50 hover:border-red-200 hover:text-red-500 text-gray-500 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path></svg>
                                    </button>
                                    <span class="w-7 text-center font-bold text-sm" x-text="item.quantity"></span>
                                    <button @click="updateQuantity(item.id, item.quantity + 1)" class="w-7 h-7 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-500 text-gray-500 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Clear Cart -->
                        <button @click="clearCart()" class="w-full text-center text-sm font-bold text-red-500 hover:text-red-600 py-2 hover:bg-red-50 rounded-xl transition-colors mt-4">
                            Clear entire cart
                        </button>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <template x-if="Object.keys(cart.items || {}).length > 0">
                <div class="flex-shrink-0 border-t border-gray-100 p-6 bg-white">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-500 font-bold">Total</span>
                        <span class="text-2xl font-black outfit text-gray-900">$<span x-text="cart.total.toFixed(2)"></span></span>
                    </div>
                    <a href="{{ route('checkout') }}" class="block w-full text-center py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-[0.98] text-lg">
                        Checkout →
                    </a>
                </div>
            </template>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-data="toastManager()" @cart-updated.window="showToast($event)" class="fixed top-24 right-4 z-[80]" x-cloak>
        <template x-if="visible">
            <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0" class="bg-white border border-gray-100 rounded-2xl shadow-2xl p-4 flex items-center gap-3 min-w-[280px]">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <p class="font-bold text-gray-900 text-sm" x-text="message"></p>
            </div>
        </template>
    </div>

    <!-- Main Content wrapper for Swup -->
    <main id="swup" class="transition-fade flex-grow relative z-10">
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
    <footer class="bg-white border-t border-gray-100 py-12 mt-auto relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center">
            <div class="flex items-center gap-2 mb-6 grayscale opacity-40">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span class="font-extrabold text-2xl outfit">TotersLeb</span>
            </div>
            <p class="text-gray-400 font-medium text-sm text-center max-w-sm mb-6">Enjoy playful animations and seamless transitions. Built for showcase purposes.</p>
            <div class="flex gap-6 text-sm font-bold text-gray-400">
                <a href="#" class="hover:text-emerald-500 transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-emerald-500 transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-emerald-500 transition-colors">Partner Hub</a>
            </div>
        </div>
    </footer>
    
    <script>
        // Nav cart badge
        function navCart() {
            return {
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
            };
        }

        // Cart drawer
        function cartDrawer() {
            return {
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
                    this.cart = event.detail;
                    this.cart.count = this.cart.count || 0;
                    this.cart.total = this.cart.total || 0;
                },

                async updateQuantity(itemId, qty) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;
                    try {
                        const res = await fetch('/cart/update', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            body: JSON.stringify({ menu_item_id: itemId, quantity: qty })
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
            };
        }

        // Toast manager
        function toastManager() {
            return {
                visible: false,
                message: '',
                timeout: null,
                showToast(event) {
                    // Only show toast from add-to-cart events that have a message detail
                    // We'll use a separate custom event for this
                },
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Register GSAP plugins
            if (window.gsap && window.MotionPathPlugin) {
                gsap.registerPlugin(MotionPathPlugin);
            }

            const swup = new Swup({
                plugins: [new SwupScriptsPlugin(), new SwupA11yPlugin()]
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
