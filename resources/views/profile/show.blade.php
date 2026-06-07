@extends('layouts.app')

@section('skeleton')
<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-12">
        <div class="w-32 h-6 bg-gray-200 dark:bg-gray-800 rounded-lg mb-6 animate-pulse"></div>
        <div class="w-48 h-10 bg-gray-200 dark:bg-gray-800 rounded-xl mb-2 animate-pulse"></div>
        <div class="w-64 h-5 bg-gray-200 dark:bg-gray-800 rounded-lg animate-pulse"></div>
    </div>
    <div class="max-w-2xl mx-auto bg-gray-100 dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 overflow-hidden h-[500px] animate-pulse">
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
        <h1 class="text-2xl md:text-4xl font-extrabold text-gray-900 outfit tracking-tight mb-2">My Profile</h1>
        <p class="text-gray-500 font-medium">Manage your personal information.</p>
    </div>

    <!-- Form Section -->
    <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-xs font-bold text-gray-700 mb-2">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900">
                                @error('name') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900">
                                @error('email') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-xs font-bold text-gray-700 mb-2">Phone Number</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $deliveryDefaults['phone']) }}"
                                    class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl transition-all outline-none font-bold text-gray-900"
                                    placeholder="+961 71 123 456">
                                @error('phone') <p class="mt-1 text-sm text-red-500 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="pt-4 flex flex-col gap-3 md:flex-row md:items-center">
                            <button type="submit" class="w-full md:w-auto px-10 py-4 bg-emerald-500 text-white font-extrabold rounded-2xl hover:bg-emerald-400 hover:-translate-y-1 transition-all active:scale-95">
                                Save Changes
                            </button>
                            <a href="{{ route('profile.password') }}" class="w-full md:w-auto inline-flex items-center justify-center gap-3 px-6 py-4 rounded-2xl border border-gray-200 bg-gray-50 text-gray-700 font-bold hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V9a5 5 0 00-10 0v2H6a2 2 0 00-2 2v6a2 2 0 002 2zm3-10V9a3 3 0 016 0v2H9z"></path>
                                </svg>
                                <span>Change Password</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

</div>
@endsection
