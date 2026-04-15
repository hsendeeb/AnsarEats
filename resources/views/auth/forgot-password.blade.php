@extends('layouts.app')
@section('hideFooter', '1')

@section('content')
<div class="min-h-[100svh] bg-white dark:bg-gray-950 mt-5">
    <div class="mx-auto w-full max-w-md px-5 pb-8 pt-10 sm:px-6 sm:pt-12">
        <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 80)" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Security</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight text-gray-900 dark:text-white outfit">Forgot Password?</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Enter your email and we'll send you a link to reset your password.</p>

            @if (session('status'))
                <div class="mt-4 p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl">
                    <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <p class="mt-4 text-sm font-semibold text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-6">
                @csrf

                <div class="relative">
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus placeholder=" "
                        class="peer h-12 w-full rounded-2xl border border-gray-300 bg-white px-4 pt-5 text-sm font-medium text-gray-900 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <label for="email" class="pointer-events-none absolute left-4 top-2 px-1 text-xs font-semibold text-gray-500 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:bg-transparent peer-placeholder-shown:px-0 peer-placeholder-shown:text-sm peer-placeholder-shown:font-medium peer-focus:top-2 peer-focus:translate-y-0 peer-focus:bg-white peer-focus:px-1 peer-focus:text-xs peer-focus:font-semibold peer-focus:text-emerald-600 dark:bg-gray-900 dark:text-gray-400 dark:peer-placeholder-shown:bg-transparent dark:peer-focus:bg-gray-900 dark:peer-focus:text-emerald-400">
                        Email Address
                    </label>
                </div>

                <div class="space-y-3">
                    <button type="submit" class="h-12 w-full rounded-2xl bg-emerald-500 text-base font-black text-white transition hover:bg-emerald-400">
                        Send Reset Link
                    </button>
                    
                    <a href="{{ route('login') }}" class="flex h-12 w-full items-center justify-center rounded-2xl border border-gray-200 text-sm font-bold text-gray-500 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/50">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
