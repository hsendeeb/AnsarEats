@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-16 px-4 relative">
    <!-- Decorative -->
    <div class="absolute top-20 right-20 w-24 h-24 bg-emerald-200 rounded-full opacity-25 animate-bounce" style="animation-duration: 4s;"></div>
    <div class="absolute bottom-20 left-20 w-16 h-16 bg-teal-200 rounded-xl opacity-20 animate-bounce" style="animation-duration: 6s;"></div>
    <div class="absolute top-1/3 left-1/4 w-10 h-10 bg-emerald-300 rounded-full opacity-15 animate-ping" style="animation-duration: 3s;"></div>

    <div class="w-full max-w-2xl relative z-10" 
         x-data="orderTracker()" 
         x-init="init()">
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="bg-white rounded-[2rem] shadow-2xl shadow-gray-200/60 border border-gray-100 overflow-hidden"
        >
            <!-- Header -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-500 p-10 text-center relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full"></div>
                <div class="absolute -bottom-8 -left-8 w-36 h-36 bg-white/10 rounded-full"></div>
                
                <div class="w-24 h-24 mx-auto bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-6 animate-bounce" style="animation-duration: 2s;">
                    <template x-if="status === 'delivered'">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </template>
                    <template x-if="status === 'out_for_delivery'">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </template>
                    <template x-if="status === 'preparing'">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </template>
                    <template x-if="['pending', 'accepted'].includes(status)">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </template>
                    <template x-if="['rejected', 'cancelled'].includes(status)">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </template>
                </div>
                <h2 class="text-3xl font-black outfit text-white tracking-tight" 
                    x-text="status === 'delivered' ? 'Food Delivered! 😋' : 
                           (status === 'out_for_delivery' ? 'On the Way! 🚀' : 
                           (status === 'preparing' ? 'Cooking... 🥘' : 
                           (['rejected', 'cancelled'].includes(status) ? 'Order Cancelled' : 'Order Placed! 🎉')))">
                    Order Placed! 🎉
                </h2>
                <p class="text-emerald-100 font-medium mt-2 text-lg" 
                   x-text="status === 'delivered' ? 'Enjoy your meal!' : 
                          (status === 'out_for_delivery' ? 'Your rider is nearby!' : 
                          (status === 'preparing' ? 'The kitchen is busy!' : 
                          (['rejected', 'cancelled'].includes(status) ? 'We are sorry for the inconvenience.' : 'Your food is on its way')))">
                    Your food is on its way
                </p>
            </div>

            <!-- Order Details -->
            <div class="p-8">
                <!-- Order Info -->
                <div class="flex items-center justify-between bg-gray-50 rounded-2xl p-4 mb-6">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Order #</p>
                        <p class="text-2xl font-black outfit text-gray-900">{{ $order->id }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status</p>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full font-bold text-sm transition-all duration-500"
                              :class="{
                                  'bg-amber-100 text-amber-700': status === 'pending',
                                  'bg-blue-100 text-blue-700': status === 'accepted',
                                  'bg-indigo-100 text-indigo-700': status === 'preparing',
                                  'bg-teal-100 text-teal-700': status === 'out_for_delivery',
                                  'bg-emerald-100 text-emerald-700': status === 'delivered',
                                  'bg-red-100 text-red-700': status === 'rejected' || status === 'cancelled'
                              }">
                            <span class="w-2 h-2 rounded-full animate-pulse"
                                  :class="{
                                      'bg-amber-500': status === 'pending',
                                      'bg-blue-500': status === 'accepted',
                                      'bg-indigo-500': status === 'preparing',
                                      'bg-teal-500': status === 'out_for_delivery',
                                      'bg-emerald-500': status === 'delivered',
                                      'bg-red-500': status === 'rejected' || status === 'cancelled'
                                  }"></span>
                            <span x-text="statusLabel()">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        </span>
                    </div>
                </div>

                <div x-show="estimatedPrepTime && ['accepted', 'preparing', 'out_for_delivery'].includes(status)"
                     x-cloak
                     class="mb-6 bg-emerald-50 border border-emerald-100 rounded-2xl p-4 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-0.5">Est. Prep Time</p>
                        <p class="text-xl font-black outfit text-gray-900"><span x-text="estimatedPrepTime"></span> Minutes</p>
                    </div>
                </div>

                <!-- Live Order Progress Stepper -->
                <div class="mb-8">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Order Progress</p>
                    <div class="relative flex items-center justify-between">
                        <!-- Connector line -->
                        <div class="absolute left-0 right-0 top-5 h-0.5 bg-gray-100 z-0"></div>

                        @php
                            $steps = [
                                ['key' => 'pending',          'label' => 'Placed',      'icon' => 'M5 13l4 4L19 7'],
                                ['key' => 'accepted',         'label' => 'Accepted',    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['key' => 'preparing',        'label' => 'Preparing',   'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                                ['key' => 'out_for_delivery', 'label' => 'On the Way',  'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                                ['key' => 'delivered',        'label' => 'Delivered',   'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                            ];
                            $allKeys = array_column($steps, 'key');
                            $currentIndex = array_search($order->status, $allKeys);
                            if ($currentIndex === false) $currentIndex = 0;
                        @endphp

                        @foreach($steps as $i => $step)
                        @php $done = $i <= $currentIndex; $active = $i === $currentIndex; @endphp
                        <div class="relative z-10 flex flex-col items-center gap-2 flex-1"
                             :class="{ 'opacity-40': !isStepDone('{{ $step['key'] }}') }">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-500 shadow-sm"
                                 :class="isStepActive('{{ $step['key'] }}') 
                                    ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/40 scale-110 ring-4 ring-emerald-100' 
                                    : (isStepDone('{{ $step['key'] }}') ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-300')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $step['icon'] }}"></path>
                                </svg>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-wide text-center leading-tight transition-colors duration-300"
                                  :class="isStepDone('{{ $step['key'] }}') ? 'text-emerald-600' : 'text-gray-300'">
                                {{ $step['label'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Restaurant -->
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-500 font-bold outfit">
                        {{ substr($order->restaurant->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">{{ $order->restaurant->name }}</p>
                        <p class="text-sm text-gray-500 font-medium">{{ $order->restaurant->address }}</p>
                    </div>
                </div>

                <!-- Items -->
                <div class="space-y-3 mb-6">
                    @foreach($order->orderItems as $item)
                        <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center font-bold text-xs">{{ $item->quantity }}×</span>
                                <span class="font-medium text-gray-800">{{ $item->name }}</span>
                            </div>
                            <span class="font-bold text-gray-900">${{ number_format($item->price * $item->quantity, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Total -->
                <div class="border-t-2 border-dashed border-gray-200 pt-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-black outfit text-gray-900">Total Paid</span>
                        <span class="text-2xl font-black outfit text-emerald-500">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>

                <!-- Delivery Info -->
                <div class="bg-gray-50 rounded-2xl p-4 space-y-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="text-sm font-medium text-gray-700">{{ $order->delivery_address }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        <span class="text-sm font-medium text-gray-700">{{ $order->phone }}</span>
                    </div>
                    @if($order->notes)
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                            <span class="text-sm font-medium text-gray-700">{{ $order->notes }}</span>
                        </div>
                    @endif
                </div>

                <!-- CTA -->
                <div class="mt-8 text-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gray-900 hover:bg-emerald-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl hover:shadow-emerald-500/30 transition-all transform hover:-translate-y-0.5 active:scale-95 text-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function orderTracker() {
    const ORDER_STEPS = ['pending', 'accepted', 'preparing', 'out_for_delivery', 'delivered'];

    return {
        show: false,
        status: '{{ $order->status }}',
        estimatedPrepTime: @js($order->estimated_prep_time),
        usingEcho: false,
        pollInterval: null,
        terminalStatuses: ['delivered', 'cancelled'],

        init() {
            setTimeout(() => this.show = true, 100);
            this.usingEcho = this.subscribeToRealtime();

            if (!this.usingEcho && !this.terminalStatuses.includes(this.status)) {
                this.startPolling();
            }
        },

        subscribeToRealtime() {
            if (!window.Echo) {
                return false;
            }

            try {
                window.Echo.private('order.{{ $order->id }}')
                    .listen('.order.updated', (payload) => this.handleRealtimeUpdate(payload));

                return true;
            } catch (error) {
                console.warn('Realtime order tracking unavailable, falling back to polling.', error);
                return false;
            }
        },

        handleRealtimeUpdate(payload) {
            const order = payload?.order;

            if (!order?.id) {
                return;
            }

            this.status = order.status;
            this.estimatedPrepTime = order.estimated_prep_time;

            if (this.terminalStatuses.includes(order.status)) {
                this.stopPolling();
            }
        },

        startPolling() {
            this.pollInterval = setInterval(() => this.fetchStatus(), 5000);
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        },

        async fetchStatus() {
            try {
                const res = await fetch('{{ route("order.status", $order->id) }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) { this.stopPolling(); return; }

                const data = await res.json();
                const newStatus = data.status;

                if (newStatus !== this.status) {
                    this.status = newStatus;
                }

                this.estimatedPrepTime = data.estimated_prep_time;

                if (this.terminalStatuses.includes(newStatus)) {
                    this.stopPolling();
                }
            } catch (e) {
                console.warn('Status poll failed:', e);
            }
        },

        isStepDone(stepKey) {
            const currentIdx = ORDER_STEPS.indexOf(this.status);
            const stepIdx = ORDER_STEPS.indexOf(stepKey);
            return stepIdx <= currentIdx;
        },

        isStepActive(stepKey) {
            return this.status === stepKey;
        },

        statusLabel() {
            return this.status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        },

        destroy() {
            this.stopPolling();
            window.Echo?.leaveChannel('private-order.{{ $order->id }}');
        }
    };
}
</script>
@endpush
