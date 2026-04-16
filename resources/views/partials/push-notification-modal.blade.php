<div 
    x-data="{ 
        show: false,
        init() {
            // Only show if permission is still 'default'
            if ('Notification' in window && Notification.permission === 'default') {
                const path = window.location.pathname;
                const isCheckoutPage = path.endsWith('/checkout') || path.includes('/checkout/');
                
                if (isCheckoutPage) {
                    // Show after 2 seconds on the checkout page
                    setTimeout(() => { this.show = true; }, 2000);
                }
            }

            // Expose a global function for testing
            window.showPushPrompt = () => { this.show = true; };
        },
        async requestPerm() {
            this.show = false;
            const permission = await Notification.requestPermission();
            if (permission === 'granted' && typeof window.subscribeToPush === 'function') {
                window.subscribeToPush();
            }
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-500"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-full opacity-0"
    class="fixed bottom-24 left-4 right-4 md:bottom-6 md:left-6 md:right-auto md:max-w-md z-[2000000] p-0 md:p-4"
    x-cloak
>
    <div class="bg-gray-900 dark:bg-emerald-950 text-white rounded-3xl shadow-2xl shadow-emerald-500/10 border border-white/10 p-5 md:p-6 backdrop-blur-xl">
        <div class="flex flex-col md:flex-row items-center gap-4 md:gap-6">
            <!-- Icon -->
            <div class="flex-shrink-0 w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>

            <!-- Text Content -->
            <div class="flex-1 text-center md:text-left">
                <h4 class="font-black outfit text-base tracking-tight mb-0.5">Order Updates</h4>
                <p class="text-xs text-gray-400 font-medium">Get real-time alerts when your food is out for delivery.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 mt-2 md:mt-0">
                <button @click="show = false" class="px-4 py-2.5 text-xs font-bold text-gray-400 hover:text-white transition-colors">
                    Later
                </button>
                <button @click="requestPerm()" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-black rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
                    Enable
                </button>
            </div>
        </div>
    </div>
</div>
