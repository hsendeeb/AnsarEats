@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] bg-gray-50 py-12 px-4 relative">
    <!-- Decorative -->
    <div class="absolute top-16 right-16 w-20 h-20 bg-emerald-200 rounded-full opacity-20 animate-bounce" style="animation-duration: 5s;"></div>
    <div class="absolute bottom-32 left-16 w-14 h-14 bg-teal-200 rounded-xl opacity-20 animate-bounce" style="animation-duration: 7s;"></div>

    <div class="max-w-4xl mx-auto relative z-10">
        <!-- Header -->
        <div class="mb-8">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-gray-500 hover:text-emerald-600 font-bold transition-colors mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back
            </a>
            <h1 class="text-3xl md:text-4xl font-black outfit text-gray-900 tracking-tight">Checkout</h1>
            <p class="text-gray-400 font-medium mt-1 text-base">Review your order and place it</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
            <!-- LEFT: Delivery Form -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-xl font-black outfit text-gray-900 mb-5 flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        Delivery Details
                    </h2>

                    <form method="POST" action="{{ route('checkout.place') }}" class="space-y-5" id="checkoutForm">
                        @csrf

                        <div class="group">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <label for="delivery_address" class="block text-sm font-bold text-gray-700">Delivery Address</label>
                                <button type="button" id="useCurrentLocation"
                                    class="text-xs font-black text-indigo-600 hover:text-indigo-500 flex items-center gap-1 transition-all">
                                    <svg id="useCurrentLocationIdleIcon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <svg id="useCurrentLocationLoadingIcon" class="w-3.5 h-3.5 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    <span id="useCurrentLocationLabel">Use Current Location</span>
                                </button>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                </div>
                                <input id="delivery_address" name="delivery_address" type="text" required value="{{ old('delivery_address') }}"
                                    class="block w-full pl-12 pr-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                    placeholder="Hamra Street, Building 42, 3rd Floor">
                            </div>
                            <p id="locationHelp" class="text-[11px] font-medium text-gray-400 mt-2">Tip: Using current location will fill coordinates; you can edit to add details.</p>
                            @error('delivery_address')
                                <p class="text-red-500 text-sm mt-2 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="group">
                            <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <input id="phone" name="phone" type="text" required value="{{ old('phone') }}"
                                    class="block w-full pl-12 pr-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                    placeholder="+961 71 123 456">
                            </div>
                            @error('phone')
                                <p class="text-red-500 text-sm mt-2 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="group">
                            <label for="notes" class="block text-sm font-bold text-gray-700 mb-2">Order Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                            <textarea id="notes" name="notes" rows="3"
                                class="block w-full px-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all resize-none"
                                placeholder="Any special instructions? Extra sauce, no onions...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-red-500 text-sm mt-2 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>

            <!-- RIGHT: Order Summary -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sticky top-28">
                    <h2 class="text-xl font-black outfit text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center text-amber-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        Order Summary
                    </h2>

                    <p class="text-sm font-bold text-emerald-600 mb-4">{{ $cart['restaurant_name'] }}</p>

                    @php
                        $lbpRate = 89000;
                        $totalLbp = (float) $cart['total'] * $lbpRate;
                    @endphp

                    <div class="space-y-3 mb-6">
                        @foreach($cart['items'] as $item)
                            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                <div class="flex items-center gap-3">
                                    <span class="w-7 h-7 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center font-bold text-xs">{{ $item['quantity'] }}×</span>
                                    <span class="font-medium text-gray-800 text-sm">
                                        {{ $item['name'] }}@if(!empty($item['variant'])) ({{ $item['variant'] }})@endif
                                    </span>
                                </div>
                                <span class="font-bold text-gray-900 text-sm">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-6" x-data="{
                        promoCode: '',
                        submitting: false,
                        message: '',
                        error: '',
                        discount: {{ $cart['discount'] }},
                        async applyPromo() {
                            if (!this.promoCode) return;
                            this.submitting = true;
                            this.message = '';
                            this.error = '';
                            try {
                                const res = await fetch('{{ route('cart.promo') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ code: this.promoCode })
                                });
                                const data = await res.json();
                                if (res.ok) {
                                    this.message = 'Promo applied!';
                                    // Refresh page to update totals
                                    window.location.reload();
                                } else {
                                    this.error = data.message || 'Invalid code';
                                }
                            } catch (e) {
                                this.error = 'Error applying promo';
                            } finally {
                                this.submitting = false;
                            }
                        }
                    }">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Promo Code</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="promoCode" placeholder="Enter code" 
                                class="flex-1 px-4 py-2 bg-gray-50 border-2 border-gray-100 rounded-xl focus:border-emerald-500 focus:bg-white outline-none font-bold uppercase text-sm transition-all"
                                @keydown.enter.prevent="applyPromo">
                            <button type="button" @click="applyPromo" :disabled="submitting"
                                class="px-4 py-2 bg-gray-900 text-white font-black rounded-xl text-xs hover:bg-gray-800 transition-all disabled:opacity-50">
                                Apply
                            </button>
                        </div>
                        <p x-show="message" x-text="message" class="text-[10px] font-bold text-emerald-500 mt-1 px-1" x-cloak></p>
                        <p x-show="error" x-text="error" class="text-[10px] font-bold text-red-500 mt-1 px-1" x-cloak></p>
                    </div>

                    <div class="border-t-2 border-dashed border-gray-200 pt-4 mb-6">
                        <div class="flex justify-between items-center text-sm text-gray-500 font-medium mb-2">
                            <span>Subtotal</span>
                            <span>${{ number_format($cart['subtotal'], 2) }}</span>
                        </div>
                        @if($cart['discount'] > 0)
                            <div class="flex justify-between items-center text-sm text-emerald-600 font-medium mb-2">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    Discount ({{ $cart['promo']['code'] }})
                                </span>
                                <span>-${{ number_format($cart['discount'], 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center text-sm text-gray-500 font-medium mb-2">
                            <span>Delivery</span>
                            <span class="font-bold {{ $cart['delivery_fee'] > 0 ? 'text-gray-900' : 'text-emerald-500' }}">
                                {{ $cart['delivery_fee'] > 0 ? '$' . number_format($cart['delivery_fee'], 2) : 'Free' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center mt-3">
                            <div>
                                <span class="text-lg font-black outfit text-gray-900">Total</span>
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">1 USD = 89,000 LBP</p>
                            </div>
                            <div class="text-right">
                                <span class="block text-2xl font-black outfit text-emerald-500">${{ number_format($cart['total'], 2) }}</span>
                                <span class="block text-sm font-black text-gray-500">LBP {{ number_format($totalLbp, 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" form="checkoutForm" class="w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-[0.98] text-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Place Order
                    </button>

                    <p class="text-center text-xs text-gray-400 font-medium mt-3">You&rsquo;ll receive a notification when your order is confirmed.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function() {
        function initUseLocation() {
            const btn = document.getElementById('useCurrentLocation');
            const input = document.getElementById('delivery_address');
            const help = document.getElementById('locationHelp');
            const idleIcon = document.getElementById('useCurrentLocationIdleIcon');
            const loadingIcon = document.getElementById('useCurrentLocationLoadingIcon');
            const label = document.getElementById('useCurrentLocationLabel');
            if (!btn || !input) return;

            btn.addEventListener('click', () => {
                if (!window.isSecureContext) {
                    if (help) help.textContent = 'Location requires HTTPS or localhost.';
                    return;
                }
            if (!navigator.geolocation) {
                if (help) help.textContent = 'Geolocation is not supported by your browser.';
                return;
            }

            btn.disabled = true;
            btn.classList.add('opacity-60');
            if (idleIcon) idleIcon.classList.add('hidden');
            if (loadingIcon) loadingIcon.classList.remove('hidden');
            if (label) label.textContent = 'Locating...';
            if (help) help.textContent = 'Getting your current location...';

            navigator.geolocation.getCurrentPosition(async (pos) => {
                const lat = pos.coords.latitude.toFixed(6);
                const lng = pos.coords.longitude.toFixed(6);

                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
                    const data = await res.json();
                    if (data && data.display_name) {
                        input.value = data.display_name;
                        if (help) help.textContent = 'Location added. You can edit the address details.';
                    } else {
                        input.value = `${lat}, ${lng}`;
                        if (help) help.textContent = 'Location added as coordinates. You can add details.';
                    }
                } catch (e) {
                    input.value = `${lat}, ${lng}`;
                    if (help) help.textContent = 'Location added as coordinates. You can add details.';
                }

                btn.disabled = false;
                btn.classList.remove('opacity-60');
                if (idleIcon) idleIcon.classList.remove('hidden');
                if (loadingIcon) loadingIcon.classList.add('hidden');
                if (label) label.textContent = 'Use Current Location';
            }, () => {
                if (help) help.textContent = 'Unable to get location. Please type it manually.';
                btn.disabled = false;
                btn.classList.remove('opacity-60');
                if (idleIcon) idleIcon.classList.remove('hidden');
                if (loadingIcon) loadingIcon.classList.add('hidden');
                if (label) label.textContent = 'Use Current Location';
            }, { enableHighAccuracy: true, timeout: 10000 });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initUseLocation);
        } else {
            initUseLocation();
        }
    })();
</script>
@endsection
