@extends('layouts.app')

@section('content')
    <div class="min-h-[80vh] flex items-center justify-center py-16 px-4 relative">
        <!-- Decorative Removed -->

        <div class="w-full max-w-2xl relative z-10" x-data="orderTracker()" x-init="init()">
            <div x-show="show" x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                class="bg-white rounded-[2rem] border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-white p-10 text-center relative overflow-hidden border-b border-gray-100">
                    <div class="w-48 h-48 mx-auto flex items-center justify-center mb-6">
                        <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
                        
                        <template x-if="status === 'delivered'">
                            <dotlottie-player src="https://lottie.host/85d585d8-d8e3-42e2-a0d3-e11c4a9a5873/KWsF3iit2h.lottie" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
                        </template>
                        <template x-if="status === 'out_for_delivery'">
                            <dotlottie-player src="https://lottie.host/85d585d8-d8e3-42e2-a0d3-e11c4a9a5873/KWsF3iit2h.lottie" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
                        </template>
                        <template x-if="status === 'preparing'">
                            <dotlottie-player src="https://lottie.host/85d585d8-d8e3-42e2-a0d3-e11c4a9a5873/KWsF3iit2h.lottie" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
                        </template>
                        <template x-if="status === 'pending'">
                            <dotlottie-player src="https://lottie.host/5430184a-92a7-4f61-b780-f110f622a177/aNsiEeX1u1.lottie" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
                        </template>
                        <template x-if="status === 'accepted'">
                            <dotlottie-player src="https://lottie.host/86910dac-1261-419e-812f-6d1678c84d12/wQYReYWYmG.lottie" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
                        </template>
                        <template x-if="['rejected', 'cancelled'].includes(status)">
                            <dotlottie-player src="https://lottie.host/9a7502eb-f874-4dcf-83e3-65683e955ea1/9tY7vTd9gR.lottie" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
                        </template>
                    </div>
                    <h2 class="text-3xl font-black outfit text-gray-900 tracking-tight" 
                        x-text="status === 'delivered' ? 'Food Delivered! ' : 
                               (status === 'out_for_delivery' ? 'Your order is on its way!' : 
                               (status === 'preparing' ? 'Preparing your food...' : 
                               (status === 'accepted' ? 'Order Accepted!' :
                               (['rejected', 'cancelled'].includes(status) ? 'Order Cancelled' : 'Order Placed! 🎉'))))">
                        Order Placed!
                    </h2>
                    <p class="text-gray-500 font-medium mt-2 text-lg"
                        x-text="status === 'delivered' ? 'Enjoy your meal!' : 
                              (status === 'out_for_delivery' ? 'Your rider is nearby and will arrive shortly!' : 
                              (status === 'preparing' ? 'The kitchen is putting the final touches on your food!' : 
                              (status === 'accepted' ? 'The restaurant has accepted your order.' :
                              (['rejected', 'cancelled'].includes(status) ? 'We are sorry for the inconvenience.' : 
                              (status === 'pending' || status === 'placed' ? 'Waiting for the restaurant to accpet your order...' : 'Sit back and relax while we handle the rest!')))))">
                        Sit back and relax while we handle the rest!
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
                            <span
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full font-bold text-sm transition-all duration-500"
                                :class="{
                                      'bg-amber-100 text-amber-700': status === 'pending',
                                      'bg-blue-100 text-blue-700': status === 'accepted',
                                      'bg-indigo-100 text-indigo-700': status === 'preparing',
                                      'bg-teal-100 text-teal-700': status === 'out_for_delivery',
                                      'bg-emerald-100 text-emerald-700': status === 'delivered',
                                      'bg-red-100 text-red-700': status === 'rejected' || status === 'cancelled'
                                  }">
                                <span class="w-2 h-2 rounded-full" :class="{
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
                        class="mb-6 bg-emerald-50 border border-emerald-100 rounded-2xl p-4 flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center text-white flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-0.5">Est. Prep
                                Time</p>
                            <p class="text-xl font-black outfit text-gray-900"><span x-text="estimatedPrepTime"></span>
                                Minutes</p>
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
                                    ['key' => 'pending', 'label' => 'Placed', 'icon' => 'M5 13l4 4L19 7'],
                                    ['key' => 'accepted', 'label' => 'Accepted', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    ['key' => 'preparing', 'label' => 'Preparing', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                                    ['key' => 'out_for_delivery', 'label' => 'On the Way', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                                    ['key' => 'delivered', 'label' => 'Delivered', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                                ];
                                $allKeys = array_column($steps, 'key');
                                $currentIndex = array_search($order->status, $allKeys);
                                if ($currentIndex === false)
                                    $currentIndex = 0;
                            @endphp

                            @foreach($steps as $i => $step)
                                @php $done = $i <= $currentIndex;
                                $active = $i === $currentIndex; @endphp
                                <div class="relative z-10 flex flex-col items-center gap-2 flex-1"
                                    :class="{ 'opacity-40': !isStepDone('{{ $step['key'] }}') }">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-500"
                                        :class="isStepActive('{{ $step['key'] }}') 
                                            ? 'bg-emerald-500 text-white scale-110 ring-4 ring-emerald-100' 
                                            : (isStepDone('{{ $step['key'] }}') ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-300')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="{{ $step['icon'] }}"></path>
                                        </svg>
                                    </div>
                                    <span
                                        class="text-[9px] font-black uppercase tracking-wide text-center leading-tight transition-colors duration-300"
                                        :class="isStepDone('{{ $step['key'] }}') ? 'text-emerald-600' : 'text-gray-300'">
                                        {{ $step['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Restaurant -->
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-500 font-bold outfit">
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
                            <div
                                class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-7 h-7 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center font-bold text-xs">{{ $item->quantity }}×</span>
                                    <span class="font-medium text-gray-800">
                                        {{ $item->name }}
                                        @if($item->variant_label)
                                            <span class="text-gray-400 font-medium text-sm">({{ $item->variant_label }})</span>
                                        @endif
                                    </span>
                                </div>
                                <span
                                    class="font-bold text-gray-900">${{ number_format($item->price * $item->quantity, 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Total -->
                    @php
                        $lbpRate = 89000;
                        $totalLbp = (float) $order->total * $lbpRate;
                    @endphp
                    <div class="border-t-2 border-dashed border-gray-200 pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-lg font-black outfit text-gray-900">Total Paid</span>
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">1 USD = 89,000
                                    LBP</p>
                            </div>
                            <div class="text-right">
                                <span
                                    class="block text-2xl font-black outfit text-emerald-500">${{ number_format($order->total, 2) }}</span>
                                <span class="block text-sm font-black text-gray-500">LBP
                                    {{ number_format($totalLbp, 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Info -->
                    <div class="bg-gray-50 rounded-2xl p-4 space-y-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">{{ $order->delivery_address }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">{{ $order->phone }}</span>
                        </div>
                        @if($order->notes)
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                                    </path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">{{ $order->notes }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- CTA -->
                    <div class="mt-8 text-center">
                        <a href="{{ route('home') }}"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-gray-900 hover:bg-emerald-500 text-white font-bold rounded-2xl transition-all transform hover:-translate-y-0.5 active:scale-95 text-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
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

                    // Initial attempt
                    this.usingEcho = this.subscribeToRealtime();

                    // Wait for connection if it failed initially
                    window.addEventListener('realtime:connected', () => {
                        if (!this.usingEcho) {
                            this.usingEcho = this.subscribeToRealtime();
                        }
                    });

                    if (this.terminalStatuses.includes(this.status)) {
                        return;
                    }

                    // Fallback monitoring
                    window.waitForRealtimeConnection?.(2500).then((connected) => {
                        if (!connected && !this.usingEcho && !this.terminalStatuses.includes(this.status)) {
                            this.startPolling();
                        }
                    });
                },

                subscribeToRealtime() {
                    if (!window.Echo) {
                        return false;
                    }

                    try {
                        window.Echo.private('order.{{ $order->id }}')
                            .listen('.order.updated', (payload) => {
                                this.handleRealtimeUpdate(payload);
                            })
                            .subscribed(() => {
                                // Successfully joined channel
                            })
                            .error((error) => {
                                // Auth failed
                            });

                        return true;
                    } catch (error) {
                        return false;
                    }
                },

                handleRealtimeUpdate(payload) {
                    const order = payload?.order;

                    if (!order?.id) {
                        return;
                    }

                    const prevStatus = this.status;
                    this.status = order.status;
                    this.estimatedPrepTime = order.estimated_prep_time;

                    // Send browser notification for status change
                    if (prevStatus !== order.status && window.sendOrderNotification) {
                        window.sendOrderNotification(order.id, order.status, payload.message);
                    }

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
                    // ONLY stop polling if we are actually SUBSCRIBED to the channel, 
                    // not just if the socket is "connected".
                    const channel = window.Echo?.connector?.channels['private-order.{{ $order->id }}'];
                    const isSubscribed = channel && channel.subscribed;

                    if (isSubscribed) {
                        this.usingEcho = true;
                        this.stopPolling();
                        return;
                    }

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
                        // Silently handle poll failures
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