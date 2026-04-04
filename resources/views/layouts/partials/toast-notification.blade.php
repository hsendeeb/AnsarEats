<div x-data @cart-updated.window="$store.appToast.show($event.detail)" @show-toast.window="$store.appToast.show($event.detail)" class="fixed top-24 right-4 z-[80]" x-cloak>
    <template x-if="$store.appToast.visible">
        <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0" class="rounded-2xl shadow-2xl p-4 flex items-center gap-3 min-w-[280px] border" :class="$store.appToast.toastClasses.container">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" :class="$store.appToast.toastClasses.iconWrap">
                <template x-if="$store.appToast.type === 'error'">
                    <svg class="w-5 h-5" :class="$store.appToast.toastClasses.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </template>
                <template x-if="$store.appToast.type !== 'error'">
                    <svg class="w-5 h-5" :class="$store.appToast.toastClasses.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </template>
            </div>
            <p class="font-bold text-sm" :class="$store.appToast.toastClasses.text" x-text="$store.appToast.message"></p>
        </div>
    </template>
</div>
