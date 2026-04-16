<div 
    x-data="{ 
        show: false,
        permission: ('Notification' in window) ? Notification.permission : 'unsupported',
        init() {
            window.showPushPrompt = () => {
                if ('Notification' in window && Notification.permission === 'default') {
                    this.show = true;
                }
            };
            
            // Auto check on load if auth
            @auth
            if ('Notification' in window && Notification.permission === 'default') {
                // Show after 2 seconds to not be too intrusive
                setTimeout(() => { this.show = true; }, 2000);
            }
            @endauth
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
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
    x-cloak
>
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="show = false"></div>

    <!-- Modal Card -->
    <div class="relative w-full max-w-sm bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-800 p-8 text-center scroll-reveal">
        <!-- Icon/Animation area -->
        <div class="mb-6 relative">
            <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-500/10 rounded-3xl mx-auto flex items-center justify-center text-emerald-500 relative z-10">
                <svg class="w-10 h-10 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation-duration: 2s;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
            <!-- Decorative pulses -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-emerald-500/10 rounded-full animate-ping" style="animation-duration: 3s;"></div>
        </div>

        <h3 class="text-2xl font-black outfit text-gray-900 dark:text-white mb-2">Enable Notifications?</h3>
        <p class="text-gray-500 dark:text-gray-400 font-medium mb-8 leading-relaxed">
            Stay updated on your order's progress. We'll only ping you when your food is being prepared or is out for delivery.
        </p>

        <div class="flex flex-col gap-3">
            <button 
                @click="requestPerm()"
                class="w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-[0.98] text-lg"
            >
                Allow Notifications
            </button>
            <button 
                @click="show = false"
                class="w-full py-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-bold transition-colors text-sm"
            >
                Maybe Later
            </button>
        </div>

        <!-- Privacy reassurance -->
        <p class="text-[10px] text-gray-400 mt-6 font-medium uppercase tracking-widest">No Spam. Ever.</p>
    </div>
</div>
