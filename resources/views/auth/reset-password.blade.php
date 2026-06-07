@extends('layouts.app')
@section('hideFooter', '1')

@section('skeleton')
<div class="min-h-[100svh] bg-white dark:bg-gray-950 mt-5">
    <div class="mx-auto w-full max-w-md px-5 pb-8 pt-10 sm:px-6 sm:pt-12 space-y-8">
        <div class="space-y-2">
            <div class="w-24 h-4 bg-gray-100 dark:bg-gray-800 rounded animate-pulse"></div>
            <div class="w-48 h-10 bg-gray-100 dark:bg-gray-800 rounded-xl animate-pulse"></div>
            <div class="w-64 h-4 bg-gray-50 dark:bg-gray-900 rounded animate-pulse"></div>
        </div>
        
        <div class="space-y-4 pt-8">
            <div class="w-full h-12 bg-gray-50 dark:bg-gray-900/50 rounded-2xl animate-pulse"></div>
            <div class="w-full h-12 bg-gray-50 dark:bg-gray-900/50 rounded-2xl animate-pulse"></div>
            <div class="w-full h-12 bg-gray-50 dark:bg-gray-900/50 rounded-2xl animate-pulse"></div>
        </div>

        <div class="w-full h-12 bg-gray-200 dark:bg-gray-800 rounded-2xl animate-pulse mt-4"></div>
    </div>
</div>
@endsection

@section('content')
<div class="min-h-[100svh] bg-white dark:bg-gray-950 mt-5">
    <div class="mx-auto w-full max-w-md px-5 pb-8 pt-10 sm:px-6 sm:pt-12">
        <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 80)" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Security</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight text-gray-900 dark:text-white outfit">Reset Password</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Choose a strong new password for your account.</p>

            @if ($errors->any())
                <p class="mt-4 text-sm font-semibold text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="relative">
                    <input id="email" name="email" type="email" value="{{ $email ?? old('email') }}" required readonly placeholder=" "
                        class="peer h-12 w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 pt-5 text-sm font-medium text-gray-500 outline-none dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-400">
                    <label for="email" class="absolute left-4 top-2 px-1 text-xs font-semibold text-gray-400">
                        Email Address
                    </label>
                </div>

                <div x-data="{ showPassword: false }">
                    <div class="relative">
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required autofocus placeholder=" "
                            class="peer h-12 w-full rounded-2xl border border-gray-300 bg-white px-4 pr-11 pt-5 text-sm font-medium text-gray-900 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <label for="password" class="pointer-events-none absolute left-4 top-2 px-1 text-xs font-semibold text-gray-500 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:bg-transparent peer-placeholder-shown:px-0 peer-placeholder-shown:text-sm peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:translate-y-0 peer-focus:bg-white peer-focus:px-1 peer-focus:text-xs peer-focus:font-semibold peer-focus:text-emerald-600 dark:bg-gray-900 dark:text-gray-400 dark:peer-placeholder-shown:bg-transparent dark:peer-focus:bg-gray-900 dark:peer-focus:text-emerald-400">
                            New Password
                        </label>
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-3 text-gray-400 transition-colors hover:text-emerald-500 dark:text-gray-500">
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <input id="password_confirmation" name="password_confirmation" type="password" required placeholder=" "
                        class="peer h-12 w-full rounded-2xl border border-gray-300 bg-white px-4 pt-5 text-sm font-medium text-gray-900 placeholder:text-transparent outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <label for="password_confirmation" class="pointer-events-none absolute left-4 top-2 px-1 text-xs font-semibold text-gray-500 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:bg-transparent peer-placeholder-shown:px-0 peer-placeholder-shown:text-sm peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:translate-y-0 peer-focus:bg-white peer-focus:px-1 peer-focus:text-xs peer-focus:font-semibold peer-focus:text-emerald-600 dark:bg-gray-900 dark:text-gray-400 dark:peer-placeholder-shown:bg-transparent dark:peer-focus:bg-gray-900 dark:peer-focus:text-emerald-400">
                        Confirm New Password
                    </label>
                </div>

                <button type="submit" class="h-12 w-full rounded-2xl bg-emerald-500 text-base font-black text-white transition hover:bg-emerald-400 mt-2">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
