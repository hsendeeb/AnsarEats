@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-12">
        <h1 class="text-4xl font-extrabold text-gray-900 outfit tracking-tight mb-2">My Profile</h1>
        <p class="text-gray-500 font-medium">Manage your personal information and security settings.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Sidebar Navigation -->
        <div class="md:col-span-1">
            <div class="sticky top-28 space-y-6">
                <!-- Sliding Underline Tabs Navigation -->
                <nav x-data="{
                        activeTab: '{{ url()->current() }}',
                        indicatorStyle: '',
                        init() {
                            this.$nextTick(() => this.updateIndicator(this.activeTab));
                            window.addEventListener('resize', () => { this.updateIndicator(this.activeTab) });
                        },
                        updateIndicator(href) {
                            const el = this.$refs.nav.querySelector(`[href='${href}']`);
                            if (!el) return;
                            const isHorizontal = window.innerWidth < 768; // md breakpoint
                            if (isHorizontal) {
                                this.indicatorStyle = `left: ${el.offsetLeft}px; width: ${el.offsetWidth}px; height: 3px; bottom: 0;`;
                            } else {
                                this.indicatorStyle = `top: ${el.offsetTop}px; height: ${el.offsetHeight}px; width: 3px; left: -1px;`;
                            }
                        },
                        clickTab(e, href) {
                            e.preventDefault();
                            this.activeTab = href;
                            this.updateIndicator(href);
                            setTimeout(() => window.location.href = href, 200); // Wait for sliding animation
                        }
                    }" 
                    x-ref="nav"
                    class="relative flex flex-row md:flex-col gap-2 md:gap-1 overflow-x-auto no-scrollbar pb-2 md:pb-0 border-b md:border-b-0 md:border-l border-gray-200 md:pl-2"
                >
                    <!-- Sliding Indicator -->
                    <div class="absolute bg-emerald-500 transition-all duration-300 ease-out z-10 rounded-full" :style="indicatorStyle"></div>

                    <!-- Links -->
                    <a href="{{ route('profile.show') }}" 
                       @click="clickTab($event, '{{ route('profile.show') }}')"
                       class="relative flex-shrink-0 flex items-center justify-center md:justify-start gap-3 px-4 py-3 md:py-4 font-bold transition-colors"
                       :class="activeTab.includes('{{ route('profile.show') }}') ? 'text-emerald-600' : 'text-gray-500 hover:text-gray-800'">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span class="whitespace-nowrap">Account Info</span>
                    </a>

                    <a href="{{ route('profile.orders') }}" 
                       @click="clickTab($event, '{{ route('profile.orders') }}')"
                       class="relative flex-shrink-0 flex items-center justify-center md:justify-start gap-3 px-4 py-3 md:py-4 font-bold transition-colors"
                       :class="activeTab.includes('{{ route('profile.orders') }}') ? 'text-emerald-600' : 'text-gray-500 hover:text-gray-800'">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <span class="whitespace-nowrap">Order History</span>
                    </a>

                    @if(auth()->user()->restaurant)
                    <a href="{{ route('owner.dashboard') }}" 
                       @click="clickTab($event, '{{ route('owner.dashboard') }}')"
                       class="relative flex-shrink-0 flex items-center justify-center md:justify-start gap-3 px-4 py-3 md:py-4 font-bold transition-colors"
                       :class="activeTab.includes('{{ route('owner.dashboard') }}') ? 'text-emerald-600' : 'text-gray-500 hover:text-gray-800'">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="whitespace-nowrap">Owner Dashboard</span>
                    </a>
                    @endif
                </nav>
            </div>
        </div>

        <!-- Form Section -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6" x-data="{
                        gettingLocation: false,
                        locationMessage: 'Save your delivery address here so checkout can fill it automatically.',
                        async useCurrentLocation() {
                            if (!window.isSecureContext) {
                                this.locationMessage = 'Location requires HTTPS or localhost.';
                                return;
                            }

                            if (!navigator.geolocation) {
                                this.locationMessage = 'Geolocation is not supported by your browser.';
                                return;
                            }

                            this.gettingLocation = true;
                            this.locationMessage = 'Getting your current location...';

                            navigator.geolocation.getCurrentPosition(async (position) => {
                                const lat = position.coords.latitude.toFixed(6);
                                const lon = position.coords.longitude.toFixed(6);

                                this.$refs.deliveryLatitude.value = lat;
                                this.$refs.deliveryLongitude.value = lon;

                                try {
                                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`);
                                    const data = await response.json();
                                    this.$refs.deliveryAddress.value = data?.display_name || `${lat}, ${lon}`;
                                    this.locationMessage = data?.display_name
                                        ? 'Location added. You can edit the address details before saving.'
                                        : 'Location added as coordinates. You can edit the address details before saving.';
                                } catch (error) {
                                    this.$refs.deliveryAddress.value = `${lat}, ${lon}`;
                                    this.locationMessage = 'Location added as coordinates. You can edit the address details before saving.';
                                } finally {
                                    this.gettingLocation = false;
                                }
                            }, () => {
                                this.gettingLocation = false;
                                this.locationMessage = 'Unable to get location. Please type your address manually.';
                            }, { enableHighAccuracy: true, timeout: 10000 });
                        },
                        clearSavedCoordinates() {
                            this.$refs.deliveryLatitude.value = '';
                            this.$refs.deliveryLongitude.value = '';
                        }
                    }">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-black text-gray-700 uppercase tracking-wider mb-2">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900">
                                @error('name') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-black text-gray-700 uppercase tracking-wider mb-2">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900">
                                @error('email') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-black text-gray-700 uppercase tracking-wider mb-2">Phone Number</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $deliveryDefaults['phone']) }}"
                                    class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900"
                                    placeholder="+961 71 123 456">
                                @error('phone') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <label for="delivery_address" class="block text-sm font-black text-gray-700 uppercase tracking-wider">Delivery Address</label>
                                    <button type="button" @click="useCurrentLocation()" :disabled="gettingLocation"
                                        class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-indigo-600 hover:text-indigo-500 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                        <svg x-show="!gettingLocation" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        <svg x-show="gettingLocation" x-cloak class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        <span x-text="gettingLocation ? 'Locating...' : 'Use Current Location'"></span>
                                    </button>
                                </div>
                                <input type="text" name="delivery_address" id="delivery_address" x-ref="deliveryAddress"
                                    @input="clearSavedCoordinates()"
                                    value="{{ old('delivery_address', $deliveryDefaults['delivery_address']) }}"
                                    class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900"
                                    placeholder="Hamra Street, Building 42, 3rd Floor">
                                <input type="hidden" name="delivery_latitude" x-ref="deliveryLatitude" value="{{ old('delivery_latitude', $deliveryDefaults['delivery_latitude']) }}">
                                <input type="hidden" name="delivery_longitude" x-ref="deliveryLongitude" value="{{ old('delivery_longitude', $deliveryDefaults['delivery_longitude']) }}">
                                <p class="mt-2 text-xs font-medium text-gray-400" x-text="locationMessage"></p>
                                @error('delivery_address') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                                @error('delivery_latitude') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                                @error('delivery_longitude') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <hr class="my-8 border-gray-100">

                            <div>
                                <label for="password" class="block text-sm font-black text-gray-700 uppercase tracking-wider mb-2">New Password (leave blank to keep current)</label>
                                <input type="password" name="password" id="password"
                                    class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900">
                                @error('password') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-black text-gray-700 uppercase tracking-wider mb-2">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900">
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full md:w-auto px-10 py-4 bg-emerald-500 text-white font-extrabold rounded-2xl shadow-xl shadow-emerald-500/30 hover:bg-emerald-400 hover:-translate-y-1 transition-all active:scale-95">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
