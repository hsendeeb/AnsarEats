<style>[x-cloak] { display: none !important; }</style>

<div 
    x-data="{ 
        show: false,
        isDenied: 'Notification' in window && Notification.permission === 'denied',
        init() {
            const path = window.location.pathname;
            const isCheckoutPage = path.includes('checkout');
            
            if (isCheckoutPage && 'Notification' in window && Notification.permission !== 'granted') {
                setTimeout(() => { this.show = true; }, 2500);
            }

            window.showPushPrompt = () => { this.show = true; };
        }
    }"
    x-show="show"
    x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-500"
    x-transition:enter-start="-translate-y-20 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in-out duration-300"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="-translate-y-10 opacity-0"
    class="fixed top-24 left-4 right-4 md:left-auto md:right-8 md:max-w-md p-0"
    style="z-index: 2000000 !important;"
    x-cloak
>
    <!-- Simple Solid Container -->
    <div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-2xl shadow-[0_10px_30px_rgba(0,0,0,0.1)] border border-gray-100 dark:border-gray-800 p-4 md:p-5 overflow-hidden">
        <div class="flex items-center gap-4">
            <!-- Icon -->
            <div class="flex-shrink-0 w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>

            <!-- Content -->
            <div class="flex-1">
                <template x-if="!isDenied">
                    <div>
                        <h4 class="font-bold text-sm tracking-tight mb-0.5">Enable Order Alerts</h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Get a ping when your food is ready.</p>
                    </div>
                </template>
                <template x-if="isDenied">
                    <div>
                        <h4 class="font-bold text-sm text-amber-500 tracking-tight mb-0.5">Notifications Blocked</h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Please enable them in your settings.</p>
                    </div>
                </template>
            </div>

            <!-- Single "OK" Button -->
            <div class="flex items-center">
                <button @click="show = false" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-black rounded-lg transition-all shadow-sm active:scale-95 cursor-pointer min-w-[60px]">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>