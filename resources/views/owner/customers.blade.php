@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('owner.dashboard') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-emerald-600 font-bold transition-colors bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>

        <div class="relative mb-8 overflow-hidden rounded-[2.5rem] border border-gray-200 bg-white p-8 text-slate-900 shadow-xl shadow-slate-200/60 md:p-10 dark:border-slate-800 dark:bg-slate-900 dark:text-white dark:shadow-slate-950/15">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(14,165,233,0.12),_transparent_30%),radial-gradient(circle_at_bottom_left,_rgba(6,182,212,0.1),_transparent_28%)] dark:bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.18),_transparent_30%),radial-gradient(circle_at_bottom_left,_rgba(14,165,233,0.16),_transparent_28%)]"></div>
            <div class="absolute -right-12 top-8 h-40 w-40 rounded-full border border-sky-200/60 bg-sky-400/10 blur-2xl dark:border-white/10"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-cyan-300/10 blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-6">
                              <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="group rounded-[2rem] border border-gray-200 bg-gray-50 p-5 shadow-sm transition-all hover:-translate-y-1 hover:bg-white dark:border-slate-700 dark:bg-slate-800 dark:shadow-xl dark:shadow-slate-950/20 dark:hover:bg-slate-800/90">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.3em] text-sky-600 dark:text-sky-200">Customers</p>
                                <p class="mt-3 text-4xl font-black text-gray-900 outfit dark:text-white">{{ $summary['total_customers'] }}</p>
                                <p class="mt-2 text-sm font-medium text-gray-500 dark:text-slate-300">Unique people who have ordered from your restaurant.</p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-500/15 text-sky-600 ring-1 ring-sky-400/20 dark:text-sky-200">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v-2a4 4 0 00-4-4H11a4 4 0 00-4 4v2m10 0H7m10-11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="group rounded-[2rem] border border-gray-200 bg-gray-50 p-5 shadow-sm transition-all hover:-translate-y-1 hover:bg-white dark:border-slate-700 dark:bg-slate-800 dark:shadow-xl dark:shadow-slate-950/20 dark:hover:bg-slate-800/90">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.3em] text-red-500 dark:text-rose-300">Blocked</p>
                                <p class="mt-3 text-4xl font-black text-gray-900 outfit dark:text-white">{{ $summary['blocked_customers'] }}</p>
                                <p class="mt-2 text-sm font-medium text-gray-500 dark:text-slate-300">Customers currently restricted from ordering.</p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-500/15 text-red-500 ring-1 ring-rose-400/20 dark:text-rose-300">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="group rounded-[2rem] border border-gray-200 bg-gray-50 p-5 shadow-sm transition-all hover:-translate-y-1 hover:bg-white dark:border-slate-700 dark:bg-slate-800 dark:shadow-xl dark:shadow-slate-950/20 dark:hover:bg-slate-800/90">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.3em] text-amber-600 dark:text-amber-300">Orders</p>
                                <p class="mt-3 text-4xl font-black text-gray-900 outfit dark:text-white">{{ $summary['total_customer_orders'] }}</p>
                                <p class="mt-2 text-sm font-medium text-gray-500 dark:text-slate-300">All orders placed by customers at this restaurant.</p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-600 ring-1 ring-amber-400/20 dark:text-amber-200">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-6 0a2 2 0 104 0m-4 0a2 2 0 114 0m-4 0h4"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-black outfit text-gray-900">Customer List</h2>
                    <p class="text-sm font-medium text-gray-500">Names, phone numbers, join dates, and lifetime order counts for this restaurant.</p>
                </div>
            </div>

            @if($customers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Customer</th>
                                <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Phone</th>
                                <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Joined</th>
                                <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Orders</th>
                                <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Status</th>
                                <th class="px-6 py-4 text-right text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($customers as $customer)
                                @php
                                    $isBlocked = (int) $customer->is_blocked > 0;
                                    $blockedAt = $customer->blocked_at ? \Carbon\Carbon::parse($customer->blocked_at) : null;
                                @endphp
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-sm font-black text-rose-600">
                                                {{ strtoupper(mb_substr($customer->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-gray-900">{{ $customer->name }}</p>
                                                <p class="text-xs font-medium text-gray-500">{{ $customer->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-sm font-bold text-gray-700">
                                        {{ $customer->restaurant_phone ?: 'No phone recorded yet' }}
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-sm font-bold text-gray-800">{{ $customer->created_at->format('M d, Y') }}</p>
                                        <p class="text-xs font-medium text-gray-500">{{ $customer->created_at->diffForHumans() }}</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-sm font-black text-indigo-600">
                                            {{ $customer->restaurant_orders_count }} {{ \Illuminate\Support\Str::plural('order', (int) $customer->restaurant_orders_count) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        @if($isBlocked)
                                            <div class="inline-flex flex-col gap-1 rounded-2xl bg-red-50 px-3 py-2 text-red-600">
                                                <span class="text-sm font-black">Blocked</span>
                                                <span class="text-[11px] font-medium">
                                                    {{ $blockedAt?->format('M d, Y') }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-sm font-black text-emerald-600">
                                                Active
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        @if($isBlocked)
                                            <form method="POST"
                                                  action="{{ route('owner.customers.unblock', $customer) }}"
                                                  class="inline-block"
                                                  onsubmit="return confirm('Unblock {{ addslashes($customer->name) }} and allow them to order from your restaurant again?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-4 py-2 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition-all hover:bg-emerald-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                    Unblock Customer
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('owner.customers.block', $customer) }}"
                                                  class="inline-block"
                                                  onsubmit="return confirm('Block {{ addslashes($customer->name) }} from ordering from your restaurant?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500 px-4 py-2 text-sm font-black text-white shadow-lg shadow-red-500/20 transition-all hover:bg-red-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728"></path></svg>
                                                    Block Customer
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-5 border-t border-gray-100">
                    {{ $customers->links() }}
                </div>
            @else
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-rose-100 text-rose-500">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5V4H2v16h5m10 0v-2a4 4 0 00-4-4H11a4 4 0 00-4 4v2m10 0H7m10-11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <h3 class="mt-6 text-2xl font-black outfit text-gray-900">No customers yet</h3>
                    <p class="mt-2 text-sm font-medium text-gray-500">Once someone orders from your restaurant, they will appear here automatically.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
