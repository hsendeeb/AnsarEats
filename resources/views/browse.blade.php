@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors">

    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="mb-4">
                <h1 class="text-3xl outfit font-black text-gray-900 tracking-tight">Browse by Category</h1>
                <p class="text-gray-500 font-medium text-sm mt-1">Explore items from all restaurants by category</p>
            </div>

            <!-- Horizontal Category Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide -mx-1 px-1"
                 id="category-pills-container">
                @foreach($categories as $cat)
                    <button
                        onclick="selectCategory('{{ $cat['slug'] }}')"
                        id="pill-{{ $cat['slug'] }}"
                        class="category-pill flex-shrink-0 flex items-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-200 border whitespace-nowrap
                               {{ $category === $cat['slug']
                                  ? 'bg-emerald-500 text-white border-emerald-500 shadow-lg shadow-emerald-500/25'
                                  : 'bg-white text-gray-600 border-gray-200 hover:border-emerald-400 hover:text-emerald-600 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700' }}">
                        <span>{{ $cat['emoji'] }}</span>
                        <span>{{ $cat['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Results count -->
        <p class="text-sm font-bold text-gray-400 mb-8 uppercase tracking-widest" id="results-label">
            Showing <span id="results-count">{{ $items->count() }}</span> items
        </p>

        <div id="items-grid"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 transition-opacity duration-300">
            @forelse($items as $meal)
                <div id="browse-card-{{ $meal->id }}" class="bg-white border border-gray-100 rounded-2xl p-4 flex gap-4 hover:shadow-xl transition-shadow group overflow-hidden h-full"
                     x-data="menuItemPricing({{ $meal->price }}, @js($meal->variants ?? []))">
                    
                    <!-- Item Image -->
                    <div id="browse-img-{{ $meal->id }}" class="w-24 h-24 flex-shrink-0 bg-gray-100 rounded-xl overflow-hidden relative">
                        <a href="{{ route('restaurant.show', $meal->menuCategory->restaurant) }}#meal-{{ $meal->id }}" class="block w-full h-full">
                            @if($meal->image)
                                <img src="{{ Storage::url($meal->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                        </a>

                        <!-- Restaurant Mini Badge Overlay -->
                        <div class="absolute bottom-1 left-1 flex items-center gap-1 bg-white/90 backdrop-blur-md pl-1 pr-1.5 py-0.5 rounded-full shadow-md z-10 pointer-events-none">
                            <div class="w-4 h-4 rounded-full overflow-hidden bg-gray-100 flex-shrink-0 border border-white">
                                @if($meal->menuCategory->restaurant->logo)
                                    <img src="{{ Storage::url($meal->menuCategory->restaurant->logo) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-emerald-100 text-emerald-600 text-[8px] font-bold">{{ substr($meal->menuCategory->restaurant->name,0,1) }}</div>
                                @endif
                            </div>
                            <span class="text-[8px] font-bold text-gray-900 truncate max-w-[60px]">{{ $meal->menuCategory->restaurant->name }}</span>
                        </div>
                    </div>
                    
                    <!-- Details -->
                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                        <div>
                            <div class="flex flex-wrap items-start justify-between gap-x-2 gap-y-1">
                                <a href="{{ route('restaurant.show', $meal->menuCategory->restaurant) }}#meal-{{ $meal->id }}" class="min-w-0 flex-1">
                                    <h4 class="font-bold text-lg text-gray-900 group-hover:text-emerald-600 transition-colors leading-tight break-words flex flex-wrap items-center gap-2">
                                        {{ $meal->name }}
                                        @if($meal->is_featured)
                                            <span class="inline-flex items-center text-amber-500 bg-amber-50 px-1.5 py-0.5 rounded text-[9px] uppercase font-black tracking-widest border border-amber-100 whitespace-nowrap">
                                                Featured
                                            </span>
                                        @endif
                                    </h4>
                                </a>
                                <span class="shrink-0 font-black text-emerald-500 whitespace-nowrap" x-text="formattedPrice">
                                    ${{ number_format($meal->price, 2) }}
                                </span>
                            </div>

                            <!-- Description (Alpine Responsive Collapse) -->
                            <div class="mt-1" x-data="{
                                expanded: false,
                                canExpand: false,
                                isSmall() { return window.matchMedia && window.matchMedia('(max-width: 767px)').matches; },
                                clampStyle(lines = 2) { return `display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:${lines};overflow:hidden;`; },
                                check() {
                                    if (!this.isSmall()) { this.canExpand = false; this.expanded = true; return; }
                                    this.$nextTick(() => {
                                        const el = this.$refs.desc;
                                        if (!el) return;
                                        const prevExpanded = this.expanded;
                                        this.expanded = false;
                                        this.$nextTick(() => {
                                            this.canExpand = el.scrollHeight > el.clientHeight + 1;
                                            this.expanded = prevExpanded;
                                        });
                                    });
                                }
                            }" x-init="check(); window.addEventListener('resize', () => check())">
                                <p x-ref="desc" class="text-sm text-gray-500 font-medium whitespace-normal break-words"
                                   :style="(!expanded && isSmall()) ? (clampStyle(2) + 'word-break:break-word;overflow-wrap:anywhere;') : 'word-break:break-word;overflow-wrap:anywhere;'">{{ $meal->description }}</p>
                                <button type="button" x-show="canExpand" x-cloak @click="expanded = !expanded" class="mt-1 text-xs font-black text-gray-500 hover:text-emerald-600 transition-colors">
                                    <span x-text="expanded ? 'See less' : 'See more'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Variants -->
                        <div x-show="hasVariants" x-cloak class="mt-3">
                            <p class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1" x-text="variantType ? variantType : 'Option'"></p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(opt, idx) in variants" :key="idx">
                                    <button type="button" @click="selectedIndex = idx"
                                            class="px-3 py-1.5 rounded-full text-[11px] font-semibold border transition-all"
                                            :class="selectedIndex === idx ? 'bg-emerald-500 text-white border-emerald-500 shadow-sm' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'">
                                        <span x-text="opt.label"></span>
                                        <span class="ml-1 opacity-80" x-text="'· $' + parseFloat(opt.price).toFixed(2)"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <div class="flex justify-end mt-3">
                            @if(Auth::id() === ($meal->menuCategory->restaurant->user_id ?? null))
                                <span class="text-xs font-bold text-amber-500 bg-amber-50 px-3 py-1 rounded-full border border-amber-100 flex-shrink-0 self-end">Own Restaurant</span>
                            @else
                                <button @click="browseAddToCart({{ $meal->id }}, currentPrice, $event, currentLabel)"
                                        class="bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white w-9 h-9 rounded-full flex items-center justify-center transition-all transform hover:scale-110 active:scale-95 shadow-sm hover:shadow-lg hover:shadow-emerald-500/30">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-24 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 text-gray-300 mb-5">
                        <span class="text-4xl">🍽️</span>
                    </div>
                    <h3 class="text-2xl outfit font-black text-gray-900 mb-2">No items found</h3>
                    <p class="text-gray-500 font-medium">Try a different category!</p>
                </div>
            @endforelse
        </div>

        <!-- Load More Button -->
        <div id="load-more-container" class="mt-12 text-center {{ $items->hasMorePages() ? '' : 'hidden' }}">
            <button id="load-more-btn" onclick="loadMoreItems()" class="inline-flex items-center gap-2 px-8 py-4 bg-emerald-50 text-emerald-600 font-black rounded-full hover:bg-emerald-500 hover:text-white transition-all shadow-sm hover:shadow-xl hover:shadow-emerald-500/30 group">
                <span>Load More</span>
                <svg class="w-5 h-5 group-hover:translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
        </div>

        <!-- Loading Skeleton -->
        <div id="loading-skeleton" class="hidden grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-3xl overflow-hidden border border-gray-100 animate-pulse"><div class="h-56 bg-gray-200"></div><div class="p-6 space-y-3"><div class="h-4 bg-gray-200 rounded-full w-1/3"></div><div class="h-6 bg-gray-200 rounded-full w-3/4"></div><div class="h-4 bg-gray-200 rounded-full w-full"></div></div></div>
            <div class="bg-white rounded-3xl overflow-hidden border border-gray-100 animate-pulse"><div class="h-56 bg-gray-200"></div><div class="p-6 space-y-3"><div class="h-4 bg-gray-200 rounded-full w-1/3"></div><div class="h-6 bg-gray-200 rounded-full w-3/4"></div><div class="h-4 bg-gray-200 rounded-full w-full"></div></div></div>
            <div class="bg-white rounded-3xl overflow-hidden border border-gray-100 animate-pulse"><div class="h-56 bg-gray-200"></div><div class="p-6 space-y-3"><div class="h-4 bg-gray-200 rounded-full w-1/3"></div><div class="h-6 bg-gray-200 rounded-full w-3/4"></div><div class="h-4 bg-gray-200 rounded-full w-full"></div></div></div>
            <div class="bg-white rounded-3xl overflow-hidden border border-gray-100 animate-pulse"><div class="h-56 bg-gray-200"></div><div class="p-6 space-y-3"><div class="h-4 bg-gray-200 rounded-full w-1/3"></div><div class="h-6 bg-gray-200 rounded-full w-3/4"></div><div class="h-4 bg-gray-200 rounded-full w-full"></div></div></div>
            <div class="bg-white rounded-3xl overflow-hidden border border-gray-100 animate-pulse"><div class="h-56 bg-gray-200"></div><div class="p-6 space-y-3"><div class="h-4 bg-gray-200 rounded-full w-1/3"></div><div class="h-6 bg-gray-200 rounded-full w-3/4"></div><div class="h-4 bg-gray-200 rounded-full w-full"></div></div></div>
            <div class="bg-white rounded-3xl overflow-hidden border border-gray-100 animate-pulse"><div class="h-56 bg-gray-200"></div><div class="p-6 space-y-3"><div class="h-4 bg-gray-200 rounded-full w-1/3"></div><div class="h-6 bg-gray-200 rounded-full w-3/4"></div><div class="h-4 bg-gray-200 rounded-full w-full"></div></div></div>
        </div>
    </div>
</div>

<!-- Particle container for GSAP burst effects -->
<div id="particle-container" class="fixed inset-0 pointer-events-none z-[99]"></div>

@push('scripts')
<script>
    // ========== Alpine Data Source ==========
    window.menuItemPricing = function(basePrice, variants) {
        return {
            basePrice: parseFloat(basePrice || 0),
            variants: variants && variants.options ? variants.options : [],
            variantType: variants && variants.type ? variants.type : null,
            selectedIndex: 0,
            get hasVariants() {
                return this.variants && this.variants.length > 0;
            },
            get currentOption() {
                if (!this.hasVariants) return null;
                return this.variants[this.selectedIndex] || this.variants[0];
            },
            get currentPrice() {
                if (this.currentOption && this.currentOption.price !== undefined && this.currentOption.price !== null) {
                    return parseFloat(this.currentOption.price);
                }
                return this.basePrice;
            },
            get formattedPrice() {
                return '$' + this.currentPrice.toFixed(2);
            },
            get currentLabel() {
                return this.currentOption ? this.currentOption.label : null;
            }
        };
    };

    // ========== GSAP Animation Helpers (same as restaurant show) ==========

    if (window.gsap && window.MotionPathPlugin) {
        gsap.registerPlugin(MotionPathPlugin);
    }

    window.getCartTargetPos = window.getCartTargetPos || function() {
        const navCartBtn = document.querySelector('[x-data="navCart"] button');
        if (navCartBtn) {
            const rect = navCartBtn.getBoundingClientRect();
            return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
        }
        return { x: window.innerWidth - 60, y: 40 };
    };

    window.createFlyingClone = window.createFlyingClone || function(sourceEl) {
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

    window.spawnParticles = window.spawnParticles || function(x, y, count) {
        count = count || 8;
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

        particles.forEach(function(p, i) {
            const angle = (i / count) * Math.PI * 2;
            const distance = gsap.utils.random(40, 100);
            gsap.to(p, {
                x: Math.cos(angle) * distance,
                y: Math.sin(angle) * distance,
                opacity: 0,
                scale: 0,
                duration: gsap.utils.random(0.6, 1.0),
                ease: 'power3.out',
                onComplete: function() { p.remove(); }
            });
        });
    };

    window.spawnRipple = window.spawnRipple || function(x, y) {
        const ripple = document.createElement('div');
        ripple.classList.add('cart-ripple');
        ripple.style.left = (x - 20) + 'px';
        ripple.style.top = (y - 20) + 'px';
        ripple.style.width = '40px';
        ripple.style.height = '40px';
        document.body.appendChild(ripple);
        gsap.to(ripple, {
            scale: 3, opacity: 0, duration: 0.7, ease: 'power2.out',
            onComplete: function() { ripple.remove(); }
        });
    };

    window.spawnPriceFly = window.spawnPriceFly || function(x, y, price) {
        const el = document.createElement('div');
        el.classList.add('price-fly');
        el.style.left = x + 'px';
        el.style.top = y + 'px';
        el.style.fontSize = '18px';
        el.textContent = '+$' + price;
        document.body.appendChild(el);
        gsap.fromTo(el,
            { opacity: 1, y: 0, scale: 1 },
            { opacity: 0, y: -60, scale: 1.5, duration: 1.2, ease: 'power2.out',
              onComplete: function() { el.remove(); } }
        );
    };

    function browseAnimateAddToCart(itemId, price, btnEl) {
        if (!window.gsap) return;

        const imgEl = document.getElementById('browse-img-' + itemId);
        const cardEl = document.getElementById('browse-card-' + itemId);
        if (!imgEl) return;

        const target = getCartTargetPos();
        const imgRect = imgEl.getBoundingClientRect();
        const startX = imgRect.left;
        const startY = imgRect.top;

        // Card bounce
        gsap.timeline()
            .to(cardEl, { scale: 0.95, duration: 0.1, ease: 'power2.in' })
            .to(cardEl, { scale: 1, duration: 0.4, ease: 'elastic.out(1, 0.4)' });

        // Flying clone
        var clone = createFlyingClone(imgEl);
        gsap.fromTo(clone, { scale: 1, rotation: 0 }, { scale: 1.2, duration: 0.15, ease: 'power2.out', yoyo: true, repeat: 1 });
        spawnParticles(startX + imgRect.width / 2, startY + imgRect.height / 2, 10);

        // Price fly
        if (btnEl) {
            var btnRect = btnEl.getBoundingClientRect();
            spawnPriceFly(btnRect.left - 30, btnRect.top - 10, price);
        }

        var midX = (startX + target.x) / 2;
        var midY = Math.min(startY, target.y) - 120;

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
            onComplete: function() {
                spawnParticles(target.x, target.y, 12);
                spawnRipple(target.x, target.y);
                var navCartBtn = document.querySelector('[x-data="navCart"] button');
                if (navCartBtn) {
                    gsap.timeline()
                        .to(navCartBtn, { scale: 1.4, duration: 0.15, ease: 'power2.out' })
                        .to(navCartBtn, { scale: 1, duration: 0.5, ease: 'elastic.out(1, 0.3)' });
                }
                gsap.to(clone, { scale: 0, opacity: 0, duration: 0.2, onComplete: function() { clone.remove(); } });
            }
        });
    }

    // ========== Add to Cart (fetch) ==========

    function browseAddToCart(menuItemId, price, event, variantLabel) {
        var btnEl = event ? event.currentTarget : null;
        browseAnimateAddToCart(menuItemId, price, btnEl);

        var token = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = token ? token.content : '';

        var payload = { menu_item_id: menuItemId, quantity: 1 };
        if (variantLabel) {
            payload.variant_label = variantLabel;
            payload.variant_price = price;
        }

        fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(function(res) { 
            if (!res.ok) {
                return res.json().then(function(err) { throw new Error(err.message || 'Error adding to cart'); });
            }
            return res.json(); 
        })
        .then(function(data) {
            if (data.cart) {
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
            }
        })
        .catch(function(e) { 
            console.error('Add to cart failed', e); 
            alert(e.message); // Show the error to the user
        });
    }

    // ========== Category Switching ==========

    var BROWSE_URL   = '{{ route('browse.index') }}';
    var activeCategory = '{{ $category }}';

    var CURRENT_USER_ID = {{ Auth::id() ?? 'null' }};
    var NEXT_PAGE_URL = '{{ $items->nextPageUrl() }}';
    var HAS_MORE = {{ $items->hasMorePages() ? 'true' : 'false' }};
    var TOTAL_ITEMS = {{ $items->total() }};
    
    function selectCategory(slug) {
        if (slug === activeCategory) return;
        activeCategory = slug;

        // Update pill styles
        document.querySelectorAll('.category-pill').forEach(function(pill) {
            pill.classList.remove('bg-emerald-500', 'text-white', 'border-emerald-500', 'shadow-lg', 'shadow-emerald-500/25');
            pill.classList.add('bg-white', 'text-gray-600', 'border-gray-200', 'hover:border-emerald-400', 'hover:text-emerald-600');
        });
        var activePill = document.getElementById('pill-' + slug);
        if (activePill) {
            activePill.classList.remove('bg-white', 'text-gray-600', 'border-gray-200', 'hover:border-emerald-400', 'hover:text-emerald-600');
            activePill.classList.add('bg-emerald-500', 'text-white', 'border-emerald-500', 'shadow-lg', 'shadow-emerald-500/25');
            activePill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }

        // Update browser URL without reload
        var url = new URL(window.location);
        url.searchParams.set('category', slug);
        window.history.pushState({}, '', url);

        // Show skeleton, hide grid
        var grid     = document.getElementById('items-grid');
        var skeleton = document.getElementById('loading-skeleton');
        grid.style.opacity = '0';
        grid.style.pointerEvents = 'none';
        skeleton.classList.remove('hidden');
        skeleton.classList.add('grid');

        // Fetch items via AJAX
        fetch(BROWSE_URL + '?category=' + encodeURIComponent(slug), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) { 
            NEXT_PAGE_URL = data.next_page_url;
            HAS_MORE = data.has_more;
            TOTAL_ITEMS = data.total;
            renderItems(data.items, false); 
        })
        .catch(function() {
            skeleton.classList.add('hidden');
            skeleton.classList.remove('grid');
            grid.style.opacity = '1';
            grid.style.pointerEvents = '';
        });
    }

    function loadMoreItems() {
            if (!NEXT_PAGE_URL) return;

            var btn = document.getElementById('load-more-btn');
            var originalText = btn.innerHTML;
            btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> <span>Loading...</span>';
            btn.disabled = true;

            fetch(NEXT_PAGE_URL, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                NEXT_PAGE_URL = data.next_page_url;
                HAS_MORE = data.has_more;
                TOTAL_ITEMS = data.total;
                
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                renderItems(data.items, true);
            })
            .catch(function() {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    }

    function renderItems(items, append) {
        var grid     = document.getElementById('items-grid');
        var skeleton = document.getElementById('loading-skeleton');
        var countEl  = document.getElementById('results-count');
        var loadMoreContainer = document.getElementById('load-more-container');

        skeleton.classList.add('hidden');
        skeleton.classList.remove('grid');

        if (!append) {
            if (items.length === 0) {
                grid.innerHTML = '<div class="col-span-3 py-24 text-center">' +
                    '<div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 text-gray-300 mb-5">' +
                    '<span class="text-4xl">\ud83c\udf7d\ufe0f</span></div>' +
                    '<h3 class="text-2xl font-black text-gray-900 mb-2">No items found</h3>' +
                    '<p class="text-gray-500 font-medium">Try a different category!</p></div>';
            } else {
                grid.innerHTML = items.map(function(meal) { return buildCard(meal); }).join('');
            }
        } else {
            // Append mode: we need to use a temporary container to extract elements so x-data initializes correctly
            // if we just concatenate innerHTML, we break existing Alpine states.
            var temp = document.createElement('div');
            temp.innerHTML = items.map(function(meal) { return buildCard(meal); }).join('');
            while(temp.firstChild) {
                grid.appendChild(temp.firstChild);
            }
        }

        // We only use TOTAL_ITEMS for the accurate results count
        if (countEl && TOTAL_ITEMS !== undefined) {
             countEl.textContent = TOTAL_ITEMS;
        }

        if (loadMoreContainer) {
             loadMoreContainer.classList.toggle('hidden', !HAS_MORE);
        }

        requestAnimationFrame(function() {
            grid.style.transition = 'opacity 0.3s ease';
            grid.style.opacity    = '1';
            grid.style.pointerEvents = '';
        });
    }

    function buildCard(meal) {
        var imageHtml = meal.image
            ? '<img alt="' + escHtml(meal.name) + '" src="' + meal.image + '" class="w-full h-full object-cover group-hover:scale-110 transition-transform">'
            : '<div class="w-full h-full flex items-center justify-center text-gray-300"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>';

        var logoHtml = meal.restaurant_logo
            ? '<img src="' + meal.restaurant_logo + '" class="w-full h-full object-cover">'
            : '<div class="w-full h-full flex items-center justify-center bg-emerald-100 text-emerald-600 text-[8px] font-bold">' + escHtml(meal.restaurant_initial) + '</div>';

        var descHtml = meal.description
            ? '<div class="mt-1" x-data="{ expanded: false, canExpand: false, isSmall() { return window.matchMedia && window.matchMedia(\'(max-width: 767px)\').matches; }, clampStyle(lines = 2) { return `display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:${lines};overflow:hidden;`; }, check() { if (!this.isSmall()) { this.canExpand = false; this.expanded = true; return; } this.$nextTick(() => { const el = this.$refs.desc; if (!el) return; const prevExpanded = this.expanded; this.expanded = false; this.$nextTick(() => { this.canExpand = el.scrollHeight > el.clientHeight + 1; this.expanded = prevExpanded; }); }); } }" x-init="check(); window.addEventListener(\'resize\', () => check())">' +
              '<p x-ref="desc" class="text-sm text-gray-500 font-medium whitespace-normal break-words" :style="(!expanded && isSmall()) ? (clampStyle(2) + \'word-break:break-word;overflow-wrap:anywhere;\') : \'word-break:break-word;overflow-wrap:anywhere;\'">' + escHtml(meal.description) + '</p>' +
              '<button type="button" x-show="canExpand" x-cloak @click="expanded = !expanded" class="mt-1 text-xs font-black text-gray-500 hover:text-emerald-600 transition-colors"><span x-text="expanded ? \'See less\' : \'See more\'"></span></button></div>'
            : '';

        var featuredHtml = meal.is_featured 
            ? '<span class="inline-flex items-center text-amber-500 bg-amber-50 px-1.5 py-0.5 rounded text-[9px] uppercase font-black tracking-widest border border-amber-100 whitespace-nowrap">Featured</span>'
            : '';

        var actionHtml = '';
        if (CURRENT_USER_ID !== null && CURRENT_USER_ID === meal.restaurant_user_id) {
            actionHtml = '<span class="text-xs font-bold text-amber-500 bg-amber-50 px-3 py-1 rounded-full border border-amber-100 flex-shrink-0 self-end">Own Restaurant</span>';
        } else {
            actionHtml = '<button @click="browseAddToCart(' + meal.id + ', currentPrice, $event, currentLabel)" class="bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white w-9 h-9 rounded-full flex items-center justify-center transition-all transform hover:scale-110 active:scale-95 shadow-sm hover:shadow-lg hover:shadow-emerald-500/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg></button>';
        }

        var variantsJson = JSON.stringify(meal.variants || []);
        var xDataStr = "menuItemPricing(" + meal.raw_price + ", " + variantsJson.replace(/"/g, '&quot;') + ")";

        return '<div id="browse-card-' + meal.id + '" class="bg-white border border-gray-100 rounded-2xl p-4 flex gap-4 hover:shadow-xl transition-shadow group overflow-hidden h-full" x-data="' + xDataStr + '">' +
            '<!-- Item Image -->' +
            '<div id="browse-img-' + meal.id + '" class="w-24 h-24 flex-shrink-0 bg-gray-100 rounded-xl overflow-hidden relative">' +
                '<a href="' + meal.url + '#meal-' + meal.id + '" class="block w-full h-full">' +
                    imageHtml +
                '</a>' +
                '<div class="absolute bottom-1 left-1 flex items-center gap-1 bg-white/90 backdrop-blur-md pl-1 pr-1.5 py-0.5 rounded-full shadow-md z-10 pointer-events-none">' +
                    '<div class="w-4 h-4 rounded-full overflow-hidden bg-gray-100 flex-shrink-0 border border-white">' +
                        logoHtml +
                    '</div>' +
                    '<span class="text-[8px] font-bold text-gray-900 truncate max-w-[60px]">' + escHtml(meal.restaurant_name) + '</span>' +
                '</div>' +
            '</div>' +
            
            '<!-- Details -->' +
            '<div class="flex-1 min-w-0 flex flex-col justify-between">' +
                '<div>' +
                    '<div class="flex flex-wrap items-start justify-between gap-x-2 gap-y-1">' +
                        '<a href="' + meal.url + '#meal-' + meal.id + '" class="min-w-0 flex-1">' +
                            '<h4 class="font-bold text-lg text-gray-900 group-hover:text-emerald-600 transition-colors leading-tight break-words flex flex-wrap items-center gap-2">' +
                                escHtml(meal.name) + ' ' + featuredHtml +
                            '</h4>' +
                        '</a>' +
                        '<span class="shrink-0 font-black text-emerald-500 whitespace-nowrap" x-text="formattedPrice">$' + escHtml(meal.price) + '</span>' +
                    '</div>' +
                    descHtml +
                '</div>' +
                
                '<!-- Variants -->' +
                '<div x-show="hasVariants" x-cloak class="mt-3">' +
                    '<p class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1" x-text="variantType ? variantType : \'Option\'"></p>' +
                    '<div class="flex flex-wrap gap-2">' +
                        '<template x-for="(opt, idx) in variants" :key="idx">' +
                            '<button type="button" @click="selectedIndex = idx" class="px-3 py-1.5 rounded-full text-[11px] font-semibold border transition-all" :class="selectedIndex === idx ? \'bg-emerald-500 text-white border-emerald-500 shadow-sm\' : \'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100\'">' +
                                '<span x-text="opt.label"></span>' +
                                '<span class="ml-1 opacity-80" x-text="\'· $\' + parseFloat(opt.price).toFixed(2)"></span>' +
                            '</button>' +
                        '</template>' +
                    '</div>' +
                '</div>' +

                '<!-- Add button -->' +
                '<div class="flex justify-end mt-3">' +
                    actionHtml +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Handle browser back/forward
    window.addEventListener('popstate', function() {
        var params = new URL(window.location).searchParams;
        selectCategory(params.get('category') || 'all');
    });
</script>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
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
@endpush
@endsection
