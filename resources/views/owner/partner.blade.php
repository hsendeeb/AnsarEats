@extends('layouts.app')

@section('content')
@php
    $restaurantDraft = $latestRequest ?? null;
@endphp

<div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="bg-gradient-to-br from-emerald-600 via-teal-500 to-cyan-500 rounded-[2rem] p-8 md:p-12 mb-8 text-white relative overflow-hidden shadow-2xl shadow-emerald-500/20">
            <div class="absolute -top-12 -right-10 w-48 h-48 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-12 -left-10 w-40 h-40 rounded-full bg-white/10"></div>

            <div class="relative z-10 max-w-2xl">
                <p class="text-xs font-black uppercase tracking-[0.35em] text-black/70 mb-4">Partner With Us</p>
                <h1 class="text-4xl md:text-5xl font-black outfit leading-tight">
                    {{ $latestRequest?->restaurant_name ? 'Finish your restaurant request' : 'Launch your restaurant on AnsarEats' }}
                </h1>
                <p class="mt-4 text-base md:text-lg font-medium text-white/85">
                    Submit your restaurant details for review. The super admin will approve or reject the request before your restaurant is created.
                </p>
            </div>
        </div>

        @if($latestRequest?->status === 'pending')
            <div class="mb-6 rounded-3xl border border-amber-200 bg-amber-50 px-6 py-5 text-amber-900 shadow-sm">
                <p class="font-black uppercase tracking-widest text-xs text-amber-600">Pending Review</p>
                <p class="mt-2 font-medium">Your latest request is waiting for super admin approval. You can still update it below.</p>
            </div>
        @elseif($latestRequest?->status === 'rejected')
            <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 px-6 py-5 text-red-900 shadow-sm">
                <p class="font-black uppercase tracking-widest text-xs text-red-600">Request Rejected</p>
                <p class="mt-2 font-medium">{{ $latestRequest->rejection_reason ?: 'No rejection reason was provided.' }}</p>
                <p class="mt-2 text-sm font-medium text-red-700">Update the form and submit again when you are ready.</p>
            </div>
        @endif

        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-xl overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/80">
                <h2 class="text-2xl font-black outfit text-gray-900">
                    {{ $latestRequest ? 'Update Restaurant Request' : 'Create Restaurant Request' }}
                </h2>
                <p class="mt-1 text-sm font-medium text-gray-500">Your restaurant will only be created after approval.</p>
            </div>

            <form method="POST" action="{{ route('owner.restaurant.store') }}" enctype="multipart/form-data" class="p-6 space-y-5" x-data="{
                logoPreview: '{{ $restaurantDraft && $restaurantDraft->logo ? Storage::url($restaurantDraft->logo) : '' }}',
                coverPreview: '{{ $restaurantDraft && $restaurantDraft->cover_image ? Storage::url($restaurantDraft->cover_image) : '' }}',
                handleLogoSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => this.logoPreview = e.target.result;
                        reader.readAsDataURL(file);
                    }
                },
                handleCoverSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => this.coverPreview = e.target.result;
                        reader.readAsDataURL(file);
                    }
                }
            }">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Restaurant Logo</label>
                        <div @click="$refs.logoInput.click()" class="relative h-36 rounded-3xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center cursor-pointer overflow-hidden group hover:border-emerald-500 transition-all">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoPreview">
                                <div class="text-center">
                                    <svg class="w-8 h-8 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p class="mt-2 text-xs font-black uppercase tracking-widest text-gray-400">Upload Logo</p>
                                </div>
                            </template>
                        </div>
                        <input type="file" name="logo" x-ref="logoInput" @change="handleLogoSelect($event)" accept="image/*" class="hidden">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Banner Image</label>
                        <div @click="$refs.coverInput.click()" class="relative h-36 rounded-3xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center cursor-pointer overflow-hidden group hover:border-emerald-500 transition-all">
                            <template x-if="coverPreview">
                                <img :src="coverPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!coverPreview">
                                <div class="text-center">
                                    <svg class="w-8 h-8 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p class="mt-2 text-xs font-black uppercase tracking-widest text-gray-400">Upload Banner</p>
                                </div>
                            </template>
                        </div>
                        <input type="file" name="cover_image" x-ref="coverInput" @change="handleCoverSelect($event)" accept="image/*" class="hidden">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Restaurant Name</label>
                    <input type="text" name="name" value="{{ old('name') ?? optional($restaurantDraft)->restaurant_name ?? '' }}" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all" placeholder="My Amazing Restaurant">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="4" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all resize-none" placeholder="Tell customers what makes your restaurant special">{{ old('description') ?? optional($restaurantDraft)->description }}</textarea>
                </div>

                <div x-data="{ 
                    gettingLocation: false, 
                    async useCurrentLocation() {
                        if (!navigator.geolocation) {
                            alert('Geolocation is not supported by your browser');
                            return;
                        }

                        this.gettingLocation = true;
                        navigator.geolocation.getCurrentPosition(async (position) => {
                            const lat = position.coords.latitude;
                            const lon = position.coords.longitude;

                            $refs.latInput.value = lat;
                            $refs.lonInput.value = lon;

                            try {
                                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`);
                                const data = await response.json();
                                if (data && data.display_name) {
                                    $refs.addressInput.value = data.display_name;
                                }
                            } catch (error) {
                                console.error('Error fetching address:', error);
                            } finally {
                                this.gettingLocation = false;
                            }
                        }, () => {
                            alert('Could not get your location. Please allow location access and try again.');
                            this.gettingLocation = false;
                        });
                    }
                }">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-bold text-gray-700">Address</label>
                        <button type="button" @click="useCurrentLocation()" :disabled="gettingLocation" class="text-xs font-black text-emerald-600 hover:text-emerald-500 transition-all">
                            <span x-text="gettingLocation ? 'Locating...' : 'Use Current Location'"></span>
                        </button>
                    </div>
                    <input type="text" name="address" x-ref="addressInput" value="{{ old('address') ?? optional($restaurantDraft)->address }}" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all" placeholder="Hamra Street, Beirut">
                    <input type="hidden" name="latitude" x-ref="latInput" value="{{ old('latitude') ?? optional($restaurantDraft)->latitude }}">
                    <input type="hidden" name="longitude" x-ref="lonInput" value="{{ old('longitude') ?? optional($restaurantDraft)->longitude }}">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') ?? optional($restaurantDraft)->phone }}" required class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all" placeholder="+961 1 234 567">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Operating Hours</label>
                    <div class="space-y-3 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        @php
                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            $hours = optional($restaurantDraft)->operating_hours ?? [];
                        @endphp
                        @foreach($days as $day)
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" x-data="{ closed: {{ ($hours[$day]['closed'] ?? false) ? 'true' : 'false' }} }">
                                <span class="w-full sm:w-20 text-xs font-black uppercase tracking-widest text-gray-500">{{ $day }}</span>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:flex-1 min-w-0">
                                    <input type="time" name="operating_hours[{{ $day }}][open]" value="{{ $hours[$day]['open'] ?? '08:00' }}" :disabled="closed" class="w-full sm:flex-1 text-xs border-0 bg-white rounded-lg focus:ring-emerald-500 disabled:opacity-40 font-bold">
                                    <span class="text-gray-300 font-black hidden sm:inline">/</span>
                                    <input type="time" name="operating_hours[{{ $day }}][close]" value="{{ $hours[$day]['close'] ?? '22:00' }}" :disabled="closed" class="w-full sm:flex-1 text-xs border-0 bg-white rounded-lg focus:ring-emerald-500 disabled:opacity-40 font-bold">
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer group self-start sm:self-auto">
                                    <input type="hidden" name="operating_hours[{{ $day }}][closed]" value="0">
                                    <input type="checkbox" name="operating_hours[{{ $day }}][closed]" value="1" x-model="closed" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-[10px] font-black uppercase tracking-tighter text-gray-400 group-hover:text-gray-600">Closed</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_open" value="1" {{ (optional($restaurantDraft)->is_open ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 rounded-full peer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:bg-emerald-500"></div>
                    </label>
                    <span class="text-sm font-bold text-gray-700">Mark restaurant as open after approval</span>
                </div>

                <button type="submit" class="w-full py-4 bg-gray-900 hover:bg-emerald-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl hover:shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5 active:scale-[0.98]">
                    {{ $latestRequest ? 'Update Request' : 'Submit for Approval' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
