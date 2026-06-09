@foreach($orders as $order)
<div x-data="{ openDetails: false }"
     class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden"
     data-order-id="{{ $order->id }}"
     data-order-status="{{ $order->status }}">
    
    <div @click="openDetails = !openDetails" class="p-6 flex items-center justify-between cursor-pointer transition-colors select-none group">
        <div class="flex items-center gap-4 border-b-0">
            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 transition-colors">
                @if($order->restaurant->logo)
                    <img src="{{ Storage::url($order->restaurant->logo) }}" class="w-full h-full object-cover">
                @else
                    <span class="text-lg font-black text-emerald-500">{{ substr($order->restaurant->name, 0, 1) }}</span>
                @endif
            </div>
            <div>
                <h3 class="font-extrabold text-gray-900 transition-colors">{{ $order->restaurant->name }}</h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-tighter">{{ $order->created_at->format('M d, Y • h:i A') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <div class="font-black text-gray-900 leading-tight">{{ number_format($order->total, 2) }} LBP</div>
                @if($order->discount_amount > 0)
                    <div class="text-[10px] font-bold text-emerald-500 uppercase">Saved {{ number_format($order->discount_amount, 2) }} LBP</div>
                @endif
                <span class="inline-block mt-1 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest status-badge-{{ $order->id }}"
                      :class="getStatusClass(getStatus({{ $order->id }}, '{{ $order->status }}'))">
                    <span x-text="formatStatus(getStatus({{ $order->id }}, '{{ $order->status }}'))">{{ $order->status }}</span>
                </span>
            </div>
            <div class="w-8 h-8 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 transition-all duration-300" 
                 :class="openDetails ? 'rotate-180 bg-emerald-50 text-emerald-600 border-emerald-100' : ''">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>

    <div x-show="openDetails"
         x-transition:enter="transition ease-out duration-300 origin-top"
         x-transition:enter-start="opacity-0 scale-y-95 -translate-y-2"
         x-transition:enter-end="opacity-100 scale-y-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 origin-top"
         x-transition:leave-start="opacity-100 scale-y-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-y-95 -translate-y-2"
         style="display: none;"
         class="border-t border-gray-50 dark:border-gray-700">

        <div x-data="{ openTracker: false }"
             x-show="isLiveStatus(getStatus({{ $order->id }}, '{{ $order->status }}'))"
             class="border-b border-emerald-50 dark:border-emerald-900/30 overflow-hidden">
            <button @click="openTracker = !openTracker" class="w-full px-6 py-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:bg-gray-900 transition-all flex items-center justify-between group cursor-pointer focus:outline-none">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-300">Live Order Tracking</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-300 uppercase tracking-widest transition-opacity" :class="openTracker ? 'opacity-0' : 'opacity-100'">View Progress</span>
                    <div class="w-6 h-6 rounded-full bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm border border-emerald-100/50 dark:border-emerald-700/50 text-emerald-500 dark:text-emerald-300 transition-transform duration-300" :class="openTracker ? 'rotate-180 bg-emerald-100 dark:bg-emerald-900/50' : ''">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </button>

            <div x-show="openTracker" 
                 x-transition:enter="transition ease-out duration-300 transform origin-top"
                 x-transition:enter-start="opacity-0 scale-y-95 -translate-y-2"
                 x-transition:enter-end="opacity-100 scale-y-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform origin-top"
                 x-transition:leave-start="opacity-100 scale-y-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-y-95 -translate-y-2"
                 class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-gray-800 dark:to-gray-800 border-t border-emerald-100/30 dark:border-emerald-900/40">
                <div class="px-6 pt-5 pb-6">
                    @php
                        $steps = ['pending'=>'Placed','accepted'=>'Accepted','preparing'=>'Preparing','out_for_delivery'=>'On the Way','delivered'=>'Delivered'];
                        $stepKeys = array_keys($steps);
                    @endphp
                    <div class="relative flex items-center justify-between">
                        <div class="absolute left-0 right-0 top-3.5 h-0.5 bg-emerald-100 dark:bg-emerald-900/50 z-0"></div>
                        @foreach($steps as $key => $label)
                        @php $i = array_search($key, $stepKeys); @endphp
                        <div class="relative z-10 flex flex-col items-center gap-1.5 flex-1">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center transition-all duration-500 text-[10px] font-black"
                                 :class="isStepDone({{ $i }}, getStatus({{ $order->id }}, '{{ $order->status }}'))
                                    ? (isStepActive({{ $i }}, getStatus({{ $order->id }}, '{{ $order->status }}'))
                                        ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/40 ring-2 ring-emerald-100 scale-110'
                                        : 'bg-emerald-500 text-white')
                                    : 'bg-white dark:bg-gray-700 text-gray-300 dark:text-gray-400 border border-gray-200 dark:border-gray-600'">
                                {{ $i + 1 }}
                            </div>
                            <span class="text-[8px] font-black uppercase tracking-wide text-center leading-tight transition-colors duration-300"
                                  :class="isStepDone({{ $i }}, getStatus({{ $order->id }}, '{{ $order->status }}')) ? 'text-emerald-700 dark:text-emerald-300 font-bold' : 'text-gray-400 dark:text-gray-300'">
                                {{ $label }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gray-50/50 dark:bg-gray-900">
            <p class="text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest mb-3">Items Ordered</p>
            <div class="space-y-2">
                @foreach($order->orderItems as $item)
                <div class="flex justify-between items-center bg-white dark:bg-gray-800 p-2 rounded-xl mb-2 shadow-sm dark:shadow-none border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center overflow-hidden border border-gray-100 dark:border-gray-600 flex-shrink-0">
                            @if($item->menuItem && $item->menuItem->image)
                                <img src="{{ Storage::url($item->menuItem->image) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-emerald-50 text-emerald-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100 line-clamp-1">
                                {{ $item->name }}
                                @if($item->variant_label)
                                    <span class="text-gray-400 dark:text-gray-300 font-medium text-xs">({{ $item->variant_label }})</span>
                                @endif
                            </span>
                            <span class="text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest">{{ $item->quantity }}x @ ${{ number_format($item->price, 2) }}</span>
                        </div>
                    </div>
                    <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">${{ number_format($item->price * $item->quantity, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endforeach
