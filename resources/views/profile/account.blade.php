@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 outfit tracking-tight">Account</h1>
            <p class="text-gray-500 font-medium">Manage your settings and preferences</p>
        </div>
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 font-black text-2xl shadow-sm border border-emerald-200">
            {{ substr($user->name ?? 'U', 0, 1) }}
        </div>
    </div>

    <!-- Details Section -->
    <h3 class="font-black text-gray-900 text-lg mb-4 ml-2 uppercase tracking-widest text-xs opacity-50">General</h3>
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <a href="{{ route('profile.show') }}" class="flex items-center justify-between p-5 hover:bg-gray-50 transition-colors cursor-pointer group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center group-hover:scale-105 transition-transform border border-indigo-100/50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">My Profile</h3>
                    <p class="text-sm text-gray-500 font-medium tracking-wide">Edit personal info, change password</p>
                </div>
            </div>
            <div class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-colors border border-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </a>
    </div>

    <!-- Legal Section -->
    <h3 class="font-black text-gray-900 text-lg mb-4 ml-2 uppercase tracking-widest text-xs opacity-50">Legal & Support</h3>
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-10">
        <a href="{{ route('legal.terms') }}" class="flex items-center justify-between p-5 hover:bg-gray-50 transition-colors cursor-pointer border-b border-gray-50 group">
            <span class="font-bold text-gray-700">Terms of Service</span>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
        </a>
        <a href="{{ route('legal.privacy') }}" class="flex items-center justify-between p-5 hover:bg-gray-50 transition-colors cursor-pointer border-b border-gray-50 group">
            <span class="font-bold text-gray-700">Privacy Policy</span>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
        </a>
        <a href="{{ route('legal.cookies') }}" class="flex items-center justify-between p-5 hover:bg-gray-50 transition-colors cursor-pointer border-b border-gray-50 group">
            <span class="font-bold text-gray-700">Cookie Policy</span>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
        </a>
        <a href="{{ route('help.center') }}" class="flex items-center justify-between p-5 hover:bg-gray-50 transition-colors cursor-pointer group">
            <span class="font-bold text-gray-700">Help Center</span>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
        </a>
    </div>

    <!-- Actions -->
    <div class="px-2">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full py-4 bg-red-50 text-red-600 font-bold rounded-2xl border border-red-100 hover:bg-red-100 hover:text-red-700 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Log Out
            </button>
        </form>
    </div>
</div>
@endsection
