@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-16 px-4 relative">
    <!-- Decorative floating shapes -->
    <div class="absolute top-16 right-16 w-24 h-24 bg-teal-200 rounded-full opacity-30 animate-bounce" style="animation-duration: 4s;"></div>
    <div class="absolute bottom-32 left-16 w-16 h-16 bg-emerald-200 rounded-xl opacity-25 animate-bounce" style="animation-duration: 6s;"></div>
    <div class="absolute top-1/2 left-1/3 w-10 h-10 bg-teal-300 rounded-full opacity-20 animate-ping" style="animation-duration: 4s;"></div>

    <div class="w-full max-w-lg relative z-10" x-data="{ show: false, step: {{ $errors->has('password') || $errors->has('password_confirmation') ? 2 : 1 }} }" x-init="setTimeout(() => show = true, 100)">
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="bg-white rounded-[2rem] shadow-2xl shadow-gray-200/60 border border-gray-100 overflow-hidden"
        >
            <!-- Header -->
            <div class="bg-gradient-to-br from-teal-500 to-emerald-500 p-8 text-center relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full"></div>
                <div class="absolute -bottom-8 -left-8 w-36 h-36 bg-white/10 rounded-full"></div>
                <div class="absolute top-6 left-6 w-8 h-8 bg-white/20 rounded-lg rotate-12"></div>
                
                <div class="w-20 h-20 mx-auto bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4 transform hover:rotate-12 transition-transform cursor-pointer shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </div>
                <h2 class="text-3xl font-black outfit text-white tracking-tight">Join AnsarEats!</h2>
                <p class="text-emerald-100 font-medium mt-2">Create your account &amp; start selling</p>
            </div>

            <!-- Progress Steps -->
            <div class="px-8 pt-6">
                <div class="flex items-center justify-center gap-3">
                    <div class="flex items-center gap-2">
                        <div :class="step >= 1 ? 'bg-emerald-500 text-white scale-110' : 'bg-gray-200 text-gray-500'" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm transition-all shadow-sm">1</div>
                        <span class="text-xs font-bold" :class="step >= 1 ? 'text-emerald-600' : 'text-gray-400'">Account</span>
                    </div>
                    <div class="w-12 h-0.5 rounded-full transition-colors" :class="step >= 2 ? 'bg-emerald-500' : 'bg-gray-200'"></div>
                    <div class="flex items-center gap-2">
                        <div :class="step >= 2 ? 'bg-emerald-500 text-white scale-110' : 'bg-gray-200 text-gray-500'" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm transition-all shadow-sm">2</div>
                        <span class="text-xs font-bold" :class="step >= 2 ? 'text-emerald-600' : 'text-gray-400'">Secure</span>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-center gap-2 text-sm font-bold text-red-700">
                                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-3 mb-6">
                    <a href="{{ route('social.redirect', 'google') }}" class="relative flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3.5 text-[15px] font-semibold text-[#3c4043] shadow-sm transition-all hover:bg-[#f8f9fa] hover:shadow-md focus:outline-none focus:ring-4 focus:ring-gray-200/70">
                        <span class="absolute left-4 flex h-6 w-6 items-center justify-center" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="#4285F4" d="M21.6 12.23c0-.68-.06-1.34-.17-1.97H12v3.73h5.39a4.62 4.62 0 0 1-2 3.03v2.52h3.24c1.9-1.75 2.97-4.33 2.97-7.31Z"/>
                                <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.61-2.44l-3.24-2.52c-.9.6-2.05.95-3.37.95-2.59 0-4.79-1.75-5.58-4.1H3.07v2.6A9.99 9.99 0 0 0 12 22Z"/>
                                <path fill="#FBBC05" d="M6.42 13.89A5.99 5.99 0 0 1 6.11 12c0-.66.11-1.3.31-1.89V7.51H3.07A9.99 9.99 0 0 0 2 12c0 1.61.39 3.14 1.07 4.49l3.35-2.6Z"/>
                                <path fill="#EA4335" d="M12 6.01c1.47 0 2.79.5 3.82 1.48l2.87-2.87C16.95 2.98 14.69 2 12 2 8.09 2 4.72 4.24 3.07 7.51l3.35 2.6c.79-2.35 2.99-4.1 5.58-4.1Z"/>
                            </svg>
                        </span>
                        <span class="w-full text-center">Continue with Google</span>
                    </a>

                    <a href="{{ route('social.redirect', 'facebook') }}" class="flex w-full items-center justify-center gap-3 rounded-2xl border-2 border-gray-200 bg-[#1877F2] px-4 py-3 font-bold text-dark transition-all hover:bg-[#1669d9]">
                        <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M24 12.1C24 5.4 18.6 0 12 0S0 5.4 0 12.1c0 6 4.4 11 10.1 11.9v-8.4H7.1v-3.5h3V9.4c0-3 1.8-4.7 4.5-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9v2.3h3.4l-.5 3.5h-2.9V24C19.6 23.1 24 18.1 24 12.1Z"/>
                        </svg>
                        Continue with Facebook
                    </a>
                </div>

                <div class="mb-6 relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="bg-white px-4 font-bold text-gray-500">or create an account with email</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <!-- Step 1: Account Info -->
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="space-y-5">
                            <div class="group">
                                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                                        class="block w-full pl-12 pr-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                        placeholder="Ahmad El-Khatib">
                                </div>
                            </div>

                            <div class="group">
                                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                                    </div>
                                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                        class="block w-full pl-12 pr-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                        placeholder="your@email.com">
                                </div>
                            </div>

                            <button type="button" @click="step = 2" class="w-full py-4 bg-gray-900 hover:bg-emerald-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl hover:shadow-emerald-500/30 transition-all transform hover:-translate-y-0.5 active:scale-[0.98] text-lg flex items-center justify-center gap-2">
                                Continue
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Password -->
                    <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-cloak>
                        <div class="space-y-5">
                            <div class="group" x-data="{ showPassword: false }">
                                <label for="password" class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </div>
                                    <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                                        class="block w-full pl-12 pr-12 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                        placeholder="Min 8 characters">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-emerald-500">
                                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="group">
                                <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">Confirm Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    </div>
                                    <input id="password_confirmation" name="password_confirmation" type="password" required
                                        class="block w-full pl-12 pr-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-2xl font-medium placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                        placeholder="Repeat your password">
                                </div>
                                @error('password_confirmation')
                                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex gap-3">
                                <button type="button" @click="step = 1" class="py-4 px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-2xl transition-all active:scale-[0.98]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>
                                </button>
                                <button type="submit" class="flex-1 py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl hover:shadow-emerald-500/30 transition-all transform hover:-translate-y-0.5 active:scale-[0.98] text-lg flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Create Account
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="mt-8 text-center">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                        <div class="relative flex justify-center text-sm"><span class="px-4 bg-white text-gray-500 font-bold">Already a member?</span></div>
                    </div>
                    <a href="{{ route('login') }}" class="inline-block mt-4 font-bold text-emerald-600 hover:text-emerald-500 transition-colors group">
                        Sign in to your account
                        <span class="inline-block group-hover:translate-x-1 transition-transform">→</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
