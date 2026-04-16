<!DOCTYPE html>
<html lang="en" class="bg-white antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#10b981">
    <meta name="application-name" content="{{ config('app.name', 'AnsarEats') }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/ansareats-logo-v2.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/ansareats-app-icon.svg') }}">
    
    <style>
        #page-loader {
            opacity: 0;
            pointer-events: none;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        body.page-loading #page-loader {
            opacity: 1;
            pointer-events: auto;
            visibility: visible;
        }
        /* Lock scroll while loading to prevent jitter */
        body.page-loading {
            overflow: hidden;
        }
    </style>
    
    <!-- Alpine.js Plugins (MUST be before core) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <meta name="google-site-verification" content="77XusHMPwtWDon-HY1LN7IRcnC2vNt1e2BIAsabD4aU" />
   
    
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

        // Dark Mode Logic - IGNORE browser/device theme. Only respect user's manual choice.
        // Default: light mode. Only dark if user explicitly toggled it on.
        if (localStorage.getItem('dark-mode') === 'true') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('darkMode', {
                on: localStorage.getItem('dark-mode') === 'true',
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

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').then((reg) => {
                    window.__swRegistration = reg;

                    // Request notification permission safely
                    @auth
                    window.subscribeToPush = async () => {
                        if (Notification.permission !== 'granted') return;
                        try {
                            const vapidPublicKey = "{{ config('push.vapid.public_key', env('VAPID_PUBLIC_KEY')) }}";
                            if (!vapidPublicKey) return;
                            
                            const urlB64ToUint8Array = (base64String) => {
                                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                                const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
                                const rawData = window.atob(base64);
                                const outputArray = new Uint8Array(rawData.length);
                                for (let i = 0; i < rawData.length; ++i) {
                                    outputArray[i] = rawData.charCodeAt(i);
                                }
                                return outputArray;
                            };

                            let subscription = await reg.pushManager.getSubscription();
                            if (!subscription) {
                                subscription = await reg.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: urlB64ToUint8Array(vapidPublicKey)
                                });
                            }

                            const key = subscription.getKey ? subscription.getKey('p256dh') : '';
                            const auth = subscription.getKey ? subscription.getKey('auth') : '';

                            if (key && auth) {
                                await fetch('/push-subscriptions', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        endpoint: subscription.endpoint,
                                        public_key: btoa(String.fromCharCode.apply(null, new Uint8Array(key))),
                                        auth_token: btoa(String.fromCharCode.apply(null, new Uint8Array(auth)))
                                    })
                                });
                            }
                        } catch (e) {
                            console.error('Push subscription failed:', e);
                        }
                    };

                    if ('Notification' in window) {
                        if (Notification.permission === 'granted') {
                            window.subscribeToPush();
                        }
                    }
                    @endauth
                }).catch((e) => { console.error('SW Error:', e); });
            });
        }

        /**
         * Send an order status notification via Service Worker.
         * Works even when the tab is in the background.
         */
        window.sendOrderNotification = function(orderId, newStatus, message) {
            if (!('Notification' in window) || Notification.permission !== 'granted') {
                return;
            }

            const statusEmoji = {
                'accepted': '✅',
                'preparing': '🍳',
                'out_for_delivery': '🚀',
                'delivered': '🎉',
                'cancelled': '❌',
            };

            const emoji = statusEmoji[newStatus] || '📦';
            const title = `${emoji} Order #${orderId}`;
            const body = message || `Your order status changed to ${newStatus.replace(/_/g, ' ')}`;

            // Use service worker for background-capable notifications
            if (navigator.serviceWorker?.controller) {
                navigator.serviceWorker.controller.postMessage({
                    type: 'SHOW_NOTIFICATION',
                    title: title,
                    body: body,
                    tag: `order-${orderId}`,
                    url: '{{ route("profile.orders") }}',
                });
            } else if (window.__swRegistration) {
                // Fallback: use registration directly
                window.__swRegistration.showNotification(title, {
                    body: body,
                    icon: '/images/brand/ansareats-app-icon.svg',
                    badge: '/images/brand/ansareats-app-icon.svg',
                    tag: `order-${orderId}`,
                    renotify: true,
                    data: { url: '{{ route("profile.orders") }}' },
                    vibrate: [200, 100, 200],
                });
            }
        };
    </script>
   

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col text-gray-800 bg-white dark:bg-gray-900 overflow-x-hidden relative page-loading transition-theme pb-40 md:pb-0">
    @include('partials.push-notification-modal')
    @php
        $hasActiveOrders = auth()->check()
            ? \App\Models\Order::where('user_id', auth()->id())
                ->whereIn('status', ['pending', 'accepted', 'preparing', 'out_for_delivery'])
                ->exists()
            : false;
        $hasRestaurant = auth()->check() && auth()->user()->restaurant;
    @endphp

    <!-- Global Page Loader (Skeleton Transition) -->
    <div id="page-loader" class="fixed top-20 inset-x-0 bottom-0 bg-white dark:bg-gray-900 z-40 overflow-hidden pointer-events-none transition-opacity duration-300">
        <!-- Body Skeleton Grid -->
        @hasSection('skeleton')
            @yield('skeleton')
        @else
            <div class="max-w-7xl mx-auto px-4 mt-8 md:mt-12">
                <div class="w-3/4 md:w-1/2 h-10 md:h-14 bg-gray-200 dark:bg-gray-800 rounded-xl mb-4 animate-pulse"></div>
                <div class="w-1/2 md:w-1/3 h-5 bg-gray-200 dark:bg-gray-800 rounded-lg mb-10 animate-pulse"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-10">
                    @for ($i = 0; $i < 6; $i++)
                    <div class="h-[300px] bg-gray-200 dark:bg-gray-800 rounded-[2rem] animate-pulse @if($i > 1) hidden md:block @endif @if($i > 2) hidden lg:block @endif"></div>
                    @endfor
                </div>
            </div>
        @endif
    </div>
    
    <!-- Navigation -->
    <nav x-data="{ mobileMenuOpen: false }" x-effect="document.documentElement.classList.toggle('overflow-hidden', mobileMenuOpen); document.body.classList.toggle('overflow-hidden', mobileMenuOpen);" class="bg-white/80 dark:bg-gray-900/95 backdrop-blur-xl sticky top-0 z-50 shadow-sm border-b border-gray-100 dark:border-gray-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center group gap-1.5">
                        <div class="w-14 h-14 flex items-center justify-center transform group-hover:scale-105 transition-all duration-300">
                            <img src="{{ asset('images/brand/ansareats-logo-v2.svg') }}" alt="AnsarEats logo" class="w-full h-full" width="56" height="56">
                        </div>
                        <span class="font-extrabold text-2xl outfit text-gray-900 dark:text-white group-hover:text-emerald-500 tracking-tight transition-colors">AnsarEats</span>
                    </a>

                    <div class="hidden lg:flex items-center ml-10 space-x-8">
                        <a href="{{ route('restaurants.index') }}" class="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-emerald-500 transition-colors uppercase tracking-widest">Explore</a>
                        @if(! $hasRestaurant)
                            <a href="{{ route('partner.with.us') }}" class="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-emerald-500 transition-colors uppercase tracking-widest">Partner with us</a>
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
                        
                        <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-2xl transition-all duration-500 ease-in-out overflow-hidden h-11"
                             :class="expanded ? 'w-40 sm:w-64 px-4' : 'w-11 justify-center cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-500/10 hover:text-emerald-500'"
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
                                   class="bg-transparent border-none shadow-none focus:outline-none focus:ring-0 focus:border-transparent focus:shadow-none text-sm font-bold text-gray-900 dark:text-white placeholder-gray-400 ml-2 py-0"
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
                             class="absolute top-full right-0 mt-3 w-72 sm:w-80 bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-[110]"
                             x-cloak>
                            
                            <div class="h-80 md:h-96 overflow-y-auto overscroll-contain no-scrollbar pb-4" style="-webkit-overflow-scrolling: touch;">
                            <template x-if="results.restaurants.length > 0">
                                <div class="p-2">
                                    <div class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400">Restaurants</div>
                                    <template x-for="r in results.restaurants" :key="r.id">
                                        <a :href="r.url" class="flex items-center gap-3 p-2 rounded-2xl hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors group">
                                            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                                <template x-if="r.logo">
                                                    <img :src="r.logo" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!r.logo">
                                                    <span class="text-sm font-black text-gray-400" x-text="r.name.charAt(0)"></span>
                                                </template>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-bold text-sm text-gray-900 dark:text-white truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors" x-text="r.name"></div>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase">Restaurant</div>
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
                        <button @click="$dispatch('toggle-cart')" class="relative font-semibold text-gray-600 dark:text-gray-400 hover:text-emerald-500 transition-colors p-2 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-500/10 h-11 w-11 flex items-center justify-center">
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
                                        class="flex items-center gap-2 px-5 py-2.5 rounded-full bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-emerald-100 dark:hover:border-emerald-500/30 transition-all group">
                                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-black">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                    <span class="font-bold text-gray-700 dark:text-gray-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ auth()->user()->name }}</span>
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
                                     class="absolute right-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl border border-gray-100 dark:border-gray-700 p-2 z-[100]"
                                     x-cloak>
                                    
                                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-600 dark:text-gray-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all group">
                                        <div class="w-8 h-8 rounded-xl bg-gray-50 dark:bg-gray-700 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-500/20 flex items-center justify-center text-gray-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
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

                </div>
            </div>
        </div>
    </nav>

    <!-- Bottom Navigation (Mobile) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 z-[100] pb-3 pt-2 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] dark:shadow-black/20">
        <div class="flex w-full px-2">
            <!-- Home -->
            <a href="{{ url('/') }}" class="flex-1 flex flex-col items-center justify-center p-2 {{ request()->is('/') ? 'text-emerald-500' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl transition-colors' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-bold">Home</span>
            </a>
            
            <!-- Explore -->
            <a href="{{ route('restaurants.index') }}" class="flex-1 flex flex-col items-center justify-center p-2 {{ request()->routeIs('restaurants.index') || request()->routeIs('restaurant.show') ? 'text-emerald-500' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-xl transition-colors' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="text-[10px] font-bold">Explore</span>
            </a>
            
            <!-- Orders -->
            <a href="{{ auth()->check() ? route('profile.orders') : route('login') }}" class="flex-1 flex flex-col items-center justify-center p-2 relative {{ request()->routeIs('profile.orders') ? 'text-emerald-500' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-xl transition-colors' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                @if($hasActiveOrders)
                    <span class="absolute top-2 right-[calc(50%-12px)] w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white"></span>
                @endif
                <span class="text-[10px] font-bold">Orders</span>
            </a>

            <!-- Dashboard / Partner -->
            @if(auth()->check() && auth()->user()->role !== 'super_admin' && auth()->user()->restaurant)
            <a href="{{ route('owner.dashboard') }}" class="flex-1 flex flex-col items-center justify-center p-2 {{ request()->routeIs('owner.dashboard') ? 'text-indigo-500' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-xl transition-colors' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="text-[10px] font-bold">Dashboard</span>
            </a>
            @else
            <a href="{{ route('partner.with.us') }}" class="flex-1 flex flex-col items-center justify-center p-2 {{ request()->routeIs('partner.with.us') ? 'text-emerald-500' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-xl transition-colors' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span class="text-[10px] font-bold">Partner</span>
            </a>
            @endif

            <!-- Account -->
            <a href="{{ auth()->check() ? route('profile.account') : route('login') }}" class="flex-1 flex flex-col items-center justify-center p-2 {{ request()->routeIs('profile.account') || request()->routeIs('profile.show') || request()->routeIs('login') ? 'text-emerald-500' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-xl transition-colors' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-[10px] font-bold">Account</span>
            </a>
        </div>
    </div>

    @include('layouts.partials.cart-drawer')
    @include('layouts.partials.toast-notification')


    <!-- Main Content -->
    <main class="flex-grow">
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

    @stack('modals')

    
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
                            const res = await fetch('/cart?_t=' + Date.now());
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
                            const res = await fetch('/cart?_t=' + Date.now());
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

            if (!Alpine.store('appToast')) {
                Alpine.store('appToast', {
                    visible: false,
                    message: '',
                    type: 'success',
                    timeout: null,
                    get toastClasses() {
                        if (this.type === 'error') {
                            return {
                                container: 'bg-white border-red-100',
                                iconWrap: 'bg-red-100',
                                icon: 'text-red-500',
                                text: 'text-red-900',
                            };
                        }

                        return {
                            container: 'bg-white border-gray-100',
                            iconWrap: 'bg-emerald-100',
                            icon: 'text-emerald-500',
                            text: 'text-gray-900',
                        };
                    },
                    show(detail = {}) {
                        this.message = detail.message || 'Cart updated!';
                        this.type = detail.type || 'success';
                        this.visible = true;
                        if (this.timeout) clearTimeout(this.timeout);
                        this.timeout = setTimeout(() => {
                            this.visible = false;
                        }, 3000);
                    },
                });
            }
        };

        window.showAppToast = function(detail = {}) {
            if (window.Alpine && Alpine.store('appToast')) {
                Alpine.store('appToast').show(detail);
                return;
            }

            window.dispatchEvent(new CustomEvent('show-toast', { detail }));
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

            // Hide loader if arriving via back/forward cache
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    window.hidePageLoader();
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>

