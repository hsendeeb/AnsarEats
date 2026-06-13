@php
    $drawerLocations = auth()->check() ? auth()->user()->locations : collect();
    $drawerDefaultLocation = $drawerLocations->where('is_default', true)->first() ?? $drawerLocations->first();
@endphp

<div
    x-data="deliveryLocationDrawer"
    @toggle-location-drawer.window="open = !open"
    @keydown.escape.window="open = false"
    x-effect="document.documentElement.classList.toggle('overflow-hidden', open); document.body.classList.toggle('overflow-hidden', open);"
    class="md:hidden"
>
    <div
        x-show="open"
        x-transition:enter="transition-opacity ease-out duration-300 motion-reduce:transition-none"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200 motion-reduce:transition-none"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-[10000] bg-black/40"
        x-cloak
        aria-hidden="true"
    ></div>

    <section
        x-show="open"
        x-transition:enter="transform-gpu transition duration-[420ms] ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
        x-transition:enter-start="translate-y-full opacity-95"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transform-gpu transition duration-250 ease-in motion-reduce:transition-none"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-95"
        class="fixed inset-x-0 bottom-0 z-[10001] max-h-[58vh] overflow-y-auto rounded-t-[32px] border-0 bg-white px-2 pb-[calc(env(safe-area-inset-bottom)+2rem)] pt-4 shadow-[0_-24px_60px_rgba(15,23,42,0.18)] dark:bg-white"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delivery-location-drawer-title"
        x-cloak
    >
        <div class="mx-auto h-1.5 w-16 rounded-full bg-gray-200"></div>

        <h2 id="delivery-location-drawer-title" class="mt-4 text-[26px] font-bold leading-tight tracking-normal text-[#111827]">
            Where should we deliver to?
        </h2>

        <div class="mt-8 py-2 shadow-sm rounded-xl overflow-hidden border border-slate-100 bg-white">
                

            <a
                href="{{ auth()->check() ? route('profile.locations') : route('login') }}"
                class="flex min-h-[76px] w-full cursor-pointer items-center gap-4 px-5  text-left transition-colors duration-200 hover:bg-slate-50 active:bg-slate-100 focus:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center text-gray-950">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-[18px] font-semibold leading-6 text-gray-950">Add new address</span>
                    <span class="mt-0.5 block text-[15px] font-medium leading-5 text-gray-400">Choose location on map</span>
                </span>
                <svg class="h-7 w-7 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2.25" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>

        <h3 class="mt-8 text-[24px] font-bold leading-tight tracking-normal text-gray-950">
            Saved addresses
        </h3>

        <div class="mt-5 mb-5 space-y-3">
            @auth
                @forelse($drawerLocations as $loc)
                    <div
                        x-data="{ menuOpen: false }"
                        @click.outside="menuOpen = false"
                        class="relative flex min-h-[54px] items-center gap-3 rounded-2xl border {{ $drawerDefaultLocation && $drawerDefaultLocation->id === $loc->id ? 'border-emerald-500' : 'border-slate-100' }} bg-white p-3 shadow-sm transition-colors duration-200 hover:bg-emerald-50/30"
                    >
                        <form action="{{ route('profile.locations.default', $loc) }}" method="POST" class="min-w-0 flex-1">
                            @csrf
                            <button type="submit" class="flex min-h-[44px] w-full cursor-pointer items-center gap-4 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-[#00B388]/40">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl  text-[#00B388]">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1 overflow-hidden">
                                    <span class="block truncate text-[18px] font-semibold leading-6 text-gray-950">{{ $loc->alias ?: 'Home' }}</span>
                                    <span class="mt-1 block max-w-full truncate text-[15px] font-medium leading-5 text-gray-400">{{ $loc->address ?: 'Coordinates: ' . $loc->latitude . ', ' . $loc->longitude }}</span>
                                </span>
                            </button>
                        </form>

                        <button
                            type="button"
                            @click.stop="menuOpen = !menuOpen"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-gray-950 transition-colors duration-200 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#00B388]/40"
                            aria-label="Address actions"
                        >
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.5 12a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm6 0a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm6 0a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div
                            x-show="menuOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-3 top-[calc(100%-0.5rem)] z-[10002] w-40 origin-top-right rounded-2xl border border-slate-100 bg-white p-1.5 shadow-[0_18px_40px_rgba(15,23,42,0.16)]"
                            x-cloak
                        >
                            <a
                                href="{{ route('profile.locations', ['edit_location' => $loc->id]) }}"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-gray-700 transition-colors hover:bg-slate-50"
                            >
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2.25" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                Edit
                            </a>

                            <div class="my-1 h-px bg-slate-100"></div>

                            <form action="{{ route('profile.locations.destroy', $loc) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-bold text-red-500 transition-colors hover:bg-red-50"
                                >
                                    <svg class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2.25" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <a
                        href="{{ route('profile.locations') }}"
                        class="m-3 flex min-h-[82px] w-[calc(100%-1.5rem)] cursor-pointer items-center gap-4 rounded-[20px] border border-[#00B388] bg-white px-4 text-left transition-colors duration-200 hover:bg-emerald-50/40 active:bg-emerald-50"
                    >
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#00B388]">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1 overflow-hidden">
                            <span class="block truncate text-[18px] font-semibold leading-6 text-gray-950">Add Home</span>
                            <span class="mt-1 block max-w-full truncate text-[15px] font-medium leading-5 text-gray-400">Save your first delivery address</span>
                        </span>
                    </a>
                @endforelse
            @else
                <a
                    href="{{ route('login') }}"
                    class="m-3 flex min-h-[82px] w-[calc(100%-1.5rem)] cursor-pointer items-center gap-4 rounded-[20px] border border-[#00B388] bg-white px-4 text-left transition-colors duration-200 hover:bg-emerald-50/40 active:bg-emerald-50"
                >
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#00B388]">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 overflow-hidden">
                        <span class="block truncate text-[18px] font-semibold leading-6 text-gray-950">Sign in</span>
                        <span class="mt-1 block max-w-full truncate text-[15px] font-medium leading-5 text-gray-400">Save and choose delivery addresses</span>
                    </span>
                </a>
            @endauth
        </div>
    </section>
</div>
