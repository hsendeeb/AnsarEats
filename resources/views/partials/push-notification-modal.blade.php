<style>[x-cloak] { display: none !important; }</style>

<div 
    x-data="{ 
        show: false,
        init() {
            console.log('Push Banner Init:', {
                hasNotification: 'Notification' in window,
                permission: 'Notification' in window ? Notification.permission : 'n/a',
                path: window.location.pathname
            });

            // Only show if permission is still 'default'
            if ('Notification' in window && Notification.permission === 'default') {
                const path = window.location.pathname;
                const isCheckoutPage = path.endsWith('/checkout') || path.includes('/checkout/');
                
                if (isCheckoutPage) {
                    console.log('On Checkout Page - Triggering Banner in 2.5s');
                    setTimeout(() => { this.show = true; }, 2500);
                }
            }

            window.showPushPrompt = () => { 
                console.log('Manual Trigger Called - Force Showing');
                this.show = true; 
            };
        },
        async requestPerm() {
            this.show = false;
            try {
                const permission = await Notification.requestPermission();
                if (permission === 'granted' && typeof window.subscribeToPush === 'function') {
                    window.subscribeToPush();
                }
            } catch (e) {
                console.error('Notification error:', e);
            }
        }
    }"
    x-show="show"
    x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-500"
    x-transition:enter-start="translate-y-20 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in-out duration-300"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-10 opacity-0"
    class="fixed bottom-28 left-4 right-4 md:bottom-8 md:left-8 md:right-auto md:max-w-md z-[5000] p-0 pointer-events-none"
    x-cloak
>
    <!-- Glass Container -->
    <div class="pointer-events-auto bg-gray-900/90 dark:bg-emerald-950/80 backdrop-blur-2xl text-white rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-white/10 p-5 md:p-6 ring-1 ring-white/5 overflow-hidden relative">
        <!-- Subtle background glow -->
        <div class="absolute -top-12 -right-12 w-24 h-24 bg-emerald-500/20 blur-3xl rounded-full"></div>
        
        <div class="relative flex flex-col md:flex-row items-center gap-4 md:gap-5">
            <!-- Professional SVG Icon (No Emojis) -->
            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>

            <!-- Content -->
            <div class="flex-1 text-center md:text-left">
                <h4 class="font-bold text-base text-emerald-50 md:text-lg tracking-tight mb-0.5 font-['Inter',sans-serif]">Keep Track of Your Order</h4>
                <p class="text-xs text-gray-300/80 font-medium leading-relaxed font-['Inter',sans-serif]">We'll send you a ping when your food is prepared or out for delivery.</p>
            </div>

            <!-- UI-UX PRO Buttons -->
            <div class="flex items-center gap-3 mt-3 md:mt-0">
                <button @click="show = false" class="px-4 py-2 text-sm font-semibold text-gray-400 hover:text-white transition-colors cursor-pointer">
                    Later
                </button>
                <button @click="requestPerm()" class="px-6 py-2.5 bg-white text-gray-900 hover:bg-emerald-50 text-sm font-black rounded-xl transition-all shadow-md active:scale-95 cursor-pointer">
                    Notify Me
                </button>
            </div>
        </div>
    </div>
</div>
