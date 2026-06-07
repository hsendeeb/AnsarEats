@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 dark:bg-gray-900" x-data="{
    modalOpen: false,
    modalTitle: '',
    modalText: '',
    formAction: '',
    formMethod: '',
    isBlock: true,
    isSubmitting: false,
    
    openBlockModal(name, action) {
        this.isBlock = true;
        this.modalTitle = 'Block Customer';
        this.modalText = `Are you sure you want to block ${name} from ordering from your restaurant?`;
        this.formAction = action;
        this.formMethod = 'POST';
        this.modalOpen = true;
        this.isSubmitting = false;
    },
    
    openUnblockModal(name, action) {
        this.isBlock = false;
        this.modalTitle = 'Unblock Customer';
        this.modalText = `Are you sure you want to unblock ${name} and allow them to order from your restaurant again?`;
        this.formAction = action;
        this.formMethod = 'DELETE';
        this.modalOpen = true;
        this.isSubmitting = false;
    }
}">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('owner.dashboard') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-emerald-600 font-bold transition-colors bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
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
                                <x-heroicon-o-user-circle class="w-7 h-7" />
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
                                <x-heroicon-o-x-circle class="w-7 h-7" />
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
                                <x-heroicon-o-clipboard-document-list class="w-7 h-7" />
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
                                            <button type="button" @click="openUnblockModal('{{ addslashes($customer->name) }}', '{{ route('owner.customers.unblock', $customer) }}')" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-4 py-2 text-sm font-black text-white transition-all hover:bg-emerald-400">
                                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                                Unblock Customer
                                            </button>
                                        @else
                                            <button type="button" @click="openBlockModal('{{ addslashes($customer->name) }}', '{{ route('owner.customers.block', $customer) }}')" class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 cursor-pointer text-2xl font-black text-red-500 transition-all hover:text-red-900">
                                                <x-heroicon-o-x-circle class="w-4 h-4" />
                                            </button>
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
                        <x-heroicon-o-user-circle class="w-10 h-10" />
                    </div>
                    <h3 class="mt-6 text-2xl font-black outfit text-gray-900">No customers yet</h3>
                    <p class="mt-2 text-sm font-medium text-gray-500">Once someone orders from your restaurant, they will appear here automatically.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- The Block/Unblock Modal -->
    <div x-show="modalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[12000] flex items-center justify-center p-4 bg-gray-950/50 backdrop-blur-sm">
        <div @click.outside="modalOpen = false"
             x-show="modalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center">
            
            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4" :class="isBlock ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600'">
                <svg x-show="isBlock" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <svg x-show="!isBlock" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="modalTitle"></h3>
            <p class="text-gray-500 mb-8 text-sm" x-text="modalText"></p>

            <div class="flex items-center justify-center gap-3">
                <button @click="modalOpen = false" type="button" :disabled="isSubmitting" class="flex-1 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all text-sm disabled:opacity-50">
                    Cancel
                </button>
                <form method="POST" :action="formAction" class="flex-1" @submit="isSubmitting = true; window.showPageLoader?.()">
                    @csrf
                    <input type="hidden" name="_method" :value="formMethod">
                    <button type="submit" :disabled="isSubmitting" class="w-full px-4 py-2.5 text-white font-bold rounded-xl transition-all text-sm flex items-center justify-center gap-2 disabled:opacity-50" :class="isBlock ? 'bg-red-600 hover:bg-red-500' : 'bg-emerald-600 hover:bg-emerald-500'">
                        <span x-text="isBlock ? 'Confirm Block' : 'Confirm Unblock'"></span>
                        <span x-show="isSubmitting" x-cloak class="inline-flex">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
