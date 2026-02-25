@extends('layouts.app')

@section('content')
<!-- Page Logic -->
<script data-swup-reload-script>
    // Ensure GSAP plugins are registered (safe to call multiple times)
    if (window.gsap && window.MotionPathPlugin) {
        gsap.registerPlugin(MotionPathPlugin);
    }

    // ========== GSAP Animation Helpers ==========

    window.getCartTargetPos = function() {
        const navCartBtn = document.querySelector('[x-data="navCart"] button');
        const floatingBtn = document.getElementById('floating-cart-btn');
        
        if (navCartBtn) {
            const rect = navCartBtn.getBoundingClientRect();
            return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
        }
        if (floatingBtn) {
            const rect = floatingBtn.getBoundingClientRect();
            return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
        }
        return { x: window.innerWidth - 60, y: 40 };
    };

    window.createFlyingClone = function(sourceEl) {
        const rect = sourceEl.getBoundingClientRect();
        const clone = document.createElement('div');
        clone.classList.add('flying-clone');
        clone.style.width = rect.width + 'px';
        clone.style.height = rect.height + 'px';
        clone.style.left = rect.left + 'px';
        clone.style.top = rect.top + 'px';
        clone.innerHTML = sourceEl.innerHTML;
        document.body.appendChild(clone);
        return clone;
    };

    window.spawnParticles = function(x, y, count = 8) {
        const container = document.getElementById('particle-container');
        const colors = ['#10b981', '#34d399', '#6ee7b7', '#a7f3d0', '#fbbf24', '#f59e0b'];
        const particles = [];

        for (let i = 0; i < count; i++) {
            const p = document.createElement('div');
            p.classList.add('gsap-particle');
            const size = gsap.utils.random(6, 14);
            p.style.width = size + 'px';
            p.style.height = size + 'px';
            p.style.left = x + 'px';
            p.style.top = y + 'px';
            p.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            container.appendChild(p);
            particles.push(p);
        }

        particles.forEach((p, i) => {
            const angle = (i / count) * Math.PI * 2;
            const distance = gsap.utils.random(40, 100);
            
            gsap.to(p, {
                x: Math.cos(angle) * distance,
                y: Math.sin(angle) * distance,
                opacity: 0,
                scale: 0,
                duration: gsap.utils.random(0.6, 1.0),
                ease: 'power3.out',
                onComplete: () => p.remove()
            });
        });
    };

    window.spawnRipple = function(x, y) {
        const ripple = document.createElement('div');
        ripple.classList.add('cart-ripple');
        ripple.style.left = (x - 20) + 'px';
        ripple.style.top = (y - 20) + 'px';
        ripple.style.width = '40px';
        ripple.style.height = '40px';
        document.body.appendChild(ripple);

        gsap.to(ripple, {
            scale: 3,
            opacity: 0,
            duration: 0.7,
            ease: 'power2.out',
            onComplete: () => ripple.remove()
        });
    };

    window.spawnPriceFly = function(x, y, price) {
        const el = document.createElement('div');
        el.classList.add('price-fly');
        el.style.left = x + 'px';
        el.style.top = y + 'px';
        el.style.fontSize = '18px';
        el.textContent = '+$' + price;
        document.body.appendChild(el);

        gsap.fromTo(el, 
            { opacity: 1, y: 0, scale: 1 },
            { 
                opacity: 0, y: -60, scale: 1.5,
                duration: 1.2, ease: 'power2.out',
                onComplete: () => el.remove()
            }
        );
    };

    window.animateAddToCart = function(itemId, btnEvent) {
        const imgEl = document.getElementById('item-img-' + itemId);
        const cardEl = document.getElementById('item-card-' + itemId);
        if (!imgEl) return;

        const target = getCartTargetPos();
        const imgRect = imgEl.getBoundingClientRect();
        const startX = imgRect.left;
        const startY = imgRect.top;

        gsap.timeline()
            .to(cardEl, { scale: 0.95, duration: 0.1, ease: 'power2.in' })
            .to(cardEl, { scale: 1, duration: 0.4, ease: 'elastic.out(1, 0.4)' });

        const clone = createFlyingClone(imgEl);
        gsap.fromTo(clone, { scale: 1, rotation: 0 }, { scale: 1.2, duration: 0.15, ease: 'power2.out', yoyo: true, repeat: 1 });
        spawnParticles(startX + imgRect.width / 2, startY + imgRect.height / 2, 10);

        if (btnEvent) {
            const btnRect = btnEvent.currentTarget?.getBoundingClientRect();
            if (btnRect) {
                const priceEl = cardEl?.querySelector('.text-emerald-500');
                const priceText = priceEl?.textContent?.replace('$', '') || '';
                spawnPriceFly(btnRect.left - 30, btnRect.top - 10, priceText);
            }
        }

        const midX = (startX + target.x) / 2;
        const midY = Math.min(startY, target.y) - 120;

        gsap.to(clone, {
            duration: 0.75,
            ease: 'power2.inOut',
            motionPath: {
                path: [
                    { x: 0, y: 0 },
                    { x: midX - startX, y: midY - startY },
                    { x: target.x - startX, y: target.y - startY }
                ],
                curviness: 1.5
            },
            scale: 0.3,
            rotation: 360,
            opacity: 0.8,
            onComplete: () => {
                spawnParticles(target.x, target.y, 12);
                spawnRipple(target.x, target.y);
                const navCartBtn = document.querySelector('[x-data="navCart"] button');
                if (navCartBtn) {
                    gsap.timeline()
                        .to(navCartBtn, { scale: 1.4, duration: 0.15, ease: 'power2.out' })
                        .to(navCartBtn, { scale: 1, duration: 0.5, ease: 'elastic.out(1, 0.3)' });
                }
                const floatingBtn = document.querySelector('#floating-cart-btn button');
                if (floatingBtn) {
                    gsap.timeline()
                        .to(floatingBtn, { scale: 1.15, duration: 0.15, ease: 'power2.out' })
                        .to(floatingBtn, { scale: 1, duration: 0.5, ease: 'elastic.out(1, 0.3)' });
                }
                gsap.to(clone, { scale: 0, opacity: 0, duration: 0.2, onComplete: () => clone.remove() });
            }
        });
    };

    window.showGsapToast = function(message) {
        const toast = document.getElementById('gsap-toast');
        const msgEl = document.getElementById('gsap-toast-msg');
        if (!toast || !msgEl) return;
        msgEl.textContent = message;
        gsap.killTweensOf(toast);
        gsap.fromTo(toast, { opacity: 0, y: -20, scale: 0.8 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, ease: 'back.out(2)' });
        gsap.to(toast, { opacity: 0, y: -20, scale: 0.8, duration: 0.3, ease: 'power2.in', delay: 2.2 });
    };

    // ========== Alpine Components ==========

    window.initRestaurantAlpine = function() {
        if (!window.Alpine) return;

        if (!Alpine.store('restaurantState')) {
            Alpine.store('restaurantState', {
                addingItem: null
            });
        }

        Alpine.data('restaurantPage', () => ({
            activeCategory: '{{ $restaurant->menuCategories->first()->id ?? "" }}',
            cart: { items: {}, count: 0, total: 0, restaurant_id: null },
            
            async init() {
                await this.loadCart();
                console.log('restaurantPage initialized');
            },
            
            async loadCart() {
                try {
                    const res = await fetch('{{ route("cart.index") }}');
                    const data = await res.json();
                    this.cart = data || { items: {}, count: 0, total: 0 };
                } catch(e) {
                    console.error('Failed to load cart', e);
                }
            },
            
            getItemQty(itemId) {
                if (!this.cart || !this.cart.items) return 0;
                return this.cart.items[itemId] ? this.cart.items[itemId].quantity : 0;
            },
            
            async addToCart(itemId, btnEvent) {
                Alpine.store('restaurantState').addingItem = itemId;
                console.log('Adding to cart:', itemId);
                if (window.animateAddToCart) animateAddToCart(itemId, btnEvent);
                try {
                    const res = await fetch('{{ route("cart.add") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ menu_item_id: itemId, quantity: 1 })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.cart = data.cart;
                        if (window.showGsapToast) showGsapToast(data.message);
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
                    }
                } catch(e) {
                    console.error('Add to cart failed', e);
                }
                Alpine.store('restaurantState').addingItem = null;
            },
            
            async updateQty(itemId, qty) {
                try {
                    const res = await fetch('{{ route("cart.update") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ menu_item_id: itemId, quantity: qty })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.cart = data.cart;
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
                    }
                } catch(e) {}
            }
        }));

        Alpine.data('floatingCart', () => ({
            cart: { items: {}, count: 0, total: 0 },
            
            init() {
                this.loadCart();
                window.addEventListener('cart-updated', (e) => {
                    this.cart = e.detail;
                });
            },

            async loadCart() {
                try {
                    const res = await fetch('{{ route("cart.index") }}');
                    this.cart = await res.json();
                } catch(e) {}
            },

            toggleCart() {
                console.log('Toggling cart from floating button');
                window.dispatchEvent(new CustomEvent('toggle-cart'));
            }
        }));
    };

    if (window.Alpine) {
        window.initRestaurantAlpine();
    } else {
        document.addEventListener('alpine:init', window.initRestaurantAlpine);
    }
</script>

<div class="relative bg-white overflow-hidden">
    <!-- Cover Layer -->
    <div class="h-[500px] sm:h-80 lg:h-96 relative w-full">
        @if($restaurant->cover_image)
            <img src="{{ Storage::url($restaurant->cover_image) }}" alt="Cover" class="w-full h-full object-cover">
        @else
            <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&q=80" alt="Cover" class="w-full h-full object-cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 w-full px-4 sm:px-6 lg:px-8 pb-6 md:pb-8 max-w-7xl mx-auto flex flex-col md:flex-row md:items-end gap-4 md:gap-6">
            <div class="flex-shrink-0 relative w-32 h-32 md:w-40 md:h-40 bg-white rounded-3xl p-2 shadow-2xl transform translate-y-6 md:translate-y-8 z-10 border-4 border-white">
                @if($restaurant->logo)
                    <img src="{{ Storage::url($restaurant->logo) }}" alt="Logo" class="w-full h-full object-cover rounded-2xl">
                @else
                    <div class="w-full h-full bg-emerald-100 rounded-2xl flex items-center justify-center text-4xl font-black text-emerald-500 outfit">
                        {{ substr($restaurant->name, 0, 1) }}
                    </div>
                @endif
            </div>
            
            <div class="flex-1 pb-2">
                <h1 class="text-4xl md:text-5xl font-black outfit text-white">{{ $restaurant->name }}</h1>
                <p class="text-emerald-300 font-bold mt-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    {{ $restaurant->address ?? 'Location not specified' }}
                </p>
                <p class="text-gray-300 font-medium max-w-2xl mt-3 line-clamp-2 md:line-clamp-none">
                    {{ $restaurant->description }}
                </p>
            </div>
        </div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pb-24 relative z-0" x-data="restaurantPage()" x-init="loadCart()">

        {{-- GSAP Toast --}}
        <div id="gsap-toast" class="fixed top-24 left-1/2 -translate-x-1/2 z-[100] pointer-events-none" style="opacity:0; transform: translateX(-50%) translateY(-20px);">
            <div class="bg-gray-900 text-white px-6 py-3 rounded-2xl shadow-2xl shadow-gray-900/30 flex items-center gap-3 font-bold text-sm backdrop-blur-sm">
                <div class="w-8 h-8 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span id="gsap-toast-msg">Added to cart!</span>
            </div>
        </div>
        
        <div class="flex flex-col lg:flex-row gap-10">
            <!-- Sidebar Navigation -->
            <div class="w-full lg:w-1/4">
                <div class="sticky top-28 bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
                    <h3 class="font-black text-2xl outfit text-gray-900 mb-6">Menu</h3>
                    
                    <nav class="space-y-2">
                        @foreach($restaurant->menuCategories as $category)
                            <button 
                                @click="activeCategory = '{{ $category->id }}'; document.getElementById('category-{{ $category->id }}').scrollIntoView({behavior: 'smooth', block: 'start'})"
                                :class="{'bg-emerald-50 text-emerald-600 font-bold pl-6': activeCategory === '{{ $category->id }}', 'text-gray-600 font-medium hover:bg-gray-50 pl-4': activeCategory !== '{{ $category->id }}'}"
                                class="w-full text-left py-3 pr-4 rounded-xl transition-all relative group"
                            >
                                <span 
                                    :class="{'opacity-100': activeCategory === '{{ $category->id }}', 'opacity-0': activeCategory !== '{{ $category->id }}'}" 
                                    class="absolute left-2 top-1/2 transform -translate-y-1/2 w-1.5 h-6 bg-emerald-500 rounded-full transition-opacity">
                                </span>
                                {{ $category->name }}
                                <span class="float-right text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full group-hover:bg-white transition-colors">
                                    {{ $category->menuItems->count() }}
                                </span>
                            </button>
                        @endforeach
                    </nav>
                </div>
            </div>
            
            <!-- Menu Sections -->
            <div class="w-full lg:w-3/4">
                @forelse($restaurant->menuCategories as $category)
                    <div id="category-{{ $category->id }}" class="mb-16 scroll-mt-28" x-intersect.margin.-200px.0.0.0="activeCategory = '{{ $category->id }}'">
                        <h2 class="text-3xl font-black outfit text-gray-900 mb-8 pb-4 border-b-2 border-dashed border-gray-200">
                            {{ $category->name }}
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($category->menuItems as $item)
                                <div id="item-card-{{ $item->id }}" class="bg-white border border-gray-100 rounded-2xl p-4 flex gap-4 hover:shadow-xl transition-shadow group {{ !$item->is_available ? 'opacity-60 grayscale' : '' }}">
                                    <!-- Item Image -->
                                    <div id="item-img-{{ $item->id }}" class="w-24 h-24 flex-shrink-0 bg-gray-100 rounded-xl overflow-hidden relative">
                                        @if($item->image)
                                            <img src="{{ Storage::url($item->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                        
                                        @if(!$item->is_available)
                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center backdrop-blur-[1px]">
                                                <span class="text-white text-xs font-bold px-2 py-1 bg-gray-900/80 rounded border border-gray-700">Sold out</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-start">
                                                <h4 class="font-bold text-lg text-gray-900 group-hover:text-emerald-600 transition-colors leading-tight">{{ $item->name }}</h4>
                                                <span class="font-black text-emerald-500 whitespace-nowrap ml-2">${{ number_format($item->price, 2) }}</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1 line-clamp-2 font-medium">{{ $item->description }}</p>
                                        </div>
                                        
                                        @if($item->is_available)
                                        <div class="flex justify-end mt-3">
                                            @if(Auth::id() === $restaurant->user_id)
                                                <span class="text-xs font-bold text-amber-500 bg-amber-50 px-3 py-1 rounded-full border border-amber-100">Own Restaurant</span>
                                            @else
                                                <template x-if="getItemQty({{ $item->id }}) === 0">
                                                    <button 
                                                        id="add-btn-{{ $item->id }}"
                                                        @click="addToCart({{ $item->id }}, $event)"
                                                        :disabled="$store.restaurantState.addingItem === {{ $item->id }}"
                                                        class="bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white w-9 h-9 rounded-full flex items-center justify-center transition-all transform hover:scale-110 active:scale-95 shadow-sm hover:shadow-lg hover:shadow-emerald-500/30">
                                                        <svg x-show="$store.restaurantState.addingItem !== {{ $item->id }}" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                                        <svg x-show="$store.restaurantState.addingItem === {{ $item->id }}" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                                    </button>
                                                </template>
                                                <template x-if="getItemQty({{ $item->id }}) > 0">
                                                    <div class="flex items-center gap-1">
                                                        <button 
                                                            @click="updateQty({{ $item->id }}, getItemQty({{ $item->id }}) - 1)"
                                                            class="bg-red-50 hover:bg-red-100 text-red-500 w-8 h-8 rounded-full flex items-center justify-center transition-all active:scale-90">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path></svg>
                                                        </button>
                                                        <span class="w-8 text-center font-black text-gray-900" x-text="getItemQty({{ $item->id }})"></span>
                                                        <button 
                                                            @click="addToCart({{ $item->id }}, $event)"
                                                            class="bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white w-8 h-8 rounded-full flex items-center justify-center transition-all active:scale-90">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($category->menuItems->isEmpty())
                                <div class="col-span-full py-8 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                    <p class="text-gray-500 font-medium">No items yet in this category.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-20 text-center">
                        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-100 text-gray-400 mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h3 class="text-2xl font-black outfit text-gray-900 mb-2">Menu goes here!</h3>
                        <p class="text-gray-500 text-lg font-medium">This restaurant is still working on its tasty menu.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Floating Cart Button -->
<div id="floating-cart-btn" x-data="floatingCart()" class="sticky bottom-6 right-6 px-2 w-full  z-50" x-cloak>
    <template x-if="cart.count > 0">
        <button 
            @click="toggleCart()"
            class="bg-emerald-500 hover:bg-emerald-400 text-white px-6 py-4 rounded-md shadow-2xl shadow-emerald-500/40 font-bold flex items-center gap-3 transition-all transform   active:scale-95 w-full">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span x-text="cart.count + ' item' + (cart.count !== 1 ? 's' : '')"></span>
            <span class="bg-white/20 px-2 py-0.5 rounded-full text-sm">$<span x-text="cart.total.toFixed(2)"></span></span>
        </button>
    </template>
</div>

<!-- Particle container for GSAP burst effects -->
<div id="particle-container" class="fixed inset-0 pointer-events-none z-[99]"></div>

<style>
    .flying-clone {
        position: fixed;
        z-index: 9999;
        pointer-events: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(16, 185, 129, 0.5);
    }
    .gsap-particle {
        position: fixed;
        pointer-events: none;
        z-index: 9998;
        border-radius: 50%;
    }
    .cart-ripple {
        position: fixed;
        border-radius: 50%;
        border: 3px solid #10b981;
        pointer-events: none;
        z-index: 9997;
    }
    .price-fly {
        position: fixed;
        z-index: 9999;
        pointer-events: none;
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        color: #10b981;
        text-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    }
</style>
@endsection
