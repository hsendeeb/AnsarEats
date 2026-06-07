@extends('layouts.app')

@section('skeleton')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-12">
        <div class="w-32 h-6 bg-gray-200 dark:bg-gray-800 rounded-lg mb-6 animate-pulse"></div>
        <div class="w-48 h-10 bg-gray-200 dark:bg-gray-800 rounded-xl mb-2 animate-pulse"></div>
        <div class="w-64 h-5 bg-gray-200 dark:bg-gray-800 rounded-lg animate-pulse"></div>
    </div>
    <div class="space-y-4">
        @for($i = 0; $i < 3; $i++)
        <div class="h-24 bg-gray-100 dark:bg-gray-800 rounded-3xl animate-pulse border border-gray-200 dark:border-gray-700"></div>
        @endfor
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8" x-data="{
    showAddForm: false,
    editingId: null,
    gettingLocation: false,
    locationMessage: '',
    deleteModalOpen: false,
    deleteLocationId: null,
    deleteLocationAlias: '',
    deleting: false,
    confirmDelete(id, alias) {
        this.deleteLocationId = id;
        this.deleteLocationAlias = alias;
        this.deleteModalOpen = true;
        this.deleting = false;
    },
    submitDelete() {
        if (this.deleteLocationId) {
            this.deleting = true;
            document.getElementById('deleteForm' + this.deleteLocationId).submit();
        }
    },

    // Add form fields
    alias: '',
    address: '',
    latitude: '',
    longitude: '',
    is_default: false,

    // Edit form fields
    editAlias: '',
    editAddress: '',
    editLatitude: '',
    editLongitude: '',
    editIsDefault: false,

    resetForm() {
        this.alias = '';
        this.address = '';
        this.latitude = '';
        this.longitude = '';
        this.is_default = false;
        this.locationMessage = '';
    },

    startEdit(loc) {
        this.editingId = loc.id;
        this.editAlias = loc.alias;
        this.editAddress = loc.address || '';
        this.editLatitude = loc.latitude || '';
        this.editLongitude = loc.longitude || '';
        this.editIsDefault = loc.is_default;
    },

    cancelEdit() {
        this.editingId = null;
    },

    async useCurrentLocation(target) {
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

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`);
                const data = await response.json();
                const addr = data?.display_name || `${lat}, ${lon}`;

                if (target === 'add') {
                    this.address = addr;
                    this.latitude = lat;
                    this.longitude = lon;
                } else {
                    this.editAddress = addr;
                    this.editLatitude = lat;
                    this.editLongitude = lon;
                }
                this.locationMessage = 'Location detected! You can edit the address before saving.';
            } catch (error) {
                if (target === 'add') {
                    this.address = `${lat}, ${lon}`;
                    this.latitude = lat;
                    this.longitude = lon;
                } else {
                    this.editAddress = `${lat}, ${lon}`;
                    this.editLatitude = lat;
                    this.editLongitude = lon;
                }
                this.locationMessage = 'Location added as coordinates.';
            } finally {
                this.gettingLocation = false;
            }
        }, () => {
            this.gettingLocation = false;
            this.locationMessage = 'Unable to get location. Please type your address manually.';
        }, { enableHighAccuracy: true, timeout: 10000 });
    }
}">
    {{-- Header --}}
    <div class="mb-10 relative">
        <a href="{{ route('profile.account') }}" class="inline-flex items-center gap-2 text-sm font-bold text-gray-400 dark:text-gray-500 hover:text-emerald-500 transition-colors mb-6 group">
            <div class="w-8 h-8 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center group-hover:bg-emerald-50 dark:group-hover:bg-emerald-500/10 transition-colors border border-gray-100 dark:border-gray-700 group-hover:border-emerald-100 dark:group-hover:border-emerald-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </div>
            Back to Account
        </a>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl md:text-4xl font-extrabold text-gray-900 dark:text-white outfit tracking-tight mb-2">My Locations</h1>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Save your delivery addresses for faster checkout.</p>
            </div>
            <button @click="showAddForm = !showAddForm; if(!showAddForm) resetForm()"
                class="inline-flex items-center gap-2 px-6 py-3 bg-teal-500 text-white font-extrabold rounded-2xl  hover:bg-teal-400 hover:-translate-y-0.5 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span x-text="showAddForm ? 'Cancel' : 'Add Location'"></span>
            </button>
        </div>
    </div>

    {{-- Add Location Form --}}
    <div x-show="showAddForm" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4" x-cloak class="mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 md:p-8">
            <h3 class="text-lg font-extrabold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 dark:bg-teal-500/10 text-teal-500 flex items-center justify-center border border-teal-100/50 dark:border-teal-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                New Location
            </h3>

            <form action="{{ route('profile.locations.store') }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Label / Alias</label>
                        <input type="text" name="alias" x-model="alias" required placeholder="e.g. Home, Work, Mom's House"
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-700 border-2 border-transparent focus:border-teal-500 focus:bg-white dark:focus:bg-gray-600 rounded-2xl transition-all outline-none font-bold text-gray-900 dark:text-white placeholder-gray-400">
                        @error('alias') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Address</label>
                            <button type="button" @click="useCurrentLocation('add')" :disabled="gettingLocation"
                                class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-teal-600 dark:text-teal-400 hover:text-teal-500 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                <svg x-show="!gettingLocation" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <svg x-show="gettingLocation" x-cloak class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                <span x-text="gettingLocation ? 'Locating...' : 'Use GPS'"></span>
                            </button>
                        </div>
                        <input type="text" name="address" x-model="address" required placeholder="Street, building, floor..."
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-700 border-2 border-transparent focus:border-teal-500 focus:bg-white dark:focus:bg-gray-600 rounded-2xl transition-all outline-none font-bold text-gray-900 dark:text-white placeholder-gray-400">
                        <input type="hidden" name="latitude" :value="latitude">
                        <input type="hidden" name="longitude" :value="longitude">
                    </div>
                </div>

                <p x-show="locationMessage" x-text="locationMessage" class="text-xs font-medium text-gray-400 dark:text-gray-500" x-cloak></p>

                <div class="flex items-center justify-between flex-wrap gap-4 pt-2">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="is_default" value="1" x-model="is_default"
                            class="w-5 h-5 rounded-lg border-2 border-gray-300 dark:border-gray-600 text-teal-500 focus:ring-teal-500 focus:ring-offset-0 transition-colors">
                        <span class="text-sm font-bold text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Set as default location</span>
                    </label>
                    <button type="submit"
                        class="px-8 py-3 bg-teal-500 text-white font-extrabold rounded-2xl cursor-pointer hover:bg-teal-400 hover:-translate-y-0.5 transition-all active:scale-95">
                        Save Location
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Locations List --}}
    @if($locations->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
            <div class="w-20 h-20 rounded-full bg-teal-50 dark:bg-teal-500/10 text-teal-500 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mb-2">No saved locations</h3>
            <p class="text-gray-500 dark:text-gray-400 font-medium mb-6">Add your first delivery address to speed up checkout.</p>
            <button @click="showAddForm = true"
                class="inline-flex items-center gap-2 px-6 py-3 bg-teal-500 text-white font-extrabold rounded-2xl cursor-pointer   hover:bg-teal-400 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="ro nd" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Add Your First Location
            </button>
        </div>
    @else
        <div class="space-y-4">
            @foreach($locations as $loc)
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 transition-all hover:shadow-md">

                    {{-- View Mode --}}
                    <div x-show="editingId !== {{ $loc->id }}" class="p-5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 border
                                {{ $loc->is_default ? 'bg-teal-50 dark:bg-teal-500/10 text-teal-500 border-teal-100/50 dark:border-teal-500/20' : 'bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-500 border-gray-100 dark:border-gray-600' }}">
                                @if($loc->alias === 'Home' || str_contains(strtolower($loc->alias), 'home'))
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                @elseif($loc->alias === 'Work' || str_contains(strtolower($loc->alias), 'work') || str_contains(strtolower($loc->alias), 'office'))
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <h3 class="font-extrabold text-gray-900 dark:text-white text-lg truncate">{{ $loc->alias }}</h3>
                                    @if($loc->is_default)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-teal-100 dark:bg-teal-500/20 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-500/30">Default</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium truncate">{{ $loc->address ?: 'Coordinates: ' . $loc->latitude . ', ' . $loc->longitude }}</p>
                            </div>
                        </div>

                        {{-- Actions Dropdown --}}
                        <div class="relative flex-shrink-0" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open"
                                class="w-9 h-9 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-600 dark:hover:text-gray-300 transition-colors border border-gray-100 dark:border-gray-600">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/></svg>
                            </button>

                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 p-1.5 z-50 origin-top-right">

                                @if(!$loc->is_default)
                                <form action="{{ route('profile.locations.default', $loc) }}" method="POST">
                                    @csrf
                                    <button type="submit" @click="open = false"
                                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-400 hover:bg-teal-50 dark:hover:bg-teal-500/10 hover:text-teal-600 dark:hover:text-teal-400 transition-colors group">
                                        <div class="w-7 h-7 rounded-lg bg-gray-50 dark:bg-gray-700 group-hover:bg-teal-100 dark:group-hover:bg-teal-500/20 flex items-center justify-center text-gray-400 group-hover:text-teal-500 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        Set as Default
                                    </button>
                                </form>
                                @endif

                                <button x-on:click='startEdit({!! json_encode(["id" => $loc->id, "alias" => $loc->alias, "address" => $loc->address, "latitude" => $loc->latitude, "longitude" => $loc->longitude, "is_default" => $loc->is_default]) !!}); open = false'
                                    class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors group">
                                    <div class="w-7 h-7 rounded-lg bg-gray-50 dark:bg-gray-700 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500/20 flex items-center justify-center text-gray-400 group-hover:text-indigo-500 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </div>
                                    Edit
                                </button>

                                <div class="my-1 mx-3 border-t border-gray-100 dark:border-gray-700"></div>

                                <form id="deleteForm{{ $loc->id }}" action="{{ route('profile.locations.destroy', $loc) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                                <button x-on:click='confirmDelete({{ $loc->id }}, "{{ addslashes($loc->alias) }}"); open = false'
                                    class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 transition-colors group">
                                    <div class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-500/10 group-hover:bg-red-100 dark:group-hover:bg-red-500/20 flex items-center justify-center text-red-400 group-hover:text-red-500 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </div>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Edit Mode --}}
                    <div x-show="editingId === {{ $loc->id }}" x-cloak class="p-6">
                        <form action="{{ route('profile.locations.update', $loc) }}" method="POST" class="space-y-5">
                            @csrf @method('PUT')
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Edit Location
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Label</label>
                                    <input type="text" name="alias" x-model="editAlias" required
                                        class="w-full px-5 py-3.5 bg-gray-50 dark:bg-gray-700 border-2 border-transparent focus:border-teal-500 focus:bg-white dark:focus:bg-gray-600 rounded-2xl transition-all outline-none font-bold text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Address</label>
                                        <button type="button" @click="useCurrentLocation('edit')" :disabled="gettingLocation"
                                            class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-teal-600 dark:text-teal-400 hover:text-teal-500 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                                            <svg x-show="!gettingLocation" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <span x-text="gettingLocation ? 'Locating...' : 'Use GPS'"></span>
                                        </button>
                                    </div>
                                    <input type="text" name="address" x-model="editAddress" required
                                        class="w-full px-5 py-3.5 bg-gray-50 dark:bg-gray-700 border-2 border-transparent focus:border-teal-500 focus:bg-white dark:focus:bg-gray-600 rounded-2xl transition-all outline-none font-bold text-gray-900 dark:text-white">
                                    <input type="hidden" name="latitude" :value="editLatitude">
                                    <input type="hidden" name="longitude" :value="editLongitude">
                                </div>
                            </div>

                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="is_default" value="1" x-model="editIsDefault"
                                        class="w-5 h-5 rounded-lg border-2 border-gray-300 dark:border-gray-600 text-teal-500 focus:ring-teal-500 focus:ring-offset-0">
                                    <span class="text-sm font-bold text-gray-600 dark:text-gray-400">Default location</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="cancelEdit()"
                                        class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-6 py-2.5 bg-teal-500 text-white font-bold rounded-xl shadow-lg shadow-teal-500/30 hover:bg-teal-400 transition-all active:scale-95">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Filament-styled Delete Confirmation Modal --}}
    <div x-show="deleteModalOpen" x-cloak
         class="fixed inset-0 z-[200] flex items-center justify-center p-4"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="deleteModalOpen = false"></div>

        {{-- Modal Panel --}}
        <div x-show="deleteModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-700">

            {{-- Header --}}
            <div class="px-6 pt-6 pb-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-500/10 text-red-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-gray-900 dark:text-white">Delete</h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 py-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Are you sure you want to delete <span class="font-bold text-gray-900 dark:text-white" x-text="deleteLocationAlias"></span>? This action cannot be undone.</p>
            </div>

            {{-- Footer --}}
            <div class="px-6 pb-6 pt-2 flex items-center justify-end gap-3">
                <button @click="deleteModalOpen = false"
                    class="px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Cancel
                </button>
                <button @click="submitDelete()" :disabled="deleting"
                    class="flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed w-28">
                    <svg x-show="deleting" class="w-4 h-4 animate-spin text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span x-text="deleting ? 'Deleting...' : 'Delete'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
