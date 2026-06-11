@extends('layouts.app')

@push('styles')
<style>
    #map-picker { width: 100%; height: 100%; }
    #map-picker img { max-width: none !important; }
    #edit-mini-map { width: 100%; height: 100%; min-height: 100%; }
    #edit-mini-map img { max-width: none !important; }
    .gm-style .gm-style-iw-c { border-radius: 16px !important; padding: 4px !important; }
    .gm-style .gm-style-iw-d { overflow: hidden !important; }
    @media (max-width: 640px) {
        #mini-map-container { height: 160px !important; }
    }
</style>
@endpush

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
    overlayOpen: false,
    step: 1,
    editingId: null,

    selectedLat: '',
    selectedLng: '',
    selectedAddress: '',
    searchQuery: '',
    searchResults: [],
    searchOpen: false,
    _map: null,
    _miniMap: null,
    _geo: null,
    _geoService: null,
    _mapListener: null,
    _autoSuggest: null,

    alias: 'Home',
    street: '',
    building: '',
    apartment: '',
    is_default: false,
    showCustomAlias: false,
    customAlias: '',

    deleteModalOpen: false,
    deleteLocationId: null,
    deleteLocationAlias: '',
    deleting: false,

    _darkStyles: [
        { elementType: 'geometry', stylers: [{ color: '#242f3e' }] },
        { elementType: 'labels.text.stroke', stylers: [{ color: '#242f3e' }] },
        { elementType: 'labels.text.fill', stylers: [{ color: '#746855' }] },
        { featureType: 'administrative.locality', elementType: 'labels.text.fill', stylers: [{ color: '#d59563' }] },
        { featureType: 'poi', elementType: 'labels.text.fill', stylers: [{ color: '#d59563' }] },
        { featureType: 'poi.park', elementType: 'geometry', stylers: [{ color: '#263c3f' }] },
        { featureType: 'poi.park', elementType: 'labels.text.fill', stylers: [{ color: '#6b9a76' }] },
        { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#38414e' }] },
        { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#212a37' }] },
        { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#9ca5b3' }] },
        { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#746855' }] },
        { featureType: 'road.highway', elementType: 'geometry.stroke', stylers: [{ color: '#1f2835' }] },
        { featureType: 'road.highway', elementType: 'labels.text.fill', stylers: [{ color: '#f3d19c' }] },
        { featureType: 'transit', elementType: 'geometry', stylers: [{ color: '#2f3948' }] },
        { featureType: 'transit.station', elementType: 'labels.text.fill', stylers: [{ color: '#d59563' }] },
        { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#17263c' }] },
        { featureType: 'water', elementType: 'labels.text.fill', stylers: [{ color: '#515c6d' }] },
        { featureType: 'water', elementType: 'labels.text.stroke', stylers: [{ color: '#17263c' }] },
    ],

    get finalAlias() {
        return this.showCustomAlias ? this.customAlias : this.alias;
    },
    get addressPreview() {
        const p = [];
        if (this.street) p.push(this.street);
        if (this.building) p.push('Bldg ' + this.building);
        if (this.apartment) p.push('Apt ' + this.apartment);
        let a = this.selectedAddress || '';
        if (p.length) a = a ? a + ', ' + p.join(', ') : p.join(', ');
        return a;
    },
    get isFormValid() {
        return this.finalAlias && this.street;
    },

    openAddLocation() {
        this.resetForm();
        this.editingId = null;
        this.step = 1;
        this.overlayOpen = true;
        this.$nextTick(() => { setTimeout(() => this.initMap(), 80); });
    },
    openEditLocation(loc) {
        this.resetForm();
        this.editingId = loc.id;
        this.alias = loc.alias || 'Home';
        this.street = loc.address || '';
        this.selectedLat = loc.latitude || '';
        this.selectedLng = loc.longitude || '';
        this.selectedAddress = loc.address || '';
        this.is_default = loc.is_default;
        this.step = 2;
        this.overlayOpen = true;
        this.$nextTick(() => { setTimeout(() => this.initMiniMap(), 80); });
    },
    closeOverlay() {
        this.overlayOpen = false;
        this.destroyMaps();
        this.step = 1;
        this.resetForm();
    },
    resetForm() {
        this.alias = 'Home';
        this.street = '';
        this.building = '';
        this.apartment = '';
        this.is_default = false;
        this.showCustomAlias = false;
        this.customAlias = '';
        this.selectedLat = '';
        this.selectedLng = '';
        this.selectedAddress = '';
        this.searchQuery = '';
        this.searchResults = [];
        this.searchOpen = false;
    },

    initMap() {
        const el = document.getElementById('map-picker');
        if (!el || this._map) return;
        if (typeof google === 'undefined' || !google.maps) {
            setTimeout(() => this.initMap(), 200);
            return;
        }
        const hasCoords = this.selectedLat && this.selectedLng;
        const center = hasCoords
            ? { lat: parseFloat(this.selectedLat), lng: parseFloat(this.selectedLng) }
            : { lat: 33.8547, lng: 35.8623 };
        const isDark = Alpine.store('darkMode')?.on;
        const map = new google.maps.Map(el, {
            center,
            zoom: hasCoords ? 16 : 12,
            disableDefaultUI: true,
            gestureHandling: 'greedy',
            styles: isDark ? this._darkStyles : null,
            zoomControl: false,
        });
        const listener = map.addListener('idle', () => {
            const c = map.getCenter();
            this.selectedLat = c.lat().toFixed(6);
            this.selectedLng = c.lng().toFixed(6);
            this.reverseGeocode(c.lat(), c.lng());
        });
        const c = map.getCenter();
        this.selectedLat = c.lat().toFixed(6);
        this.selectedLng = c.lng().toFixed(6);
        this.reverseGeocode(c.lat(), c.lng());
        if (!hasCoords && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (p) => { map.setCenter({ lat: p.coords.latitude, lng: p.coords.longitude }); map.setZoom(16); },
                () => {},
                { enableHighAccuracy: true, timeout: 5000 }
            );
        }
        this._map = map;
        this._mapListener = listener;
        this._geoService = new google.maps.Geocoder();
        google.maps.importLibrary('places').then(function(m) {
            this._autoSuggest = m.AutocompleteSuggestion;
        }.bind(this));
    },
    initMiniMap() {
        const el = document.getElementById('edit-mini-map');
        if (!el) return;
        if (typeof google === 'undefined' || !google.maps) {
            setTimeout(() => this.initMiniMap(), 200);
            return;
        }
        if (this._miniMap) { this._miniMap = null; }
        el.innerHTML = '';
        const lat = parseFloat(this.selectedLat);
        const lng = parseFloat(this.selectedLng);
        if (isNaN(lat) || isNaN(lng)) return;
        const isDark = Alpine.store('darkMode')?.on;
        const map = new google.maps.Map(el, {
            center: { lat, lng },
            zoom: 15,
            disableDefaultUI: true,
            gestureHandling: 'none',
            keyboardShortcuts: false,
            styles: isDark ? this._darkStyles : null,
        });
        const pinSvg = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
            '<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 32 48\' width=\'28\' height=\'42\'><path d=\'M16 2C8.27 2 2 8.27 2 16c0 12 14 30 14 30s14-18 14-30C30 8.27 23.73 2 16 2z\' fill=\'#14b8a6\' stroke=\'white\' stroke-width=\'2.5\'/><circle cx=\'16\' cy=\'16\' r=\'5\' fill=\'white\'/></svg>'
        );
        new google.maps.Marker({
            position: { lat, lng },
            map,
            icon: { url: pinSvg, scaledSize: new google.maps.Size(28, 42), anchor: new google.maps.Point(14, 42) },
        });
        this._miniMap = map;
    },
    destroyMaps() {
        if (this._mapListener) { google.maps.event.removeListener(this._mapListener); this._mapListener = null; }
        if (this._map) { this._map = null; }
        if (this._miniMap) { this._miniMap = null; }
        const p = document.getElementById('map-picker'); if (p) p.innerHTML = '';
        const m = document.getElementById('edit-mini-map'); if (m) m.innerHTML = '';
        if (this._geo) { clearTimeout(this._geo); this._geo = null; }
    },
    reverseGeocode(lat, lng) {
        if (!this._geoService) return;
        if (this._geo) clearTimeout(this._geo);
        this._geo = setTimeout(() => {
            this._geoService.geocode(
                { location: { lat, lng } },
                (results, status) => {
                    if (status === google.maps.GeocoderStatus.OK && results && results[0]) {
                        this.selectedAddress = results[0].formatted_address;
                    } else {
                        this.selectedAddress = lat.toFixed(6) + ', ' + lng.toFixed(6);
                    }
                }
            );
        }, 400);
    },
    searchLocation() {
        if (!this.searchQuery.trim()) { this.searchResults = []; this.searchOpen = false; return; }
        var as = this._autoSuggest;
        if (!as) return;
        as.fetchAsync({ input: this.searchQuery }).then(function(r) {
            var suggestions = r.suggestions;
            if (suggestions) {
                this.searchResults = suggestions.map(function(s) {
                    return {
                        suggestion: s,
                        description: s.placePrediction.text.text,
                        types: s.placePrediction.types,
                    };
                });
                this.searchOpen = true;
            } else {
                this.searchResults = [];
                this.searchOpen = false;
            }
        }.bind(this)).catch(function() {
            this.searchResults = [];
            this.searchOpen = false;
        }.bind(this));
    },
    selectSearchResult(r) {
        this.searchQuery = r.description;
        this.searchOpen = false;
        var place = r.suggestion.toPlace();
        var self = this;
        place.fetchFields({ fields: ['location', 'formattedAddress'] }).then(function() {
            self.selectedLat = place.location.lat().toFixed(6);
            self.selectedLng = place.location.lng().toFixed(6);
            self.selectedAddress = place.formattedAddress || r.description;
            if (self._map) {
                self._map.setCenter({ lat: place.location.lat(), lng: place.location.lng() });
                self._map.setZoom(16);
            }
        }).catch(function() {});
    },
    useCurrentLocation() {
        if (!navigator.geolocation || !this._map) return;
        navigator.geolocation.getCurrentPosition(
            (p) => {
                this._map.setCenter({ lat: p.coords.latitude, lng: p.coords.longitude });
                this._map.setZoom(16);
            },
            () => {},
            { enableHighAccuracy: true, timeout: 10000 }
        );
    },

    selectAlias(chip) {
        if (chip === '__custom__') {
            this.showCustomAlias = true;
            this.$nextTick(() => { const i = document.getElementById('custom-alias-input'); if (i) i.focus(); });
        } else {
            this.alias = chip;
            this.showCustomAlias = false;
            this.customAlias = '';
        }
    },

    confirmLocation() { this.step = 2; this.$nextTick(() => { setTimeout(() => this.initMiniMap(), 80); }); },
    adjustPin() {
        this.step = 1;
        if (this._miniMap) { this._miniMap = null; }
        const m = document.getElementById('edit-mini-map'); if (m) m.innerHTML = '';
        this.$nextTick(() => { setTimeout(() => this.initMap(), 80); });
    },

    submitSave() {
        const f = document.createElement('form'); f.method = 'POST';
        f.action = this.editingId ? '/locations/' + this.editingId : '/locations';
        const t = document.querySelector('meta[name=\'csrf-token\']')?.content || '';
        const add = (n, v) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v || ''; f.appendChild(i); };
        add('_token', t);
        if (this.editingId) add('_method', 'PUT');
        add('alias', this.finalAlias);
        add('address', this.addressPreview);
        add('latitude', this.selectedLat);
        add('longitude', this.selectedLng);
        add('is_default', this.is_default ? '1' : '0');
        document.body.appendChild(f);
        f.submit();
    },

    confirmDelete(id, alias) { this.deleteLocationId = id; this.deleteLocationAlias = alias; this.deleteModalOpen = true; this.deleting = false; },
    submitDelete() { if (this.deleteLocationId) { this.deleting = true; document.getElementById('deleteForm' + this.deleteLocationId).submit(); } }
}">
    {{-- ======================== PAGE HEADER ======================== --}}
    <div class="mb-10 relative">
        <a href="{{ route('profile.account') }}" class="inline-flex items-center gap-2 text-sm font-bold text-gray-400 dark:text-gray-500 hover:text-emerald-500 transition-colors mb-6 group">
            <div class="w-8 h-8 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center group-hover:bg-emerald-50 dark:group-hover:bg-emerald-500/10 transition-colors border border-gray-100 dark:border-gray-700 group-hover:border-emerald-100 dark:group-hover:border-emerald-500/20">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </div>
            Back to Account
        </a>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl md:text-4xl font-extrabold text-gray-900 dark:text-white outfit tracking-tight mb-2">My Locations</h1>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Save your delivery addresses for faster checkout.</p>
            </div>
            <button @click="openAddLocation()"
                class="inline-flex items-center gap-2 px-6 py-3 bg-teal-500 text-white font-extrabold rounded-2xl hover:bg-teal-400 hover:-translate-y-0.5 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Add Location
            </button>
        </div>
    </div>

    {{-- ======================== LOCATIONS LIST ======================== --}}
    @if($locations->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
            <div class="w-20 h-20 rounded-full bg-teal-50 dark:bg-teal-500/10 text-teal-500 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mb-2">No saved locations</h3>
            <p class="text-gray-500 dark:text-gray-400 font-medium mb-6">Add your first delivery address to speed up checkout.</p>
            <button @click="openAddLocation()"
                class="inline-flex items-center gap-2 px-6 py-3 bg-teal-500 text-white font-extrabold rounded-2xl cursor-pointer hover:bg-teal-400 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Add Your First Location
            </button>
        </div>
    @else
        <div class="space-y-4">
            @foreach($locations as $loc)
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 transition-all hover:shadow-md">
                    <div class="p-5 flex items-center justify-between gap-4">
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

                                <button @click='openEditLocation({!! json_encode(["id" => $loc->id, "alias" => $loc->alias, "address" => $loc->address, "latitude" => $loc->latitude, "longitude" => $loc->longitude, "is_default" => $loc->is_default]) !!}); open = false'
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
                                <button @click='confirmDelete({{ $loc->id }}, "{{ addslashes($loc->alias) }}"); open = false'
                                    class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 transition-colors group">
                                    <div class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-500/10 group-hover:bg-red-100 dark:group-hover:bg-red-500/20 flex items-center justify-center text-red-400 group-hover:text-red-500 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </div>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ======================== STEP 1: MAP PICKER ======================== --}}
    <div x-show="overlayOpen && step === 1" x-cloak
         class="fixed inset-0 z-[150] bg-white dark:bg-gray-900 overflow-hidden">

        <div id="map-picker" class="w-full h-full absolute inset-0"></div>

        {{-- Fixed center pin --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-10 pointer-events-none" style="margin-top: -12px;">
            <svg viewBox="0 0 32 48" width="32" height="48" class="drop-shadow-lg">
                <defs>
                    <filter id="pin-shadow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.25"/>
                    </filter>
                </defs>
                <path d="M16 2C8.27 2 2 8.27 2 16c0 12 14 30 14 30s14-18 14-30C30 8.27 23.73 2 16 2z" fill="#14b8a6" stroke="white" stroke-width="2.5" filter="url(#pin-shadow)"/>
                <circle cx="16" cy="16" r="5" fill="white"/>
            </svg>
        </div>

        {{-- Tooltip: "Your order will be delivered here" --}}

        {{-- Search bar --}}
        <div class="absolute top-4 left-4 right-4 z-20" @click.away="searchOpen = false">
            <div class="relative">
                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 px-4 py-3">
                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" x-model="searchQuery"
                        @input.debounce.300ms="searchLocation()"
                        @focus="if(searchResults.length) searchOpen = true"
                        placeholder="Search area..."
                        class="w-full bg-transparent outline-none text-sm font-bold text-gray-900 dark:text-white placeholder-gray-400">
                    <button @click="closeOverlay()" class="flex-shrink-0 w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div x-show="searchOpen && searchResults.length" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 max-h-64 overflow-y-auto z-30">
                    <template x-for="(r, i) in searchResults" :key="i">
                        <button @click="selectSearchResult(r)"
                            class="w-full flex items-start gap-3 px-4 py-3.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                            <svg class="w-4 h-4 mt-0.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <div class="min-w-0">
                                <span class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2" x-text="r.description"></span>
                                <span class="text-xs text-gray-400 font-medium capitalize" x-text="r.types?.[0] || ''"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Bottom sheet (Toters style) --}}
        <div class="absolute bottom-0 left-0 right-0 z-20 pointer-events-none">
            <div class="bg-white dark:bg-gray-800 rounded-t-3xl shadow-2xl border-t border-gray-100 dark:border-gray-700 px-6 pt-6 pb-8 pointer-events-auto" style="padding-bottom: max(2rem, env(safe-area-inset-bottom, 1rem));">
                <div class="flex justify-center mb-4 -mt-1">
                    <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                </div>
                <h3 class="text-lg font-extrabold text-gray-900 dark:text-white mb-1">Confirm delivery location</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-5 line-clamp-2 leading-relaxed" x-text="selectedAddress || 'Drag the map to set your location...'"></p>
                <button @click="confirmLocation()"
                    class="w-full py-4 bg-teal-500 text-white font-extrabold text-base rounded-2xl hover:bg-teal-400 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Confirm delivery location
                </button>
            </div>
        </div>

       

    </div>

    {{-- ======================== STEP 2: ADDRESS DETAILS ======================== --}}
    <div x-show="overlayOpen && step === 2" x-cloak
         class="fixed inset-0 z-[150] bg-white dark:bg-gray-900 flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 pt-4 pb-3 border-b border-gray-100 dark:border-gray-700/50 flex-shrink-0 bg-white dark:bg-gray-900 z-10">
            <button @click="adjustPin()" class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors flex-shrink-0">
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Addresses</h2>
        </div>

        <div class="flex-1 overflow-y-auto">
            {{-- Mini map preview --}}
            <div class="mx-4 mt-4 rounded-2xl overflow-hidden relative border border-gray-100 dark:border-gray-700" id="mini-map-container" style="height: 180px;">
                <div id="edit-mini-map" class="w-full h-full"></div>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-4 pt-12">
                    <p class="text-white text-sm font-bold leading-snug line-clamp-2 drop-shadow-md" x-text="selectedAddress || ''"></p>
                </div>
                <button @click="adjustPin()"
                    class="absolute top-3 right-3 bg-white dark:bg-gray-800 text-xs font-black uppercase tracking-wider text-teal-600 dark:text-teal-400 px-3 py-2 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Adjust Pin
                </button>
            </div>

            {{-- Form --}}
            <div class="px-4 pt-6 pb-4 space-y-6">
                {{-- Alias chips --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wider">Label</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="chip in ['Home', 'Work', &quot;Mom's&quot;, 'Other']" :key="chip">
                            <button @click="selectAlias(chip)"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold border-2 transition-all"
                                :class="!showCustomAlias && alias === chip
                                    ? 'bg-teal-500 text-white border-teal-500 shadow-md shadow-teal-500/20'
                                    : 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-teal-300 dark:hover:border-teal-600'">
                                <span x-text="chip"></span>
                            </button>
                        </template>
                        <button @click="selectAlias('__custom__')"
                            class="px-5 py-2.5 rounded-xl text-sm font-bold border-2 border-dashed transition-all"
                            :class="showCustomAlias
                                ? 'bg-teal-500 text-white border-teal-500 shadow-md shadow-teal-500/20'
                                : 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-teal-300 dark:hover:border-teal-600'">
                            <span x-text="showCustomAlias ? 'Custom' : '+ Custom'"></span>
                        </button>
                    </div>
                    <div x-show="showCustomAlias" x-cloak class="mt-3">
                        <input type="text" id="custom-alias-input" x-model="customAlias" placeholder="Enter custom label..."
                            class="w-full px-5 py-3.5 bg-gray-50 dark:bg-gray-700 border-2 border-teal-500 focus:border-teal-500 focus:bg-white dark:focus:bg-gray-600 rounded-2xl transition-all outline-none font-bold text-gray-900 dark:text-white placeholder-gray-400">
                    </div>
                </div>

                {{-- Street --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Street <span class="text-red-500">*</span></label>
                    <input type="text" x-model="street" required placeholder="e.g. 123 Main Street"
                        class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-700 border-2 border-transparent focus:border-teal-500 focus:bg-white dark:focus:bg-gray-600 rounded-2xl transition-all outline-none font-bold text-gray-900 dark:text-white placeholder-gray-400">
                </div>

                {{-- Building & Apartment --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Building</label>
                        <input type="text" x-model="building" placeholder="e.g. 5"
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-700 border-2 border-transparent focus:border-teal-500 focus:bg-white dark:focus:bg-gray-600 rounded-2xl transition-all outline-none font-bold text-gray-900 dark:text-white placeholder-gray-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Apartment</label>
                        <input type="text" x-model="apartment" placeholder="e.g. 7A"
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-700 border-2 border-transparent focus:border-teal-500 focus:bg-white dark:focus:bg-gray-600 rounded-2xl transition-all outline-none font-bold text-gray-900 dark:text-white placeholder-gray-400">
                    </div>
                </div>

                {{-- Default checkbox --}}
                <label class="flex items-center gap-3 cursor-pointer group py-2">
                    <input type="checkbox" x-model="is_default"
                        class="w-5 h-5 rounded-lg border-2 border-gray-300 dark:border-gray-600 text-teal-500 focus:ring-teal-500 focus:ring-offset-0 transition-colors">
                    <span class="text-sm font-bold text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Set as default location</span>
                </label>

                <div class="h-6"></div>
            </div>
        </div>

        {{-- Sticky bottom button --}}
        <div class="flex-shrink-0 border-t border-gray-100 dark:border-gray-700/50 bg-white dark:bg-gray-900 px-4 pt-4 pb-4" style="padding-bottom: max(1rem, env(safe-area-inset-bottom, 1rem));">
            <button @click="submitSave()" :disabled="!isFormValid"
                class="w-full py-4 text-base font-extrabold rounded-2xl transition-all flex items-center justify-center gap-2"
                :class="isFormValid
                    ? 'bg-teal-500 text-white hover:bg-teal-400 active:scale-[0.98] shadow-lg shadow-teal-500/30'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                <span x-text="editingId ? 'Update Location' : 'Save Location'"></span>
            </button>
        </div>
    </div>

    {{-- ======================== DELETE MODAL ======================== --}}
    <div x-show="deleteModalOpen" x-cloak
         class="fixed inset-0 z-[200] flex items-center justify-center p-4"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="deleteModalOpen = false"></div>
        <div x-show="deleteModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="px-6 pt-6 pb-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-500/10 text-red-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-gray-900 dark:text-white">Delete</h3>
                </div>
            </div>
            <div class="px-6 py-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Are you sure you want to delete <span class="font-bold text-gray-900 dark:text-white" x-text="deleteLocationAlias"></span>? This action cannot be undone.</p>
            </div>
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

@push('scripts')
<script>
window.gmapsLoaded = function() {
    window._gmapsReady = true;
    (window._gmapsQueue || []).forEach(function(fn) { fn(); });
    window._gmapsQueue = [];
};
var key = '{{ config("services.google_maps.key") }}';
if (!key) { console.warn('Google Maps API key missing'); } else {
    var s = document.createElement('script');
    s.src = 'https://maps.googleapis.com/maps/api/js?key=' + key + '&libraries=places,geocoding&callback=gmapsLoaded&loading=async';
    s.async = true;
    s.defer = true;
    document.head.appendChild(s);
}
</script>
@endpush