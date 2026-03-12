<div x-data="toastManager" @cart-updated.window="showToast($event)" @show-toast.window="showToast($event)" class="fixed top-24 right-4 z-[80]" x-cloak>
    <template x-if="visible">
        <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0" class="bg-white border border-gray-100 rounded-2xl shadow-2xl p-4 flex items-center gap-3 min-w-[280px]">
            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <p class="font-bold text-gray-900 text-sm" x-text="message"></p>
        </div>
    </template>
</div>
