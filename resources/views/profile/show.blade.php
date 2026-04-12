@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-12 relative">
        <a href="{{ route('profile.account') }}" class="inline-flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-emerald-500 transition-colors mb-6 group">
            <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-emerald-50 transition-colors border border-gray-100 group-hover:border-emerald-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </div>
            Back to Account
        </a>
        <h1 class="text-4xl font-extrabold text-gray-900 outfit tracking-tight mb-2">My Profile</h1>
        <p class="text-gray-500 font-medium">Manage your personal information and security settings.</p>
    </div>

    <!-- Form Section -->
    <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
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
@endsection
