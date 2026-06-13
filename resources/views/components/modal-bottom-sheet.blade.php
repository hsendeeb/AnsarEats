<!-- Modal Bottom Sheet Component -->
<div x-data="{}" x-show="$store.bottomSheet.open" x-transition:enter="transition-transform ease-out duration-700 transform" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition-transform ease-in duration-500 transform" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" class="fixed inset-0 flex items-end justify-center z-50" style="display: none;">
    <!-- Backdrop -->
    <div @click="$store.bottomSheet.open = false" class="fixed inset-0 bg-black/40" aria-hidden="true"></div>

    <!-- Bottom Sheet -->
    <div class="relative w-full max-w-lg mx-auto p-4 sm:p-6 md:p-8">
        <div class="bg-white rounded-t-[32px] shadow-2xl overflow-hidden">
            <!-- Drag Handle -->
            <div class="flex justify-center pt-3">
                <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
            </div>

            <!-- Content -->
            <div class="px-6 py-5">
                <!-- Title -->
                <h2 class="text-xl font-bold text-zinc-900 mb-4">Where should we deliver to?</h2>

                <!-- Action Items -->
                <div class="space-y-4">
                    <!-- Item 1 -->
                    <button class="w-full flex items-center justify-between p-4 bg-white rounded-lg border border-gray-100 hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 .69-.355 1.303-.909 1.652L12 17l-1.091-4.348A1.99 1.99 0 0010 11c0-1.105.895-2 2-2s2 .895 2 2z"/></svg>
                            <div class="text-left">
                                <p class="font-medium text-gray-900">Use current location</p>
                                <p class="text-sm text-gray-500">Detect your position automatically</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <!-- Item 2 -->
                    <button class="w-full flex items-center justify-between p-4 bg-white rounded-lg border border-gray-100 hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 .69-.355 1.303-.909 1.652L12 17l-1.091-4.348A1.99 1.99 0 0010 11c0-1.105.895-2 2-2s2 .895 2 2z"/></svg>
                            <div class="text-left">
                                <p class="font-medium text-gray-900">Deliver somewhere else</p>
                                <p class="text-sm text-gray-500">Choose a different address</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <!-- Item 3 -->
                    <button class="w-full flex items-center justify-between p-4 bg-white rounded-lg border border-gray-100 hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <div class="text-left">
                                <p class="font-medium text-gray-900">Add new address</p>
                                <p class="text-sm text-gray-500">Save a new delivery location</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <!-- Saved Addresses Header -->
                <h3 class="mt-8 mb-3 text-base font-semibold text-zinc-800">Saved addresses</h3>
                <!-- Saved Address Card -->
                <button class="w-full flex items-center justify-between p-4 bg-white rounded-lg border border-gray-100 hover:bg-gray-50 transition">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 01-2 2h-4a2 2 0 01-2-2V12H9v8a2 2 0 01-2 2H3a2 2 0 01-2-2z"/></svg>
                        <div class="text-left">
                            <p class="font-medium text-gray-900">Home</p>
                            <p class="text-sm text-gray-500">damascus highway, block D, ground floor</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M6 10a2 2 0 114 0 2 2 0 01-4 0zm6 0a2 2 0 114 0 2 2 0 01-4 0z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>
