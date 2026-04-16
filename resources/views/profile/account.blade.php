@extends('layouts.app')

@section('skeleton')
<div class="max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-10">
        <div>
            <div class="w-32 h-8 bg-gray-200 dark:bg-gray-800 rounded-xl mb-2 animate-pulse"></div>
            <div class="w-64 h-4 bg-gray-200 dark:bg-gray-800 rounded animate-pulse"></div>
        </div>
        <div class="w-16 h-16 bg-gray-200 dark:bg-gray-800 rounded-full animate-pulse shadow-sm border border-gray-100 dark:border-gray-800"></div>
    </div>
    
    <!-- General -->
    <div class="w-20 h-3 bg-gray-200 dark:bg-gray-800 rounded mb-4 ml-2 animate-pulse"></div>
    <div class="w-full h-[90px] bg-gray-100 dark:bg-gray-800 rounded-3xl mb-8 animate-pulse border border-gray-200 dark:border-gray-700"></div>

    <!-- Preferences -->
    <div class="w-24 h-3 bg-gray-200 dark:bg-gray-800 rounded mb-4 ml-2 animate-pulse"></div>
    <div class="w-full h-[90px] bg-gray-100 dark:bg-gray-800 rounded-3xl mb-8 animate-pulse border border-gray-200 dark:border-gray-700"></div>

    <!-- Legal -->
    <div class="w-28 h-3 bg-gray-200 dark:bg-gray-800 rounded mb-4 ml-2 animate-pulse"></div>
    <div class="w-full h-[250px] bg-gray-100 dark:bg-gray-800 rounded-3xl mb-10 animate-pulse border border-gray-200 dark:border-gray-700 flex flex-col justify-evenly px-6">
        @for($i=0; $i<4; $i++)
            <div class="w-32 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        @endfor
    </div>

    <!-- Actions -->
    <div class="px-2">
        <div class="w-full h-14 bg-gray-100 dark:bg-gray-800 rounded-2xl animate-pulse border border-gray-200 dark:border-gray-700"></div>
    </div>
</div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8" x-data="{}">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white outfit tracking-tight">Account</h1>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Manage your settings and preferences</p>
            </div>
            <div
                class="w-16 h-16 bg-emerald-100 dark:bg-emerald-500/20 rounded-full flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-black text-2xl shadow-sm border border-emerald-200 dark:border-emerald-500/30">
                {{ substr($user->name ?? 'U', 0, 1) }}
            </div>
        </div>

        <!-- Details Section -->
        <h3
            class="font-black text-gray-900 dark:text-white/50 text-lg mb-4 ml-2 uppercase tracking-widest text-xs opacity-50">
            General</h3>
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
            <a href="{{ route('profile.show') }}"
                class="flex items-center justify-between p-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer group">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 flex items-center justify-center group-hover:scale-105 transition-transform border border-indigo-100/50 dark:border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg">My Profile</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-wide">Edit personal info,
                            change password</p>
                    </div>
                </div>
                <div
                    class="w-8 h-8 rounded-full bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center group-hover:bg-indigo-50 dark:group-hover:bg-indigo-500/20 group-hover:text-indigo-500 transition-colors border border-gray-100 dark:border-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        </div>

        <!-- Preferences Section -->
        <h3
            class="font-black text-gray-900 dark:text-white/50 text-lg mb-4 ml-2 uppercase tracking-widest text-xs opacity-50">
            Preferences</h3>
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
            <div class="flex items-center justify-between p-5 border-b border-gray-50 dark:border-gray-700/50">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-500 dark:text-amber-400 flex items-center justify-center border border-amber-100/50 dark:border-amber-500/20">
                        <svg x-show="!$store.darkMode.on" class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <svg x-show="$store.darkMode.on" x-cloak class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg">Dark Mode</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-wide"
                            x-text="$store.darkMode.on ? 'Switch to light mode' : 'Switch to dark mode'"></p>
                    </div>
                </div>
                <button @click="$store.darkMode.toggle()"
                    class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                    :class="$store.darkMode.on ? 'bg-emerald-500' : 'bg-gray-200'">
                    <span
                        class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        :class="$store.darkMode.on ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
        </div>

        <!-- Legal Section -->
        <h3
            class="font-black text-gray-900 dark:text-white/50 text-lg mb-4 ml-2 uppercase tracking-widest text-xs opacity-50">
            Legal & Support</h3>
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-10">
            <a href="{{ route('legal.terms') }}"
                class="flex items-center justify-between p-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer border-b border-gray-50 dark:border-gray-700/50 group">
                <span class="font-bold text-gray-700 dark:text-gray-300">Terms of Service</span>
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-emerald-500 transition-colors"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            <a href="{{ route('legal.privacy') }}"
                class="flex items-center justify-between p-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer border-b border-gray-50 dark:border-gray-700/50 group">
                <span class="font-bold text-gray-700 dark:text-gray-300">Privacy Policy</span>
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-emerald-500 transition-colors"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            <a href="{{ route('legal.cookies') }}"
                class="flex items-center justify-between p-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer border-b border-gray-50 dark:border-gray-700/50 group">
                <span class="font-bold text-gray-700 dark:text-gray-300">Cookie Policy</span>
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-emerald-500 transition-colors"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            <a href="{{ route('help.center') }}"
                class="flex items-center justify-between p-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer group">
                <span class="font-bold text-gray-700 dark:text-gray-300">Help Center</span>
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-emerald-500 transition-colors"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- Actions -->
        <div class="px-2">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full py-4 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-500 font-bold rounded-2xl border border-red-100 dark:border-red-500/20 hover:bg-red-100 dark:hover:bg-red-500/20 hover:text-red-700 dark:hover:text-red-400 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    Log Out
                </button>
            </form>
        </div>
    </div>
@endsection