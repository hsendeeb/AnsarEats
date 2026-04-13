@extends('layouts.app')

@section('content')
<!-- Page Logic -->
<script>
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


    // ========== Alpine Components ==========

    window.createRatingComponent = function(initialRating, initialComment, postUrl, csrfToken) {
        return {
            rating: initialRating || 0,
            comment: initialComment || '',
            submitting: false,
            message: '',
            error: '',
            setRating(value) {
                this.rating = value;
            },
            async submitRating() {
                this.message = '';
                this.error = '';
                if (!this.rating) {
                    this.error = 'Please select a star rating.';
                    return;
                }
                this.submitting = true;
                try {
                    const res = await fetch(postUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            rating: this.rating,
                            comment: this.comment,
                        }),
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.message = data.message || 'Rating submitted successfully!';
                        this.error = '';
                    } else {
                        this.error = data.message || 'Unable to submit rating.';
                    }
                } catch (e) {
                    this.error = 'Something went wrong while submitting your rating.';
                } finally {
                    this.submitting = false;
                }
            }
        };
    };

    window.menuItemPricing = function(basePrice, variants, isOnSale, salePrice, discountPercentage) {
        return {
            basePrice: parseFloat(basePrice || 0),
            variants: variants && variants.options ? variants.options : [],
            variantType: variants && variants.type ? variants.type : null,
            isOnSale: !!isOnSale,
            salePrice: salePrice !== null && salePrice !== undefined && salePrice !== '' ? parseFloat(salePrice) : null,
            discountPercentage: discountPercentage !== null && discountPercentage !== undefined && discountPercentage !== '' ? parseFloat(discountPercentage) : null,
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

                if (this.discountPercentage !== null && !Number.isNaN(this.discountPercentage) && this.discountPercentage > 0) {
                    return Math.max(numericPrice * (1 - (this.discountPercentage / 100)), 0);
                }

                if (!this.hasVariants && this.salePrice !== null && !Number.isNaN(this.salePrice) && this.salePrice < this.basePrice) {
                    return this.salePrice;
                }

                return numericPrice;
            },
            get hasActiveSale() {
                return this.isOnSale
                    && (
                        (this.discountPercentage !== null && !Number.isNaN(this.discountPercentage) && this.discountPercentage > 0)
                        || (!this.hasVariants && this.salePrice !== null && !Number.isNaN(this.salePrice) && this.salePrice < this.basePrice)
                    );
            },
            get currentOption() {
                if (!this.hasVariants) return null;
                return this.variants[this.selectedIndex] || this.variants[0];
            },
            get currentOriginalPrice() {
                if (this.currentOption && this.currentOption.price !== undefined && this.currentOption.price !== null) {
                    return parseFloat(this.currentOption.price);
                }
                return this.basePrice;
            },
            get currentPrice() {
                return this.hasActiveSale
                    ? this.calculateDiscountedPrice(this.currentOriginalPrice)
                    : this.currentOriginalPrice;
            },
            get originalPrice() {
                return this.hasActiveSale ? this.currentOriginalPrice : null;
            },
            get formattedPrice() {
                return this.formatCurrency(this.currentPrice);
            },
            get formattedOriginalPrice() {
                return this.originalPrice !== null ? this.formatCurrency(this.originalPrice) : '';
            },
            get savingsAmount() {
                return this.hasActiveSale ? Math.max(this.originalPrice - this.currentPrice, 0) : 0;
            },
            get formattedSavings() {
                return this.formatCurrency(this.savingsAmount);
            },
            get currentLabel() {
                return this.currentOption ? this.currentOption.label : null;
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
            }
        };
    };

    window.initRestaurantAlpine = function() {
        if (!window.Alpine) return;

        if (!Alpine.store('restaurantState')) {
            Alpine.store('restaurantState', {
                addingItem: null
            });
        }

        if (!Alpine.data('restaurantPage')) {
            Alpine.data('restaurantPage', () => ({
                activeCategory: '{{ $restaurant->menuCategories->first()->id ?? "" }}',
                cart: { items: {}, count: 0, total: 0, restaurant_id: null },
                
                normalizeCart(cart) {
                    const normalized = cart || {};
                    const items = normalized.items && !Array.isArray(normalized.items)
                        ? normalized.items
                        : {};

                    return {
                        items,
                        count: normalized.count || 0,
                        total: normalized.total || 0,
                        restaurant_id: normalized.restaurant_id || null,
                    };
                },
                
                async init() {
                    console.log('restaurantPage init starting...');
                    await this.loadCart();

                    window.addEventListener('cart-updated', (e) => {
                        this.cart = this.normalizeCart(e.detail);
                    });

                    this.$watch('activeCategory', val => {
                        this.scrollNavToActive(val);
                    });

                    window.addEventListener('resize', () => {
                        this.scrollNavToActive(this.activeCategory);
                    });

                    let isScrolling = false;
                    window.addEventListener('scroll', () => {
                        if (!isScrolling) {
                            window.requestAnimationFrame(() => {
                                this.onScroll();
                                isScrolling = false;
                            });
                            isScrolling = true;
                        }
                    });
                    
                    console.log('restaurantPage initialized');
                },

                onScroll() {
                    if (this.scrollingToCategory) return;
                    const sections = Array.from(document.querySelectorAll('[id^="category-"]'));
                    if (!sections.length) return;

                    let currentActive = this.activeCategory;
                    // Sticky bar height (approx 60px) + Navbar height (80px) = 140px, adding 40px buffer
                    const offset = window.innerWidth < 1024 ? 180 : 160;
                    
                    // We select the first section that hasn't scrolled completely past the offset mark
                    const activeSection = sections.find(section => {
                        const rect = section.getBoundingClientRect();
                        return rect.bottom > offset;
                    });

                    if (activeSection) {
                        currentActive = activeSection.id.replace('category-', '');
                    }

                    if (this.activeCategory !== currentActive) {
                        this.activeCategory = currentActive;
                    }
                },

                scrollNavToActive(id) {
                    const nav = this.$refs.nav;
                    if (!nav) return;
                    const el = nav.querySelector(`[data-cat-id='${id}']`);
                    if (!el) return;

                    // Only auto-scroll on mobile/tablet horizontal layout
                    if (window.innerWidth < 1024) {
                        const navWidth = nav.offsetWidth;
                        const elWidth = el.offsetWidth;
                        const elOffset = el.offsetLeft;
                        const targetScroll = elOffset - (navWidth / 2) + (elWidth / 2);
                        nav.scrollTo({ left: Math.max(0, targetScroll), behavior: 'smooth' });
                    }
                },

                scrollToCategory(id) {
                    this.scrollingToCategory = true;
                    const el = document.getElementById(id);
                    if (!el) return;
                    // Sticky bar height (approx 60px) + Navbar height (80px) = 140px
                    const offset = window.innerWidth < 1024 ? 140 : 120; 
                    const elementPosition = el.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                    
                    // Reset scrolling flag after animation finishes
                    setTimeout(() => { this.scrollingToCategory = false; }, 800);
                },
                
                async loadCart() {
                    try {
                        const res = await fetch('{{ route("cart.index") }}');
                        const data = await res.json();
                        this.cart = this.normalizeCart(data);
                    } catch(e) {
                        console.error('Failed to load cart', e);
                    }
                },
                
                makeItemKey(itemId, variantLabel, variantPrice) {
                    const label = (variantLabel || '').toString().trim().toLowerCase();
                    const price = (variantPrice !== undefined && variantPrice !== null)
                        ? parseFloat(variantPrice).toFixed(2)
                        : '0.00';
                    return `${itemId}|${label}|${price}`;
                },

                getItemQty(itemId, variantLabel, variantPrice) {
                    if (!this.cart || !this.cart.items) return 0;
                    const key = this.makeItemKey(itemId, variantLabel, variantPrice);
                    return this.cart.items[key] ? this.cart.items[key].quantity : 0;
                },
                
                async addToCart(itemId, btnEvent) {
                    Alpine.store('restaurantState').addingItem = itemId;
                    console.log('Adding to cart:', itemId);
                    if (window.animateAddToCart) animateAddToCart(itemId, btnEvent);

                    const payload = { menu_item_id: itemId, quantity: 1 };
                    if (btnEvent && btnEvent.currentTarget) {
                        const el = btnEvent.currentTarget;
                        const variantLabel = el.getAttribute('data-variant-label');
                        const variantPrice = el.getAttribute('data-variant-price');
                        if (variantLabel && variantPrice) {
                            payload.variant_label = variantLabel;
                            payload.variant_price = parseFloat(variantPrice);
                        }
                    }

                    try {
                        const res = await fetch('{{ route("cart.add") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.cart = this.normalizeCart(data.cart);
                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
                            window.showAppToast({ message: data.message || 'Added to cart!', type: 'success' });
                        } else {
                            window.showAppToast({
                                message: data.message || 'Could not add item.',
                                type: 'error'
                            });
                        }
                    } catch(e) {
                        console.error('Add to cart failed', e);
                        window.showAppToast({
                            message: 'Network error. Please try again.',
                            type: 'error'
                        });
                    } finally {
                        Alpine.store('restaurantState').addingItem = null;
                    }
                },
                
                async updateQty(itemKey, qty) {
                    try {
                        const res = await fetch('{{ route("cart.update") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ item_key: itemKey, quantity: qty })
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.cart = this.normalizeCart(data.cart);
                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
                        }
                    } catch(e) {}
                }
            }));
        }

        if (!Alpine.data('floatingCart')) {
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
        }
        console.log('initRestaurantAlpine completed successfully');
    };

    if (window.Alpine) {
        window.initRestaurantAlpine();
    } else {
        document.addEventListener('alpine:init', window.initRestaurantAlpine);
    }
</script>

<div class="relative bg-white">
    <!-- Cover Layer -->
    <div class="h-[460px] sm:h-[420px] md:h-[460px] lg:h-96 relative w-full">
        @if($restaurant->cover_image)
            <img src="{{ Storage::url($restaurant->cover_image) }}" alt="Cover" class="w-full h-full object-cover">
        @else
            <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&q=80" alt="Cover" class="w-full h-full object-cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 w-full px-4 sm:px-6 lg:px-8 pb-6 md:pb-8 max-w-7xl mx-auto flex flex-col md:flex-row md:items-end gap-4 md:gap-6">
            <div class="flex-shrink-0 relative w-32 h-32 md:w-40 md:h-40 bg-white rounded-3xl p-2 shadow-2xl transform translate-y-6 md:translate-y-8 z-20 border-4 border-white mb-4">
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
                <div class="flex items-center gap-3 mt-2">
                    @php
                        $mapsQuery = $restaurant->latitude && $restaurant->longitude 
                            ? "{$restaurant->latitude},{$restaurant->longitude}" 
                            : urlencode($restaurant->address);
                        $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$mapsQuery}";
                    @endphp
                    <a href="{{ $mapsUrl }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-2xl text-emerald-300 hover:text-white border border-white/10 hover:border-white/20 transition-all duration-300 group shadow-lg shadow-black/20 max-w-full">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/20 flex items-center justify-center group-hover:scale-110 transition-transform shrink-0">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <span class="text-sm font-black tracking-tight truncate max-w-[120px] sm:max-w-[200px] md:max-w-[300px]" title="{{ $restaurant->address }}">{{ $restaurant->address ?? 'Location not specified' }}</span>
                    </a>
                    <span class="shrink-0 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $restaurant->isOpenNow() ? 'bg-emerald-400/20 text-emerald-400 border border-emerald-400/30' : 'bg-red-400/20 text-red-400 border border-red-400/30' }}">
                        {{ $restaurant->isOpenNow() ? 'Open Now' : 'Closed' }}
                    </span>
                </div>
                <p class="text-gray-300 font-medium max-w-2xl mt-3 line-clamp-2 md:line-clamp-none">
                    {{ $restaurant->description }}
                </p>
            </div>
        </div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-24 pb-24 relative z-0" x-data="restaurantPage" x-cloak>


        <div class="flex flex-col lg:flex-row gap-10" x-data="{ isStuck: false }" @scroll.window="isStuck = window.pageYOffset > 400">
            <!-- Sidebar / Mobile Tabs Navigation -->
            <div class="w-screen lg:w-1/4 sticky top-20 lg:top-28 z-[45] bg-white/95 backdrop-blur-md lg:bg-transparent lg:backdrop-blur-none transition-all duration-300 -mx-4 sm:-mx-6 lg:mx-0 px-4 sm:px-6 lg:px-0 border-b border-gray-100 lg:border-none"
                 :class="isStuck ? 'shadow-xl shadow-gray-900/5' : 'shadow-none'">
                <div class="bg-white lg:border border-gray-100 lg:rounded-3xl p-0 lg:p-6 shadow-none transition-all duration-300">
                    <h3 class="hidden lg:block font-black text-2xl outfit text-gray-900 mb-6">Menu</h3>
                    
                    <nav x-ref="nav" class="relative flex flex-row lg:flex-col overflow-x-auto no-scrollbar scroll-smooth p-0 lg:p-0">

                        @foreach($restaurant->menuCategories as $category)
                            <button 
                                type="button"
                                data-cat-id="{{ $category->id }}"
                                @click="activeCategory = '{{ $category->id }}'; scrollToCategory('category-{{ $category->id }}')"
                                :class="activeCategory === '{{ $category->id }}' 
                                    ? 'text-emerald-600 font-bold border-b-[3px] border-emerald-500 lg:border-b-0 lg:border-l-[3px] lg:bg-emerald-50' 
                                    : 'text-gray-500 font-medium border-b-[3px] border-transparent lg:border-b-0 lg:border-l-[3px] lg:border-transparent hover:text-gray-800 lg:hover:bg-gray-50'"
                                class="relative flex-shrink-0 flex items-center justify-between gap-3 px-6 py-5 lg:py-3 transition-all group lg:w-full text-left lg:rounded-xl bg-transparent"
                            >
                                <span class="whitespace-nowrap">{{ $category->name }}</span>
                                <span 
                                    :class="activeCategory === '{{ $category->id }}' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-500'"
                                    class="hidden lg:block text-[10px] font-black px-2 py-1 rounded-full transition-colors">
                                    {{ $category->menuItems->count() }}
                                </span>
                            </button>
                        @endforeach
                    </nav>

                    @if($restaurant->operating_hours)
                        <div class="hidden lg:block mt-8 pt-6 border-t border-gray-100">
                            <h4 class="font-black text-xs uppercase tracking-widest text-gray-400 mb-4 px-2">Operating Hours</h4>
                            <div class="space-y-3">
                                @php
                                    $today = strtolower(now()->format('l'));
                                @endphp
                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    @php
                                        $dayHours = $restaurant->operating_hours[$day] ?? null;
                                        $isToday = $today === $day;
                                    @endphp
                                    <div class="flex items-center justify-between px-2 {{ $isToday ? 'bg-emerald-50 py-2 rounded-xl border border-emerald-100' : 'text-gray-600' }}">
                                        <span class="text-xs font-bold uppercase {{ $isToday ? 'text-emerald-700' : 'text-gray-500' }}">{{ substr($day, 0, 3) }}</span>
                                        <span class="text-[11px] font-black {{ $isToday ? 'text-emerald-600' : 'text-gray-900' }}">
                                            @if($dayHours && !($dayHours['closed'] ?? false))
                                                {{ \Carbon\Carbon::parse($dayHours['open'])->format('g:i A') }} - {{ \Carbon\Carbon::parse($dayHours['close'])->format('g:i A') }}
                                            @else
                                                <span class="text-red-400">Closed</span>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="w-full lg:w-3/4 space-y-10">
                <!-- Menu Sections -->
                @forelse($restaurant->menuCategories as $category)
                    <div id="category-{{ $category->id }}" class="mb-16 scroll-mt-36 lg:scroll-mt-28">
                        <h2 class="text-3xl font-black outfit text-gray-900 mb-8 pb-4 border-b-2 border-dashed border-gray-200">
                            {{ $category->name }}
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($category->menuItems as $item)
                                <div id="item-card-{{ $item->id }}" class="bg-white border border-gray-100 rounded-2xl p-4 flex gap-4 hover:shadow-xl transition-shadow group {{ !$item->is_available ? 'opacity-60 grayscale' : '' }}"
                                     x-data="menuItemPricing({{ $item->price }}, @js($item->variants), {{ $item->is_on_sale ? 'true' : 'false' }}, {{ Js::from($item->sale_price) }}, {{ Js::from($item->saleDiscountPercentage()) }})">
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
                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center backdrop-blur-[1px] z-10">
                                                <span class="text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 bg-gray-900/80 rounded border border-gray-700">Sold out</span>
                                            </div>
                                        @elseif(!$restaurant->isOpenNow())
                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center backdrop-blur-[1px] z-10">
                                                <span class="text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 bg-red-900/80 rounded border border-red-700">Closed</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-start gap-x-2 gap-y-1">
                                                <h4 class="min-w-0 flex-1 font-bold text-lg text-gray-900 group-hover:text-emerald-600 transition-colors leading-tight break-words flex items-center gap-2">
                                                    {{ $item->name }}
                                                </h4>
                                                <div class="shrink-0 text-right whitespace-nowrap">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <span x-show="hasActiveSale" x-cloak class="text-sm font-bold text-gray-400 line-through" x-text="formattedOriginalPrice"></span>
                                                        <span class="font-black text-emerald-500" x-text="formattedPrice">
                                                            ${{ number_format($item->price, 2) }}
                                                        </span>
                                                    </div>
                                                    <p x-show="hasActiveSale" x-cloak class="mt-0.5 text-[11px] font-bold text-orange-500" x-text="`Save ${formattedSavings}`"></p>
                                                </div>
                                            </div>
                                            <div class="mt-1" x-data="{
                                                expanded: false,
                                                canExpand: false,
                                                isSmall() { return window.matchMedia && window.matchMedia('(max-width: 767px)').matches; },
                                                clampStyle(lines = 2) {
                                                    return `display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:${lines};overflow:hidden;`;
                                                },
                                                check() {
                                                    // Desktop/tablet: never clamp, never show button
                                                    if (!this.isSmall()) { this.canExpand = false; this.expanded = true; return; }

                                                    this.$nextTick(() => {
                                                        const el = this.$refs.desc;
                                                        if (!el) return;

                                                        // Force collapsed style for measurement
                                                        const prevExpanded = this.expanded;
                                                        this.expanded = false;

                                                        this.$nextTick(() => {
                                                            this.canExpand = el.scrollHeight > el.clientHeight + 1;
                                                            this.expanded = prevExpanded;
                                                        });
                                                    });
                                                }
                                            }"
                                            x-init="check(); window.addEventListener('resize', () => check())">
                                                <p x-ref="desc"
                                                   class="text-sm text-gray-500 font-medium whitespace-normal break-words"
                                                   :style="(!expanded && isSmall()) ? (clampStyle(2) + 'word-break:break-word;overflow-wrap:anywhere;') : 'word-break:break-word;overflow-wrap:anywhere;'">{{ $item->description }}</p>
                                                <button type="button"
                                                        x-show="canExpand"
                                                        x-cloak
                                                        @click="expanded = !expanded"
                                                        class="mt-1 text-xs font-black text-gray-500 hover:text-emerald-600 transition-colors">
                                                    <span x-text="expanded ? 'See less' : 'See more'"></span>
                                                </button>
                                            </div>
                                            @if($item->is_featured)
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center text-amber-500 bg-amber-50 px-1.5 py-0.5 rounded text-[9px] uppercase font-black tracking-widest border border-amber-100 whitespace-nowrap" title="Featured Item">
                                                        <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                        Featured
                                                    </span>
                                                </div>
                                            @endif


                                        </div>
                                        
                                        @if($item->is_available && $restaurant->isOpenNow())
                                        <div x-show="hasVariants" class="mt-3">
                                            <p class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1" x-text="variantType ? variantType : 'Option'"></p>
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="(opt, idx) in variants" :key="idx">
                                                    <button type="button"
                                                            @click="selectedIndex = idx"
                                                            class="px-3 py-1.5 rounded-full text-[11px] font-semibold border transition-all"
                                                            :class="selectedIndex === idx 
                                                                ? 'bg-emerald-500 text-white border-emerald-500 shadow-sm' 
                                                                : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'">
                                                        <span x-text="opt.label"></span>
                                                        <span x-show="hasActiveSale" x-cloak class="ml-1 text-[10px] text-gray-400 line-through" x-text="formattedOptionOriginalPrice(opt)"></span>
                                                        <span class="ml-1 opacity-80" :class="hasActiveSale ? 'font-bold text-emerald-500 opacity-100' : ''" x-text="hasActiveSale ? formattedOptionSalePrice(opt) : formattedOptionOriginalPrice(opt)"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex justify-end mt-3">
                                            @if(Auth::id() === $restaurant->user_id)
                                                <span class="text-xs font-bold text-amber-500 bg-amber-50 px-3 py-1 rounded-full border border-amber-100">Own Restaurant</span>
                                            @else
                                                <template x-if="getItemQty({{ $item->id }}, currentLabel, currentPrice) === 0">
                                                    <button 
                                                        id="add-btn-{{ $item->id }}"
                                                        @click="addToCart({{ $item->id }}, $event)"
                                                        :disabled="$store.restaurantState.addingItem === {{ $item->id }}"
                                                        class="bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white w-9 h-9 rounded-full flex items-center justify-center transition-all transform hover:scale-110 active:scale-95 shadow-sm hover:shadow-lg hover:shadow-emerald-500/30"
                                                        :data-variant-label="currentLabel || null"
                                                        :data-variant-price="currentPrice.toFixed(2)">
                                                        <svg x-show="$store.restaurantState.addingItem !== {{ $item->id }}" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                                        <svg x-show="$store.restaurantState.addingItem === {{ $item->id }}" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                                    </button>
                                                </template>
                                                <template x-if="getItemQty({{ $item->id }}, currentLabel, currentPrice) > 0">
                                                    <div class="flex items-center gap-1">
                                                        <button 
                                                            @click="updateQty(makeItemKey({{ $item->id }}, currentLabel, currentPrice), getItemQty({{ $item->id }}, currentLabel, currentPrice) - 1)"
                                                            class="bg-red-50 hover:bg-red-100 text-red-500 w-8 h-8 rounded-full flex items-center justify-center transition-all active:scale-90">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path></svg>
                                                        </button>
                                                        <span class="w-8 text-center font-black text-gray-900" x-text="getItemQty({{ $item->id }}, currentLabel, currentPrice)"></span>
                                                        <button 
                                                            @click="addToCart({{ $item->id }}, $event)"
                                                            class="bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white w-8 h-8 rounded-full flex items-center justify-center transition-all active:scale-90"
                                                            :data-variant-label="currentLabel || null"
                                                            :data-variant-price="currentPrice.toFixed(2)">
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

                @if($restaurant->operating_hours)
                    <div class="lg:hidden bg-gray-50 shadow-sm border border-gray-100 rounded-[2.5rem] p-5 mb-10 group overflow-hidden">
                        <div class="flex flex-col sm:flex-row items-center gap-6">
                            <div class="flex-shrink-0 flex sm:flex-col items-center gap-2 px-6 py-3 bg-white shadow-sm border border-gray-100 rounded-3xl">
                                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div class="text-left sm:text-center">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 leading-none">Working</p>
                                    <p class="text-lg font-black outfit text-gray-900 leading-tight">Hours</p>
                                </div>
                            </div>
                            
                            <div class="flex-1 w-full overflow-x-auto scrollbar-hide">
                                <div class="flex items-center gap-3 min-w-max py-2">
                                    @php
                                        $today = strtolower(now()->format('l'));
                                    @endphp
                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                        @php
                                            $dayHours = $restaurant->operating_hours[$day] ?? null;
                                            $isToday = $today === $day;
                                        @endphp
                                        <div class="relative px-5 py-4 rounded-[2rem] flex flex-col items-center justify-center min-w-[120px] transition-all duration-300 {{ $isToday ? 'bg-emerald-500 text-white shadow-xl shadow-emerald-500/30 scale-105 z-10' : 'bg-white text-gray-600 border border-gray-50 hover:border-emerald-200 hover:shadow-md' }}">
                                            @if($isToday)
                                                <div class="absolute -top-2 left-1/2 -translate-x-1/2 bg-white text-emerald-600 text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter shadow-sm border border-emerald-100">Today</div>
                                            @endif
                                            <span class="text-[10px] font-bold uppercase tracking-widest {{ $isToday ? 'text-emerald-100' : 'text-gray-400' }}">{{ substr($day, 0, 3) }}</span>
                                            <span class="text-[11px] font-black mt-1 whitespace-nowrap">
                                                @if($dayHours && !($dayHours['closed'] ?? false))
                                                    {{ \Carbon\Carbon::parse($dayHours['open'])->format('g:i A') }} <span class="opacity-50 mx-0.5">-</span> {{ \Carbon\Carbon::parse($dayHours['close'])->format('g:i A') }}
                                                @else
                                                    <span class="{{ $isToday ? 'text-emerald-100/80' : 'text-red-400' }}">Closed</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Rating & Reviews --}}
                <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm"
                     x-data='createRatingComponent({{ $userRating->rating ?? 0 }}, @json($userRating->comment ?? ""), "{{ route('restaurant.rate', $restaurant) }}", "{{ csrf_token() }}")'>
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-black outfit text-gray-900 mb-1">Customer ratings</h2>
                            <p class="text-sm text-gray-500 font-medium mb-3">See what people think about this place.</p>
                            <div>
                                @include('layouts.partials.star-rating', [
                                    'rating' => round($restaurant->ratings_avg_rating ?? 0, 1),
                                    'count' => $restaurant->ratings_count ?? 0,
                                    'size' => 'lg',
                                    'showText' => true,
                                ])
                            </div>
                        </div>
                        <div class="mt-2 md:mt-0">
                            @auth
                                @if(auth()->id() === $restaurant->user_id)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-600 border border-amber-100">
                                        You are the owner · Ratings disabled
                                    </span>
                                @else
                                    <span class="text-xs font-semibold text-gray-500">
                                        @if($userRating)
                                            You rated this restaurant {{ $userRating->rating }} ★
                                        @else
                                            Be the first to rate this restaurant!
                                        @endif
                                    </span>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-600 hover:text-emerald-500">
                                    <span>Sign in to leave a rating</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            @endauth
                        </div>
                    </div>

                    @auth
                        @if(auth()->id() !== $restaurant->user_id)
                            <div class="mt-4 border-t border-dashed border-gray-200 pt-4">
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Your rating</p>
                                <div class="flex items-center gap-2 mb-3">
                                    <template x-for="star in [1,2,3,4,5]" :key="star">
                                        <button type="button"
                                                @click="setRating(star)"
                                                class="focus:outline-none">
                                            <svg class="w-6 h-6"
                                                 :class="rating >= star ? 'text-amber-400' : 'text-gray-200'"
                                                 fill="currentColor"
                                                 viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                        </button>
                                    </template>
                                    <span class="text-xs font-semibold text-gray-500" x-text="rating ? rating + ' / 5' : 'Tap to rate'"></span>
                                </div>
                                <textarea
                                    x-model="comment"
                                    rows="2"
                                    maxlength="500"
                                    class="w-full text-sm border border-gray-200 rounded-2xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                                    placeholder="Share a quick comment (optional)"></textarea>
                                <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <button
                                        type="button"
                                        @click="submitRating()"
                                        :disabled="submitting"
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-2xl text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-400 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
                                        <svg x-show="submitting" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span x-text="submitting ? 'Submitting...' : 'Submit rating'"></span>
                                    </button>
                                    <div class="text-xs font-medium">
                                        <span x-show="message" x-text="message" class="text-emerald-600"></span>
                                        <span x-show="error" x-text="error" class="text-red-500"></span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endauth

                    @if($restaurant->ratings->isNotEmpty())
                        <div class="mt-6 border-t border-dashed border-gray-200 pt-4">
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Recent reviews</p>
                            <div class="space-y-4 max-h-72 overflow-y-auto pr-1">
                                @foreach($restaurant->ratings as $rating)
                                    <div class="flex gap-3">
                                        <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-[11px] font-black text-emerald-700 outfit flex-shrink-0">
                                            {{ strtoupper(substr($rating->user->name ?? 'G', 0, 1)) }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-sm font-semibold text-gray-900">
                                                    {{ $rating->user->name ?? 'Guest' }}
                                                </p>
                                                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest whitespace-nowrap">
                                                    {{ $rating->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            <div class="mt-1">
                                                @include('layouts.partials.star-rating', [
                                                    'rating' => $rating->rating,
                                                    'count' => 1,
                                                    'size' => 'sm',
                                                    'showText' => false,
                                                ])
                                            </div>
                                            @if($rating->comment)
                                                <p class="mt-1 text-sm text-gray-600">
                                                    {{ $rating->comment }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Cart Button -->
<div id="floating-cart-btn" x-data="floatingCart" class="fixed bottom-6 right-6 left-6 md:left-auto md:w-80 z-50" x-cloak>
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
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection
