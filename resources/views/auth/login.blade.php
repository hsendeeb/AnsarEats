@extends('layouts.app')
@section('hideFooter', '1')

@section('content')
<div class="min-h-[100svh] bg-white dark:bg-gray-950 mt-5">
    <div class="mx-auto w-full max-w-md px-5 pb-8 pt-10 sm:px-6 sm:pt-12">
        <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 80)" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Welcome back</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight text-gray-900 dark:text-white outfit">Sign in</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Use your account to continue.</p>

            @if (session('status'))
                <div class="mt-4 p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl">
                    <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <p class="mt-4 text-sm font-semibold text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
            @endif

            <div class="mt-8 mb-4 space-y-2.5">
                <a href="{{ route('social.redirect', 'google') }}" class="relative flex mb-4 h-12 w-full items-center justify-center rounded-2xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                    <span class="absolute left-4 flex h-5 w-5 items-center justify-center" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#4285F4" d="M21.6 12.23c0-.68-.06-1.34-.17-1.97H12v3.73h5.39a4.62 4.62 0 0 1-2 3.03v2.52h3.24c1.9-1.75 2.97-4.33 2.97-7.31Z"/>
                            <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.61-2.44l-3.24-2.52c-.9.6-2.05.95-3.37.95-2.59 0-4.79-1.75-5.58-4.1H3.07v2.6A9.99 9.99 0 0 0 12 22Z"/>
                            <path fill="#FBBC05" d="M6.42 13.89A5.99 5.99 0 0 1 6.11 12c0-.66.11-1.3.31-1.89V7.51H3.07A9.99 9.99 0 0 0 2 12c0 1.61.39 3.14 1.07 4.49l3.35-2.6Z"/>
                            <path fill="#EA4335" d="M12 6.01c1.47 0 2.79.5 3.82 1.48l2.87-2.87C16.95 2.98 14.69 2 12 2 8.09 2 4.72 4.24 3.07 7.51l3.35 2.6c.79-2.35 2.99-4.1 5.58-4.1Z"/>
                        </svg>
                    </span>
                    <span>Continue with Google</span>
                </a>

                <a href="{{ route('social.redirect', 'facebook') }}" class="relative flex h-12 w-full items-center justify-center rounded-2xl bg-[#1877F2] px-4 text-sm font-bold text-white transition-colors hover:bg-[#1669d9]">
                    <span class="absolute left-4 flex h-5 w-5 items-center justify-center" aria-hidden="true">
                        <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M24 12.1C24 5.4 18.6 0 12 0S0 5.4 0 12.1c0 6 4.4 11 10.1 11.9v-8.4H7.1v-3.5h3V9.4c0-3 1.8-4.7 4.5-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9v2.3h3.4l-.5 3.5h-2.9V24C19.6 23.1 24 18.1 24 12.1Z"/>
                        </svg>
                    </span>
                    <span>Continue with Facebook</span>
                </a>
            </div>

            <div class="my-6 flex items-center gap-3">
                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></div>
                <span class="text-[11px] font-bold uppercase tracking-widest text-gray-400">or</span>
                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></div>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div class="relative">
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus placeholder=" "
                        class="peer h-12 w-full rounded-2xl border border-gray-300 bg-white px-4 pt-5 text-sm font-medium text-gray-900 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <label for="email" class="pointer-events-none absolute left-4 top-2  px-1 text-xs font-semibold text-gray-500 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:bg-transparent peer-placeholder-shown:px-0 peer-placeholder-shown:text-sm peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:translate-y-0 peer-focus:bg-white peer-focus:px-1 peer-focus:text-xs peer-focus:font-semibold peer-focus:text-emerald-600 dark:bg-gray-900 dark:text-gray-400 dark:peer-placeholder-shown:bg-transparent dark:peer-focus:bg-gray-900 dark:peer-focus:text-emerald-400">
                        Email
                    </label>
                </div>

                <div x-data="{ showPassword: false }">
                    <div class="relative">
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required placeholder=" "
                            class="peer h-12 w-full rounded-2xl border border-gray-300 bg-white px-4 pr-11 pt-5 text-sm font-medium text-gray-900 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <label for="password" class="pointer-events-none absolute left-4 top-2  px-1 text-xs font-semibold text-gray-500 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:bg-transparent peer-placeholder-shown:px-0 peer-placeholder-shown:text-sm peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:translate-y-0 peer-focus:bg-white peer-focus:px-1 peer-focus:text-xs peer-focus:font-semibold peer-focus:text-emerald-600 dark:bg-gray-900 dark:text-gray-400 dark:peer-placeholder-shown:bg-transparent dark:peer-focus:bg-gray-900 dark:peer-focus:text-emerald-400">
                            Password
                        </label>
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-3 text-gray-400 transition-colors hover:text-emerald-500 dark:text-gray-500">
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="h-12 w-full rounded-2xl bg-emerald-500 text-base font-black text-white transition hover:bg-emerald-400">
                    Sign In
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-gray-500 dark:text-gray-400">
                No account?
                <a href="{{ route('register') }}" class="font-bold text-emerald-600 dark:text-emerald-400">Create one</a>
            </p>
        </div>
    </div>
</div>
@endsection
