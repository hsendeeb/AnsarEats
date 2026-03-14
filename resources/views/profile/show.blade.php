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
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
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
