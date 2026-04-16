<div x-data="cartDrawer" @toggle-cart.window="open = !open" @cart-updated.window="updateFromEvent($event)">
    <!-- Floating Cart Button (Mobile) -->
    <div x-show="cart.count > 0 && !open && !window.location.pathname.includes('/checkout') && !window.location.pathname.includes('/order-confirmation')"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-20 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-20 opacity-0"
        class="fixed bottom-24 inset-x-4 z-[1000] md:hidden" x-cloak>
        <button @click="open = true"
            class="w-full bg-emerald-500 text-white px-6 h-14 flex items-center justify-between rounded-xl font-black outfit tracking-widest text-sm cursor-pointer hover:bg-emerald-600 transition-colors duration-200">
            <div class="w-10"></div> <!-- Left spacer -->
            <div class="flex-1 text-center">VIEW CART</div>
            <div class="text-base" x-text="'$' + (cart.total || 0).toFixed(2)"></div>
        </button>
    </div>



    <!-- Overlay -->
    <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="open = false"
        class="fixed inset-0 bg-gray-900/40 backdrop-blur-md z-[10000]" x-cloak></div>

    <!-- Drawer -->
    <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
        class="fixed right-0 top-0 h-full w-full max-w-md bg-white shadow-[-20px_0_50px_rgba(0,0,0,0.1)] z-[10001] flex flex-col"
        x-cloak>

        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-6 text-white flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black outfit">Your Cart</h3>
                        <p class="text-emerald-100 text-sm font-medium"
                            x-text="cart.restaurant_name ? 'From ' + cart.restaurant_name : 'Empty cart'"></p>
                    </div>
                </div>
                <button @click="open = false"
                    class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-6">
            <template x-if="Object.keys(cart.items || {}).length === 0">
                <div class="flex flex-col items-center justify-center h-full text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-black outfit text-gray-900 mb-2">Cart is empty</h4>
                    <p class="text-gray-500 font-medium">Add items from a restaurant to get started!</p>
                </div>
            </template>

            <template x-if="Object.keys(cart.items || {}).length > 0">
                <div class="space-y-4">
                    <template x-for="(item, key) in cart.items" :key="key">
                        <div
                            class="flex items-center gap-4 bg-gray-50 rounded-2xl p-4 border border-gray-100 group hover:shadow-sm transition-shadow">
                            <!-- Image -->
                            <div class="w-16 h-16 bg-gray-200 rounded-xl flex-shrink-0 overflow-hidden">
                                <template x-if="item.image">
                                    <img :src="'/storage/' + item.image" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image">
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0">
                                <h5 class="font-bold text-gray-900 truncate"
                                    x-text="item.variant ? `${item.name} (${item.variant})` : item.name"></h5>
                                <p class="text-sm font-black text-emerald-500">$<span
                                        x-text="(item.price * item.quantity).toFixed(2)"></span></p>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button @click="updateQuantity(key, item.quantity - 1)"
                                    class="w-7 h-7 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-red-50 hover:border-red-200 hover:text-red-500 text-gray-500 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M20 12H4"></path>
                                    </svg>
                                </button>
                                <span class="w-7 text-center font-bold text-sm" x-text="item.quantity"></span>
                                <button @click="updateQuantity(key, item.quantity + 1)"
                                    class="w-7 h-7 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-500 text-gray-500 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Clear Cart -->
                    <button @click="clearCart()"
                        class="w-full text-center text-sm font-bold text-red-500 hover:text-red-600 py-2 hover:bg-red-50 rounded-xl transition-colors mt-4">
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
                    <span class="text-2xl font-black outfit text-gray-900">$<span
                            x-text="cart.total.toFixed(2)"></span></span>
                </div>
                <a href="{{ route('checkout') }}" @click="open = false"
                    class="block w-full text-center py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-[0.98] text-lg">
                    Checkout →
                </a>
            </div>
        </template>
    </div>
</div>