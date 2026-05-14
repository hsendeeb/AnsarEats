@extends('layouts.app')

@section('skeleton')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-12">
        <div class="w-40 h-6 bg-gray-200 dark:bg-gray-800 rounded-lg mb-6 animate-pulse"></div>
        <div class="w-56 h-10 bg-gray-200 dark:bg-gray-800 rounded-xl mb-2 animate-pulse"></div>
        <div class="w-72 h-5 bg-gray-200 dark:bg-gray-800 rounded-lg animate-pulse"></div>
    </div>
    <div class="max-w-2xl mx-auto bg-gray-100 dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 overflow-hidden h-[340px] animate-pulse">
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-12 relative">
        <a href="{{ route('profile.account') }}" class="inline-flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-emerald-500 transition-colors mb-6 group">
            <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-emerald-50 transition-colors border border-gray-100 group-hover:border-emerald-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </div>
            Back to Account
        </a>
        <h1 class="text-2xl md:text-4xl font-extrabold text-gray-900 outfit tracking-tight mb-2">Change Password</h1>
        <p class="text-gray-500 font-medium">Update your password in a dedicated security screen.</p>
    </div>

    <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-6">
                @csrf

              
                <div class="space-y-4">
                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" id="password"
                            class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900">
                        @error('password') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900">
                    </div>
                </div>

                <div class="pt-1">
                    <a href="{{ route('password.request') }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-600 hover:text-emerald-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10a4 4 0 118 0c0 1.657-1.343 3-3 3h-1v1m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"></path>
                        </svg>
                        <span>Forgot password?</span>
                    </a>
                </div>

                <div class="pt-4 flex flex-col gap-3 md:flex-row md:items-center">
                    <button type="submit" class="w-full md:w-auto px-10 py-4 bg-emerald-500 text-white font-extrabold rounded-2xl  hover:bg-emerald-400 hover:-translate-y-1 transition-all active:scale-95">
                        Save Password
                    </button>
                    <a href="{{ route('profile.show') }}" class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-4 rounded-2xl border border-gray-200 bg-gray-50 text-gray-700 font-bold hover:border-gray-300 hover:bg-white transition-all">
                        <span>Back to Profile</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
